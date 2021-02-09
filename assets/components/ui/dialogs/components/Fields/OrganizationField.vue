<template>
    <div>
        <!-- Вывод сообщений. -->
        <v-snackbar
            v-model="notification.show"
            :color="notification.type"
            top
        >
            {{ notification.text }}
        </v-snackbar>

        <v-combobox
            :label="header.ru_name"
            :items="organizations"
            item-text="address"
            clearable
            color="teal"
            class="ma-1 pa-1"
            dense
            return-object
            no-data-text="Нет данных"
            :loading="loading[header.name]"
            :filter="filter"
            :value="initField(editedItem[header.name], header.display_as)"
            :rules="getRules(header)"
            @input.native="getOrgs($event, header.name)"
            @change="setValue(header.name, $event)"
            @click:clear="setValue(header.name, null)"
            :readonly="header.readonly"
        ></v-combobox>
    </div>
</template>

<script>
    import axios from 'axios';

    export default {
        props: ['header', 'editedItem'],

        data() {
            return {
                organizations: [],
                loading: {},
            }
        },

        methods: {
            // Генерация элементов в выпадающем списке из ответа от сервиса ЕГРИП/ЕГРЮЛ
            getOrgs: _.debounce(function(event, field) {
                this.loading[field] = true;
                this.organizations = [];
                let path = `https://sm.tmn-obl.ru/egrul-egrip/api/v1/egrul/find/inn=${event.target.value}`;
                if (event.target.value.length > 0) {
                    axios.defaults.headers.common = {
                        'Authorization': 'Token 72330ef722166f4c11652fdd12dfa295f78b7644'
                    };
                    axios.get(path)
                        .then(({data}) => {
                            if(!data.length) {
                                this.loading[field] = false;
                                this.organizations = [];
                            }
                            let itemForSave = {};
                            for (let org of data) {
                                itemForSave = {
                                    name: org.name,
                                    grn: org.grn,
                                    inn_kpp: org.inn_kpp,
                                };
                                this.organizations.push(itemForSave)
                            }
                            this.loading[field] = false
                        }).catch(error => {
                            this.loading[field] = false;
                            this.showNotification({
                                text: error,
                                type: 'error'
                            });
                            console.log("get organizations error:", error);
                        })
                }
            }, 1000),

            // Убираем стандартную фильтрацию
            // В стандартной фильтрации ищется полное вхождение, например "тюм советская" - ничего не найдет
            filter (item, queryText, itemText) {
                const hasValue = val => val != null ? val : '';

                const text = hasValue(itemText);
                const query = hasValue(queryText);
                let find;

                // Проверяем вхождение каждого слова поиска в каждом слове поисковой строки
                let text_words = text.split(' ');
                let query_words = query.split(' ');
                for (let i=0;i<query_words.length;i++) {
                    find = false;
                    for (let j=0;j<text_words.length;j++) {
                        // Если слово найдено
                        if (text_words[j].toLowerCase().indexOf(query_words[i].toLowerCase()) > -1) {
                            find = true;
                            break;
                        }
                    }
                    // Слово не было найдено
                    if(!find) {
                        return false
                    }
                }
                return true
            },

            // Устанавливаем значение поля при изменении
            setValue (column, selected) {
                if (selected) {
                    this.editedItem[column] = selected;
                } else {
                    this.editedItem[column] = null
                }
            },

            // Инициализируем значение поля
            initField(editItem, display_as) {
                if(editItem) {
                    // При редактировании парcим значение
                    if ((typeof editItem === 'string')) {
                        let editItem_obj = JSON.parse(editItem);
                        if (editItem_obj) {
                            return editItem_obj[display_as]
                        }
                        // При поиске нового - отображаем сразу адрес
                    } else {
                        return editItem.name
                    }
                }
            },
        }
    }
</script>

<style scoped>

</style>