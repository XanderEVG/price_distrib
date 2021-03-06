<template>
    <v-container fluid grid-list-md>
        <v-layout wrap>
            <v-flex>
                <!-- Вывод сообщений. -->
                <v-snackbar
                    v-model="notification.show"
                    :color="notification.type"
                    top
                >
                    {{ notification.text }}
                </v-snackbar>

                <grid-items-del-dialog
                    :show="showDialogDelete"
                    @closeDialogDel="showDialogDelete = false"
                    @confirmDialogDel="confirmDialogDel"
                ></grid-items-del-dialog>

                <grid-item-save-dialog
                    :show="showDialogSave"
                    :eventCreate="eventCreate"
                    :headers="headers"
                    :editedItem="editedItem"
                    table-name="shops"
                    @closeDialogSave="showDialogSave = false"
                    @confirmDialogSave="confirmDialogSave"
                    @showMessage="showNotification({text: $event.text, type: $event.type })"
                ></grid-item-save-dialog>

                <grid-ext
                    table-name="shops"
                    :headers="headers"
                    :items="items"
                    :total="total"
                    :loading="loading"
                    @getDataFromApi="getItems"
                    @deleteItem="deleteItem"
                    @createItem="createItem"
                    @editItem="editItem"
                ></grid-ext>
            </v-flex>
        </v-layout>
    </v-container>
</template>

<script>
import axios from 'axios';
import qs from 'qs';
import GridExt from "../components/ui/grid/Grid";
import GridItemsDelDialog from "../components/ui/dialogs/GridItemsDelDialog.vue";
import GridItemSaveDialog from "../components/ui/dialogs/GridItemSaveDialog.vue";

export default {
    components: {GridExt, GridItemsDelDialog, GridItemSaveDialog},

    data() {
        return {
            loading: false,
            headers: [],
            items: [],
            current_city_id: null,
            cities: [],

            showDialogDelete: false,
            selected: null,

            total: 0,
            showDialogSave: false,
            editedIndex: -1,
            defaultItem: {
                id: 0,
                name: null,
                city: null
            },
            editedItem: {},
        }
    },

    computed: {
        // Флаг создания новой записи а не редактирование существующей
        eventCreate() {
            return this.editedIndex === -1
        }
    },

    watch: {
        //Обнуляем значения при закрытии модального окна создания/редактирования записи
        showDialogSave(val) {
            if (!val) {
                this.editedItem = Object.assign({}, this.defaultItem);
                this.editedIndex = -1;
            }
        },
    },

    async mounted() {
        this.current_city_id = this.$store.getters.getCurrentCityId;
        axios.defaults.headers.common = {
            'X-CSRF-TOKEN': document.getElementsByName("csrf-token")[0].getAttribute('content')
        };
        await this.getCities().then(response => this.cities = response);
        this.headers = [
            {
                ru_name: 'ИД',
                name: 'id',
                field_type: 'integer',
                field_required: true,
                align: 'left',
                sortable: true,
                hidden: true,
            },
            {
                ru_name: 'Наименование',
                name: 'name',
                field_type: 'text',
                field_required: true,
                show_in_grid: true,
                sortable: true,
            },
            {
                ru_name: 'Адрес',
                name: 'address',
                field_type: 'text',
                field_required: false,
                show_in_grid: true,
                sortable: true,
            },
            {
                ru_name: 'Город',
                name: 'city',
                field_type: 'combobox',
                multiple: false,
                field_required: true,
                items: this.cities,
                show_in_grid: true,
                hide_in_menu_columns: true,
                sortable: true,
            }
        ]
    },

    methods: {
        getCities() {
            return new Promise((resolve, reject) => {
                axios.get('/api/city/get_list', {
                    params: {
                        sortBy: {column: 'name', direction: 'asc'}
                    },
                    paramsSerializer: params => qs.stringify(params),
                }).then(response => {
                        resolve(response.data.cities);
                    }).catch(error => {
                    reject(error);
                    this.showNotification({
                        text: 'Ошибка при запросе городов: ' + error,
                        type: 'error'
                    });
                });
            })

        },


        // Запрашиваем список строк согласно пагинации
        getItems(data) {
            this.loading = true;
            axios.defaults.headers.common = {
                'X-CSRF-TOKEN': document.getElementsByName("csrf-token")[0].getAttribute('content')
            };
            axios.get('/api/shop/get_list', {
                params: {
                    offset: data.start,
                    limit: data.limit,
                    filterBy: data.filterBy,
                    orderBy: data.orderBy
                },
                paramsSerializer: params => qs.stringify(params),
            }).then(response => {
                this.loading = false;

                if (response.data.success) {
                    this.items = response.data.shops;
                    this.total = response.data.total;
                } else {
                    this.showNotification({
                        text: (response.data.msg !== '') ? response.data.msg : 'Неизвестная ошибка',
                        type: 'error'
                    });
                }
            }).catch(error => {
                this.loading = false;
                this.showNotification({
                    text: error,
                    type: 'error'
                });
            });
        },

        // Создание новой записи
        createItem() {
            this.editedItem = Object.assign({}, this.defaultItem);
            if (this.current_city_id) {
                let city = this.cities.filter(city => city.id === this.current_city_id);
                if (city.length > 0) {
                    let name = city[0].name;
                    this.editedItem.city = {
                        'id': this.current_city_id,
                        'name': name
                    }
                }
            }
            this.showDialogSave = true;
        },

        // Редактирование существующей записи
        editItem(data) {
            this.editedIndex = data.editedIndex;
            this.editedItem = data.editedItem;
            this.showDialogSave = true;
        },

        // Удаление записи
        deleteItem(selected) {
            this.selected = selected;
            this.showDialogDelete = true;
        },

        // Подтверждение удаления записи
        confirmDialogDel() {
            this.showDialogDelete = false;

            axios.defaults.headers.common = {
                'X-CSRF-TOKEN': document.getElementsByName("csrf-token")[0].getAttribute('content')
            };
            axios.delete('/api/shop/delete', {
                data: this.selected
            }).then(response => {
                if (response.data.success) {
                    for (let i = 0; i < this.selected.length; i++) {
                        const index = this.items.map(function (e) {
                            return e.id;
                        }).indexOf(this.selected[i]);
                        if (index >= 0) {
                            this.items.splice(index, 1);
                        }
                    }
                    this.selected = null;
                } else {
                    this.showNotification({
                        text: (response.data.msg !== '') ? response.data.msg : 'Неизвестная ошибка',
                        type: 'error'
                    });
                }
            }).catch(error => {
                this.selected = null;
                this.showNotification({
                    text: error,
                    type: 'error'
                });
                console.log("delete shop error:", error);
            });
        },

        // Запрос на сохранение создаваемой/редактируемой записи
        saveFromApi(data) {
            return new Promise((resolve, reject) => {
                axios.defaults.headers.common = {
                    'X-CSRF-TOKEN': document.getElementsByName("csrf-token")[0].getAttribute('content')
                };

                function filterNonNull(obj) {
                    return Object.fromEntries(Object.entries(obj).filter(([k, v]) => v));
                }

                axios.post('/api/shop/save', qs.stringify(
                    filterNonNull(data)
                )).then(response => {
                    if (response.data.success) {
                        resolve(response.data.shop.id)
                    } else {
                        this.showNotification({
                            text: (response.data.msg !== '') ? response.data.msg : 'Неизвестная ошибка',
                            type: 'error'
                        });
                    }
                }).catch(error => {
                    reject(error);
                    this.showNotification({
                        text: error,
                        type: 'error'
                    });
                    console.log("save shop error:", error);
                });
            })
        },

        // Cохранение создаваемой/редактируемой записи
        confirmDialogSave() {
            this.saveFromApi(this.editedItem)
                .then(rowId => {
                    // Обновляем информацию на странице
                    if (this.editedIndex > -1) {
                        Object.assign(this.items[this.editedIndex], this.editedItem);
                    } else {
                        this.editedItem.id = rowId;
                        this.items.push(this.editedItem);
                        this.total = this.total + 1;
                    }
                    this.showDialogSave = false;
                })
        },
    }
}
</script>
