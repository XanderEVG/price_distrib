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
                        table-name="users"
                        @closeDialogSave="showDialogSave = false"
                        @confirmDialogSave="confirmDialogSave"
                        @showMessage="showNotification({text: $event.text, type: $event.type })"
                ></grid-item-save-dialog>

                <grid-ext
                        table-name="cities"
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
        components: { GridExt,  GridItemsDelDialog, GridItemSaveDialog },

        data() {
            return {
                loading: false,
                headers: [],
                items: [],

                showDialogDelete: false,
                selected: null,

                total: 0,
                showDialogSave: false,
                editedIndex: -1,
                defaultItem: {
                    id: 0,
                    name: null
                },
                editedItem: {},
            }
        },

        computed: {
            // Флаг создания новой записи а не редактирование существующей
            eventCreate () {
                return this.editedIndex === -1
            }
        },

        watch: {
            //Обнуляем значения при закрытии модального окна создания/редактирования записи
            showDialogSave (val) {
                if (!val) {
                    this.editedItem = Object.assign({}, this.defaultItem);
                    this.editedIndex = -1;
                }
            },
        },

         mounted() {
            axios.defaults.headers.common = {
                'X-CSRF-TOKEN': document.getElementsByName("csrf-token")[0].getAttribute('content')
            };
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
                }
            ]
        },

        methods: {
            // Запрашиваем список
            getItems(data) {
                this.loading = true;
                axios.defaults.headers.common = {
                    'X-CSRF-TOKEN': document.getElementsByName("csrf-token")[0].getAttribute('content')
                };
                axios.get('/api/city/get_list', { params: {
                        start: data.start,
                        limit: data.limit,
                        filters: data.filters,
                        sort: data.sort,
                        sort_dir: data.sort_dir,
                    }}).then(response => {
                    this.loading = false;

                    if (response.data.success) {
                        this.items = response.data.cities;
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
                axios.delete('/api/city/delete', {
                    data: this.selected
                }).then(response => {
                    if (response.data.success) {
                        for(let i=0; i < this.selected.length; i++) {
                            const index = this.items.map(function(e) { return e.id; }).indexOf(this.selected[i]);
                            if(index >= 0 ) {
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
                    console.log("delete users error:", error);
                });
            },

            // Запрос на сохранение создаваемой/редактируемой записи
            saveFromApi(data) {
                return new Promise((resolve, reject) => {
                    axios.defaults.headers.common = {
                        'X-CSRF-TOKEN': document.getElementsByName("csrf-token")[0].getAttribute('content')
                    };

                    // Удаляет поля = null
                    function filterNonNull(obj) {
                        return Object.fromEntries(Object.entries(obj).filter(([k, v]) => v));
                    }
                    axios.post('/api/city/save', qs.stringify(
                        filterNonNull(data)
                    )).then(response => {
                        if (response.data.success) {
                            resolve(response.data.city.id)
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
                        console.log("save users error:", error);
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
