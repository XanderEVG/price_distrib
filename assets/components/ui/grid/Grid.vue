<template>
    <v-container>
        <v-data-table
                :items="items"
                :items-per-page="25"
                class="grid_table"
                item-key="id"
                loading-text="Загрузка данных..."
                no-data-text="Нет записей"
                no-results-text="Неправильно сформированы заголовки"
                :options.sync="pagination"
                :server-items-length="total"
                :loading="loading"
                :class="'table_' + tableName + '_class'"
                :headers-length="headerLength"
                :footer-props="{
                    showFirstLastPage: true,
                    itemsPerPageText: 'Записей на странице',
                    itemsPerPageOptions: [5,10,15,20,25,50,{ text: 'Все', value: null }],
                  }"
        >
            <!--Устанавливаем цвет прогрессбара-->
            <template v-slot:progress>
                <v-progress-linear
                        color="teal"
                        :height="3"
                        indeterminate
                ></v-progress-linear>
            </template>

            <!--Заголовки-->
            <template v-slot:header>
                <tr class="table_header" >
                    <th style="width: 53px;">
                        <v-checkbox
                                :input-value="selectedAll"
                                :indeterminate="indeterminate"
                                color="teal"
                                hide-details
                                class="ma-0"
                                @click.stop="toggleAll"
                        ></v-checkbox>
                    </th>
                    <th
                            v-for="header in visibleHeaders"
                            :class="[
                               'text-start',
                                header.sortable === false ? 'not_sortable' : 'sortable',
                               pagination.descending ? 'desc' : 'asc',
                               {th_active: header.name === pagination.sortBy || filtersEnabled.includes(header.name)},
                            ]"
                    >
                        <v-layout row pr-3>
                            <v-flex grow pa-3>
                                <v-icon class="sort_arrow" small v-if="header.sortable !== false">arrow_upward</v-icon>
                                <div class="sort_arrow_spacer"  v-if="header.sortable !== false"></div>
                                <span @click="changeSort(header.name, header.sortable)">{{header.ru_name}}</span>
                            </v-flex>
                        </v-layout>
                    </th>
                </tr>
            </template>


            <!--Формируем строки-->
            <template v-slot:item="{ item, index }">
                <tr :key="item.id">
                    <td>
                        <v-checkbox
                                :value="item.id"
                                v-model="selected"
                                color="teal"
                                hide-details
                                class="ma-0"
                                @change="indeterminate = true"
                        ></v-checkbox>
                    </td>
                    <td v-for="header in visibleHeaders" @dblclick="editItem(item)">
                        <item-field-grid
                                :header="header"
                                :table_name="tableName"
                                :item="item"
                        ></item-field-grid>
                    </td>
                </tr>
            </template>


            <!--Добавляем функциональные кнопки-->
            <template v-slot:top>
                <v-toolbar flat color="white">
                    <v-tooltip bottom>
                        <template v-slot:activator="{ on }">
                            <v-btn text icon @click="createItem" v-on="on">
                                <v-icon>add</v-icon>
                            </v-btn>
                        </template>
                        <span>Добавить</span>
                    </v-tooltip>

                    <v-tooltip bottom>
                        <template v-slot:activator="{ on }">
                            <v-btn text icon @click="deleteItem" v-on="on">
                                <v-icon>delete</v-icon>
                            </v-btn>
                        </template>
                        <span>Удалить</span>
                    </v-tooltip>

                    <refresh-btn @getDataFromApi="getDataFromApi"></refresh-btn>
                </v-toolbar>
            </template>



            <!--Устанавливаем текст пагинатора-->
            <template v-slot:footer.page-text="{ pageStart, pageStop, itemsLength }">
                Строки {{ pageStart }} - {{ pageStop }} из {{ itemsLength }}
            </template>
        </v-data-table>
    </v-container>
</template>

<script>
    import RefreshBtn from "./grid_toolbar/RefreshBtn.vue";
    import ItemFieldGrid from "./ItemFieldGrid.vue";

    export default {
        components: {
            RefreshBtn,
            ItemFieldGrid
        },
        props: {
            headers: {
                type: Array,
                required: true,
            },
            items: Array,
            total: {
                type: Number,
                required: true,
            },
            tableName: String,
            loading: Boolean,
            editedItem: Object,
        },

        data() {
            return {
                selected: [],
                pagination: {
                    sortBy: "id",
                    descending: false,
                    page: 1,
                    rowsPerPage: 10,
                },
                filters: {},
                indeterminate: false,
                selectedAll: false,
                filtersEnabled: [],
            };
        },

        computed: {
            // Видимые колонки
            visibleHeaders() {
                return this.headers.filter(h => h.show_in_grid);
            },
            // Расчет количества столбцов, чтобы в случае отсутствия записей сообщение растягивалось на всю длину строки
            headerLength() {
                return this.headers.length + 1;
            },
        },

        watch: {
            // Событие изменения пагинации
            pagination: {
                handler() {
                    this.getDataFromApi();
                },
                deep: true,
            },

            selected(val) {
                if (!val.length) {
                    this.indeterminate = false;
                    this.selectedAll = false;
                }
            },
        },

        methods: {
            // Запрашиваем список записей согласно пагинации
            getDataFromApi() {
                const {sortBy, descending, page, itemsPerPage} = this.pagination;

                let start = (page - 1) * itemsPerPage,
                    limit = itemsPerPage;

                this.$emit("getDataFromApi", {
                    start,
                    limit,
                    sort: null,
                    sort_dir: null,
                    filters: null
                });
            },

            // Удаление записи
            deleteItem(item) {
                if (!item) {
                    if (!this.selected.length) {
                        this.showNotification({
                            text: "Выберите хотя бы одну запись",
                            type: "warning",
                        });
                        return;
                    }
                }
                this.$emit("deleteItem", this.selected);
            },

            // Создание новой записи
            createItem() {
                this.$emit("createItem");
            },

            // Редактирование записи
            editItem(item) {
                this.$emit("editItem", {
                    editedIndex: this.items.indexOf(item),
                    editedItem: Object.assign({}, item),
                });
            },

            // Выделение всех записей
            toggleAll() {
                this.indeterminate = false;

                if (this.selected.length) {
                    this.selected = [];
                    this.selectedAll = false;
                } else {
                    this.items.forEach((item) => {
                        this.selected.push(item.id);
                    });
                    this.selectedAll = true;
                }
            },

            // Сортировка. Если явно не указан параметр sort, то делается toggle в случае повторной сортировки
            changeSort(column, sortable, sort) {
                if (sortable === false) {
                    return;
                }
                if (this.pagination.sortBy === column) {
                    this.pagination.descending =
                        sort !== undefined ? sort : !this.pagination.descending;
                } else {
                    this.pagination.sortBy = column;
                    this.pagination.descending = sort !== undefined ? sort : false;
                }
            },

            // Уведомление об ошибках
            showErrorNotification(msg) {
                this.showNotification({
                    text: msg,
                    type: "error",
                });
            },
        }
    }
</script>

<style scoped>

</style>