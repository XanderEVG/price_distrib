<template>
    <v-container fluid grid-list-md>
      <vue-headful title="Устройства"/>
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
                    table-name="devices"
                    @closeDialogSave="closeDialogSave"
                    @confirmDialogSave="confirmDialogSave"
                    @showMessage="showNotification({text: $event.text, type: $event.type })"
                ></grid-item-save-dialog>

                <grid-ext
                    table-name="devices"
                    :headers="headers"
                    :items="items"
                    :total="total"
                    :loading="loading"
                    @getDataFromApi="getItems"
                    @deleteItem="deleteItem"
                    @createItem="createItem"
                    @editItem="editItem"
                    @showEmptyDevices="showEmptyDevices"
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
            current_shop_id: null,
            shops: [],

            showDialogDelete: false,
            selected: null,

            total: 0,
            showDialogSave: false,
            show_empty_devices: false,
            editedIndex: -1,
            defaultItem: {
                id: 0,
                shop: null,
                mac: null,
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
        this.current_shop_id = this.$store.getters.getCurrentShopId;

        axios.defaults.headers.common = {
            'X-CSRF-TOKEN': document.getElementsByName("csrf-token")[0].getAttribute('content')
        };
        await this.getShops().then(response => this.shops = response);
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
                ru_name: 'MAC-адрес',
                name: 'mac',
                field_type: 'text',
                field_required: true,
                show_in_grid: true,
                sortable: true,
                field_mask: "mac"
            },
            {
                ru_name: 'Магазин',
                name: 'shop',
                field_type: 'combobox',
                multiple: false,
                field_required: false,
                items: this.shops,
                show_in_grid: true,
                hide_in_menu_columns: true,
                sortable: true,
            }
        ]
    },

    methods: {
        getShops() {
            return new Promise((resolve, reject) => {
                axios.get('/api/shop/get_list', {
                    params: {
                        sortBy: {column: 'name', direction: 'asc'}
                    },
                    paramsSerializer: params => qs.stringify(params),
                }).then(response => {
                        resolve(response.data.shops);
                    }).catch(error => {
                    reject(error);
                    this.showNotification({
                        text: 'Ошибка при запросе магазинов: ' + error,
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
            axios.get('/api/device/get_list', {
                params: {
                    offset: data.start,
                    limit: data.limit,
                    filterBy: data.filterBy,
                    orderBy: data.orderBy,
                    shop_id: this.show_empty_devices === true ? null : (this.current_shop_id === null ? 'all' : this.current_shop_id)
                },
                paramsSerializer: params => qs.stringify(params),
            }).then(response => {
                this.loading = false;

                if (response.data.success) {
                    this.items = response.data.devices;
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
            this.getCurrentShops();

            this.editedItem = Object.assign({}, this.defaultItem);
            if (this.current_shop_id) {
                let shop = this.shops.filter(shop => shop.id === this.current_shop_id);
                if (shop.length > 0) {
                    let name = shop[0].name;
                    this.editedItem.shop = {
                        'id': this.current_shop_id,
                        'name': name
                    }
                }
            }
            this.showDialogSave = true;
        },

        // Редактирование существующей записи
        editItem(data) {
            this.getCurrentShops();

            this.editedIndex = data.editedIndex;
            this.editedItem = data.editedItem;
            this.showDialogSave = true;
        },

        // Удаление записи
        deleteItem(selected) {
            this.selected = selected;
            this.showDialogDelete = true;
        },

        closeDialogSave() {
            this.showDialogSave = false;
            let that = this;
            this.headers.forEach(function (value, i) {
                if (value.name === 'shop') {
                    that.headers[i].items = that.shops;
                }
            });
        },
        getCurrentShops() {
            if (this.current_city_id) {
                let that = this;
                this.headers.forEach(function (value, i) {
                    if (value.name === 'shop') {
                        that.headers[i].items = that.shops.filter(h => h.city.id === that.current_city_id);
                    }
                });
            }
        },


        // Подтверждение удаления записи
        confirmDialogDel() {
            this.showDialogDelete = false;

            axios.defaults.headers.common = {
                'X-CSRF-TOKEN': document.getElementsByName("csrf-token")[0].getAttribute('content')
            };
            axios.delete('/api/device/delete', {
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
                console.log("delete device error:", error);
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

                axios.post('/api/device/save', qs.stringify(
                    filterNonNull(data)
                )).then(response => {
                    if (response.data.success) {
                        resolve(response.data.device.id)
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
                    console.log("save device error:", error);
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

        showEmptyDevices() {
            this.show_empty_devices = !this.show_empty_devices;
            this.$emit('update');
        }
    }
}
</script>
