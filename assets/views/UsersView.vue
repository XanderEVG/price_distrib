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
                        v-on:changedCityIdx="changedCityIdx($event)"
                ></grid-item-save-dialog>

                <grid-ext
                        table-name="users"
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
                roles: [],
                cities: [],
                shops: [],

                showDialogDelete: false,
                selected: null,

                total: 0,
                showDialogSave: false,
                editedIndex: -1,
                defaultItem: {
                    id: 0,
                    username: null,
                    email: null,
                    fio: null,
                    roles: null,
                    cities: null,
                    shops: null,
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

        async mounted() {
            axios.defaults.headers.common = {
                'X-CSRF-TOKEN': document.getElementsByName("csrf-token")[0].getAttribute('content')
            };
            this.roles = [{'id':'ROLE_USER', 'name':'Пользователь'}, {'id':'ROLE_ADMIN', 'name':'Администратор'}];
            await this.getCities().then(response => this.cities = response);
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
                    ru_name: 'Логин',
                    name: 'username',
                    field_type: 'text',
                    login: true,
                    field_required: true,
                    show_in_grid: true,
                },
                {
                    ru_name: 'ФИО',
                    name: 'fio',
                    field_type: 'text',
                    field_required: true,
                    show_in_grid: true,
                },
                {
                    ru_name: 'E-mail',
                    name: 'email',
                    field_type: 'text',
                    field_required: true,
                    show_in_grid: true,
                    email: true,
                },
                {
                    ru_name: 'Роли',
                    name: 'roles',
                    field_type: 'combobox',
                    multiple: true,
                    field_required: true,
                    items: this.roles,
                    show_in_grid: true,
                },
                {
                    ru_name: 'Города',
                    name: 'cities',
                    field_type: 'combobox',
                    multiple: true,
                    field_required: false,
                    items: this.cities,
                    show_in_grid: true,
                    hide_in_menu_columns: true,
                    sortable: false,
                },
                {
                    ru_name: 'Магазины',
                    name: 'shops',
                    field_type: 'combobox',
                    multiple: true,
                    field_required: false,
                    items: this.shops,
                    show_in_grid: true,
                    hide_in_menu_columns: true,
                    sortable: false,
                },
                {
                    ru_name: 'Пароль',
                    name: 'password',
                    field_type: 'text',
                    password: true,
                    field_required: true,
                    readonly: true,
                    hide_in_menu_columns: true,
                },
            ]
        },

        methods: {
            getCities() {
                return new Promise((resolve, reject) => {
                    axios.post('/api/city/get_list', {
                        start: 0,
                        limit: null,
                    })
                        .then(response => {
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
                    axios.post('/api/shop/get_list', {
                        start: 0,
                        limit: null,
                    })
                        .then(response => {
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

            changedCityIdx(event) {
                let that = this;
                this.headers.forEach(function (value, i) {
                    if (value.name === 'shops') {
                        that.headers[i].items = that.shops.filter(h => ((event.includes(h.city.id))));
                    }
                });

                if (this.editedItem.shops !== null) {
                    this.editedItem.shops = this.editedItem.shops.filter(h => ((event.includes(h.city.id))));
                }
            },

            // Запрашиваем список пользователей согласно пагинации
            getItems1932
                (data) {
                this.loading = true;
                axios.defaults.headers.common = {
                    'X-CSRF-TOKEN': document.getElementsByName("csrf-token")[0].getAttribute('content')
                };
                axios.get('/api/users/get_list', { params: {
                        start: data.start,
                        limit: data.limit,
                        filters: data.filters,
                        sort: data.sort,
                        sort_dir: data.sort_dir,
                }}).then(response => {
                    this.loading = false;

                    if (response.data.success) {
                        this.items = response.data.users;
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
                axios.delete('/api/users/delete', {
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
                    axios.post('/api/users/save', qs.stringify(
                        filterNonNull(data)
                    )).then(response => {
                        if (response.data.success) {
                            resolve(response.data.user.id)
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
