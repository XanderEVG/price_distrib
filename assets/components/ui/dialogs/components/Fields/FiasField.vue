<template>
    <v-combobox
        :items="fias_addresses"
        item-text="address"
        clearable
        class="martop select"
        dense
        return-object
        no-data-text="Нет данных"
        :loading="loading[header.name]"
        :filter="filterFIAS"
        :value="initFIASField(editedItem[header.name], 'address')"
        :rules="getRules(header)"
        placeholder="Начните вводить адрес"
        @input.native="getFIAS($event, header.name)"
        @change="setValueFIAS(header.name, $event)"
        @click:clear="setValueFIAS(header.name, null)"
        :readonly="header.readonly"
        solo
        flat
    ></v-combobox>
</template>

<script>
    import axios from 'axios';

    export default {
        props: ['header', 'editedItem'],

        data() {
            return {
                fias_addresses: [],
                loading: {},
            }
        },

        methods: {
            /** Генерация элементов в выпадающем списке запросом к сервису ФИАС
             * Пример ответа: [
                 {
                    "address": "Саха /Якутия/ Респ",
                    "address_struct": "[{\"formal_name\":\"Саха /Якутия/\",\"level\":1,\"short_name\":\"Респ\"}]",
                    "ao_level": 1,
                    "ao_id": "d9e4c4c3-3dbe-4fc5-ac26-8e9102af5bd9",
                    "house_id": "",
                    "postal_code": "",
                    "ao_guid": "c225d3db-1db6-4063-ace0-b3fe9ea3805f",
                    "house_guid": ""
                 }, {
                    "address": "Северная Осетия - Алания Респ",
                    "address_struct": "[{\"formal_name\":\"Северная Осетия - Алания\",\"level\":1,\"short_name\":\"Респ\"}]",
                    "ao_level": 1,
                    "ao_id": "a7feacd1-6a9a-4275-94a2-3ed2cf2de0bb",
                    "house_id": "",
                    "postal_code": "",
                    "ao_guid": "de459e9c-2933-4923-83d1-9c64cfd7a817",
                    "house_guid": ""
                 }
             ]; */
            getFIAS: _.debounce(function(event, field) {
                this.loading[field] = true;
                this.fias_addresses = [];
                if (event.target.value.length > 0) {
                    axios.defaults.headers.common = {
                        'Authorization': 'Token cd0498d17f2a5e0e25a3c6a26190d614eb768134'
                    };
                    axios.get(`https://sm.tmn-obl.ru/fias-api/api/v1/search?term=${event.target.value}`)
                        .then(({data}) => {
                            if(!data.length) {
                                this.loading[field] = false;
                                this.fias_addresses = [];
                            }
                            //this.fias_addresses = data;
                            let itemForSave = {};
                            data.forEach(itemFIAS => {
                                itemForSave = {
                                    address: itemFIAS.address,
                                    guid: itemFIAS.house_guid || itemFIAS.ao_guid,
                                };
                                this.fias_addresses.push(itemForSave)
                            });
                            this.loading[field] = false
                        }).catch(error => {
                            alert("Ошибке при попытке запроса ФИАС: " + error);
                        })
                }
            }, 1000),

            // Убираем стандартную фильтрацию
            // В стандартной фильтрации ищется полное вхождение, например "тюм советская" - ничего не найдет
            filterFIAS (item, queryText, itemText) {
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

            // Устанавливаем значение поля ФИАС при изменении
            setValueFIAS (column, selected) {
                if (selected) {
                    axios.get('/api/geocoder/coordinates?address=' + selected.address).then((response) => {
                        const coords = response.data.coordinates,
                            //coordsSv = (coords && coords[0] && coords[0][0]) ? coords[0][0] + ',' + coords[0][1] : null;
                            coordsSv = coords[0] || null;
                        this.editedItem[column] = {...selected, coordinates: coordsSv}
                     })
                } else {
                    this.editedItem[column] = null
                }
            },

            // Инициализируем значение поля ФИАС
            initFIASField(editItem, display_as) {
                if(editItem) {
                    // При редактировании парим значение
                    if ((typeof editItem === 'string')) {
                        let editItem_obj = JSON.parse(editItem);
                        if (editItem_obj) {
                            return editItem_obj[display_as]
                        }
                        // При поиске нового - отображаем сразу адрес
                    } else {
                        return editItem.address
                    }
                }
            },
        }
    }
</script>

<style scoped>

</style>