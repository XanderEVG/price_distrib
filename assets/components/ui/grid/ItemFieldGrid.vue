<template>
    <!-- Уникальные поля -->
        <!-- users -->
        <span v-if="table_name === 'users' && header.name  === 'roles'" v-html="getRoles(item[header.name], header.name)"></span>
        <span v-else-if="table_name === 'users' && header.name  === 'cities'" v-html="getCities(item[header.name])"></span>
        <span v-else-if="table_name === 'users' && header.name  === 'shops'" v-html="getShops(item[header.name])"></span>

        <!-- shops -->
        <span v-else-if="table_name === 'shops' && header.name  === 'city'" v-html="getOneCity(item[header.name])"></span>

        <!-- devices -->
        <span v-else-if="table_name === 'devices' && header.name  === 'shop'" v-html="getOneShop(item[header.name])"></span>

        <!-- devices -->
        <span v-else-if="table_name === 'products' && header.name  === 'city'" v-html="getOneCity(item[header.name])"></span>
        <span v-else-if="table_name === 'products' && header.name  === 'shop'" v-html="getOneShop(item[header.name])"></span>
        <span v-else-if="table_name === 'products' && header.name  === 'devices'" v-html="getDevices(item[header.name])"></span>





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

                return rez.join('<br>\n');
            },

            // Вывод массива городов в грид, с джойном через <br>
            getCities(values) {
                let rez = [];
                if (values) {
                    values.forEach((element) => {
                        rez.push(element['name']);
                    });
                }
                return rez.join('<br>\n');
            },

            // Вывод массива городов в грид, с джойном через <br>
            getShops(values) {
                let rez = [];
                if (values) {
                    values.forEach((element) => {
                        rez.push(element['name'] + " (" + element['city'].name + ")");
                    });
                }
                return rez.join('<br>\n');
            },

            // Вывод города в грид
            getOneCity(values) {
              if (values) {
                return values['name'];
              } else {
                return null;
              }
            },

            // Вывод магазина в грид
            getOneShop(values) {
                if (values) {
                  return values['name'];
                } else {
                  return null;
                }
            },

            // Вывод устройств в грид
            getDevices(values) {
              let rez = [];
              if (values) {
                values.forEach((element) => {
                  rez.push(element['mac']);
                });
              }
              return rez.join('<br>\n');
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