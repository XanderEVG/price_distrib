<template>
    <!-- Уникальные поля -->
        <!-- users -->
        <span v-if="table_name === 'users' && header.name  === 'roles'" v-html="getRoles(item[header.name], header.name)"></span>
        <span v-else-if="table_name === 'users' && header.name  === 'cities'" v-html="getUserCities(item[header.name])"></span>
        <span v-else-if="table_name === 'users' && header.name  === 'shops'" v-html="getUserShops(item[header.name])"></span>




    <!-- Общие поля -->
        <v-checkbox
            v-else-if="header.field_type === 'boolean'"
            :input-value=item[header.name]
            disabled
            hide-details
            class="ma-0"
        ></v-checkbox>

        <span v-else-if="header.field_type  === 'date'" v-html="getDateWithFormat(item[header.name])"></span>

        <span v-else>
            {{ item[header.name] }}
        </span>
</template>

<script>
    import axios from 'axios';
    import moment from 'moment';

    export default {
        props: ['table_name', 'header', 'item'],

        methods: {
            // Вывод массива ролей в грид
            getRoles(values, column_name) {
                let items = this.header.items;
                let rez = [];
                if (values) {
                    values.forEach((element, index) => {
                        rez.push(element.name);
                    });
                }

                return rez.join('<br>\n')
            },

            // Вывод массива городов в грид
            getUserCities(values) {
                let rez = [];
                if (values) {
                    values.forEach((element) => {
                        rez.push(element['name']);
                    });
                }
                return rez.join('<br>\n')
            },

            // Вывод массива городов в грид
            getUserShops(values) {
                let rez = [];
                if (values) {
                    values.forEach((element) => {
                        rez.push(element['name'] + " (" + element['city'].name + ")");
                    });
                }
                return rez.join('<br>\n')
            },

            //Форматирование даты
            getDateWithFormat(date_string) {
                if (date_string) {
                    return moment(date_string).format('DD.MM.YYYY');
                } else {
                    return '';
                }
            },
        }
    }
</script>

<style scoped>

</style>