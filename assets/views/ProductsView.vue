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
                    table-name="products"
                    @closeDialogSave="closeDialogSave"
                    @confirmDialogSave="confirmDialogSave"
                    @showMessage="showNotification({text: $event.text, type: $event.type })"
                    v-on:changedCityIdx="changedCityIdx($event)"
                ></grid-item-save-dialog>

                <grid-ext
                    table-name="products"
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
            current_shop_id: null,
            cities: [],
            shops: [],
            devices: [],

            showDialogDelete: false,
            selected: null,

            total: 0,
            showDialogSave: false,
            editedIndex: -1,
            defaultItem: {
                id: 0,
                name: null,
                main_unit: null,
                main_price: null,
                second_unit: null,
                second_price: null,
                city: null,
                shop: null,
                devices: null,
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
        await this.getCities().then(response => this.cities = response);
        await this.getShops().then(response => this.shops = response);
        await this.getDevices().then(response => this.devices = response);

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
                ru_name: 'Код товара',
                name: 'productCode',
                field_type: 'text',
                field_required: true,
                show_in_grid: true,
                sortable: true
            },
            {
                ru_name: 'Наименование',
                name: 'name',
                field_type: 'text',
                field_required: true,
                show_in_grid: true,
                sortable: true
            },
            {
                ru_name: 'Ед. изм.',
                name: 'mainUnit',
                field_type: 'text',
                field_required: true,
                show_in_grid: true,
                sortable: true
            },
            {
                ru_name: 'Цена',
                name: 'mainPrice',
                field_type: 'float',
                field_required: true,
                show_in_grid: true,
                sortable: true
            },
            {
                ru_name: 'Доп. ед. изм.',
                name: 'secondUnit',
                field_type: 'text',
                field_required: false,
                show_in_grid: false,
                sortable: false
            },
            {
                ru_name: 'Доп. цена',
                name: 'secondPrice',
                field_type: 'float',
                field_required: false,
                show_in_grid: false,
                sortable: false
            },
            {
                ru_name: 'Город',
                name: 'city',
                field_type: 'combobox',
                multiple: false,
                field_required: true,
                items: this.cities,
                show_in_grid: this.current_city_id == null,
                hide_in_menu_columns: true,
                sortable: true,
            },
            {
                ru_name: 'Магазин',
                name: 'shop',
                field_type: 'combobox',
                multiple: false,
                field_required: true,
                items: this.shops,
                show_in_grid: this.current_shop_id == null,
                hide_in_menu_columns: true,
                sortable: true,
            },
            {
                ru_name: 'Устройство',
                name: 'devices',
                field_type: 'combobox',
                multiple: true,
                field_required: false,
                items: this.devices,
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
        getDevices() {
            return new Promise((resolve, reject) => {
                axios.get('/api/device/get_list', {
                    params: {
                        shop_id: this.current_shop_id,
                        sortBy: {column: 'mac', direction: 'asc'}
                    },
                    paramsSerializer: params => qs.stringify(params),
                }).then(response => {
                        resolve(response.data.devices);
                    }).catch(error => {
                    reject(error);
                    this.showNotification({
                        text: 'Ошибка при запросе магазинов: ' + error,
                        type: 'error'
                    });
                });
            })
        },

        // Изменился выбранный город в диалоге
        changedCityIdx(new_city_id) {
            let that = this;
            this.headers.forEach(function (value, i) {
                if (value.name === 'shop') {
                    that.headers[i].items = that.shops.filter(h => (h.city.id === new_city_id));
                }
            });

            if (this.editedItem.shop !== null) {
                this.editedItem.shop = null;
            }
        },

        // Запрашиваем список строк согласно пагинации
        getItems(data) {
            this.loading = true;
            axios.defaults.headers.common = {
                'X-CSRF-TOKEN': document.getElementsByName("csrf-token")[0].getAttribute('content')
            };
            axios.get('/api/product/get_list', {
                params: {
                    offset: data.start,
                    limit: data.limit,
                    filterBy: data.filterBy,
                    orderBy: data.orderBy,
                    city_id: this.current_city_id,
                    shop_id: this.current_shop_id,
                },
                paramsSerializer: params => qs.stringify(params),
            }).then(response => {
                this.loading = false;

                if (response.data.success) {
                    this.items = response.data.products;
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

            // Ставим город по умолчанию
            if (this.current_city_id) {
                let city = this.cities.filter(city => city.id === this.current_city_id);
                if (city.length > 0) {
                    let name = city[0].name;
                    this.editedItem.city = {
                        'id': this.current_city_id,
                        'name': name
                    }
                }

                // Отфильтровываем лишние магазины на основе выбранного города
                let that = this;
                this.headers.forEach(function (value, i) {
                    if (value.name === 'shop') {
                        that.headers[i].items = that.shops.filter(h => (that.current_city_id === h.city.id));
                    }
                });
            }

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

            // отображаем только свободные устройства + те что заданы в данной строке
            this.getEmptyDevices(this.editedItem);

            this.showDialogSave = true;
        },

        // Редактирование существующей записи
        editItem(data) {
            this.editedIndex = data.editedIndex;
            this.editedItem = data.editedItem;

            // Отфильтровываем лишние магазины на основе выбранного города
            let that = this;
            this.headers.forEach(function (value, i) {
                if (value.name === 'shop') {
                    that.headers[i].items = that.shops.filter(h => (that.editedItem.city.id === h.city.id));
                }
            });

            // отображаем только свободные устройства + те что заданы в данной строке
            this.getEmptyDevices(this.editedItem);

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
                if (value.name === 'devices') {
                    that.headers[i].items = that.devices;
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

        // Фильтруем устройства - выдаем в селект только не привязанные и привязанные к этому товару
        getEmptyDevices(editedItem) {
            let selected_macs = editedItem.devices.map(function (m) {
                return m.mac;
            });
            let that = this;
            this.headers.forEach(function (value, i) {
                if (value.name === 'devices') {
                    that.headers[i].items = that.devices.filter(h => (h.product == null || selected_macs.includes(h.mac)));
                }
            });
        },

        // Подтверждение удаления записи
        confirmDialogDel() {
            this.showDialogDelete = false;

            axios.defaults.headers.common = {
                'X-CSRF-TOKEN': document.getElementsByName("csrf-token")[0].getAttribute('content')
            };
            axios.delete('/api/product/delete', {
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
                axios.post('/api/product/save',
                    qs.stringify(filterNonNull(data))
                ).then(response => {
                    if (response.data.success) {

                        // Добавляем в выбранные this.devices(список устройств для селектбокса) текущий товар. Это нужно для последующей фильтрации
                        let saved_macs = response.data.product.devices.map(function (m) {
                            return m.mac;
                        });
                        let that = this;
                        this.devices.forEach(function (value, i) {
                            if (saved_macs.includes(value.mac)) {
                                that.devices[i].product = {'id': response.data.product.id, 'name': response.data.product.name};
                            }
                        });


                        resolve(response.data.product.id)
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
                    console.log("save product error:", error);
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
