<template>
    <div class="grid-field">
        <!--<v-toolbar flat color="white" dense>
            <v-toolbar-title>{{ header.ru_name }}</v-toolbar-title>
        </v-toolbar>-->

        <v-data-table
            :headers="header[header.name]"
            :items="editedItem[header.name]"
            hide-default-footer
            no-data-text="Нет записей"
            no-results-text="Нет записей2"
            item-key="entity_id"
            :items-per-page="10000"
            dense
            show-expand
            single-expand
            fixed-header
            height="300px"
        >
            <template v-slot:item.data-table-expand="{ expand, isExpanded }">
                <v-icon @click="expand(!isExpanded)">keyboard_arrow_down</v-icon>
            </template>

            <template v-slot:item.c="{ item }">
                <v-simple-checkbox v-model="item.c"></v-simple-checkbox>
            </template>

            <template v-slot:item.r="{ item }">
                <v-simple-checkbox v-model="item.r"></v-simple-checkbox>
            </template>

            <template v-slot:item.u="{ item }">
                <v-simple-checkbox v-model="item.u"></v-simple-checkbox>
            </template>

            <template v-slot:item.d="{ item }">
                <v-simple-checkbox v-model="item.d"></v-simple-checkbox>
            </template>

            <template v-slot:item.ra="{ item }">
                <v-simple-checkbox v-model="item.ra"></v-simple-checkbox>
            </template>

            <template v-slot:item.export="{ item }">
                <v-simple-checkbox v-model="item.export"></v-simple-checkbox>
            </template>

            <template v-slot:item.import="{ item }">
                <v-simple-checkbox v-model="item.import"></v-simple-checkbox>
            </template>

            <template v-slot:expanded-item="{ headers, item }">
                <td :colspan="headers.length">
                    <v-data-table
                        :headers="rightFieldsHeaders"
                        :items="editedItem.fieldsRights[item.entity_id]"
                        item-key="id"
                        hide-default-footer
                        dense
                        no-data-text="Нет записей"
                    >
                        <template v-slot:item.read="{ item }">
                            <v-simple-checkbox v-model="item.read"></v-simple-checkbox>
                        </template>

                        <template v-slot:item.write="{ item }">
                            <v-simple-checkbox v-model="item.write"></v-simple-checkbox>
                        </template>
                    </v-data-table>
                </td>
            </template>

        </v-data-table>
    </div>
</template>

<script>
    export default {
        props: ['header', 'editedItem'],

        data() {
            return {
                rightFieldsHeaders: [
                    { text: 'Поле', value: 'ru_field' },
                    { text: 'Чтение', value: 'read' },
                    { text: 'Редактирование', value: 'write' },
                ],
            }
        },
    }
</script>

<style scoped>
    .grid-field {
        overflow-y: auto
    }
</style>