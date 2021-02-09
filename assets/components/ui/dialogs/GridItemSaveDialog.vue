<template>
    <v-dialog v-model="show" persistent :max-width="fullWidth ? '90%' : '50%'" :key="editedItem.id">

        <v-card class="custom_form">
            <v-toolbar dark dense flat color="teal">
                <v-icon>{{ eventCreate ? 'add' : 'edit' }}</v-icon>
                <v-toolbar-title class="white--text">
                    {{ eventCreate ? 'Новая запись' : 'Редактирование записи' }}
                </v-toolbar-title>
                <v-spacer></v-spacer>
                <v-btn icon @click="$emit('closeDialogSave')">
                    <v-icon>close</v-icon>
                </v-btn>
            </v-toolbar>

            <v-form ref="form" class="fix-form">
                <v-container grid-list-md>
                    <v-layout wrap>
                        <v-flex xs12 sm12 md12>
                            <item-field-form
                                v-for="(header, index) in visibleHeaders"
                                :key="index"
                                :header="header"
                                :edited-item="editedItem"
                                :event-create="eventCreate"
                                :full-width="fullWidth"
                            ></item-field-form>
                        </v-flex>
                    </v-layout>
                </v-container>
            </v-form>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn text @click="$emit('closeDialogSave')">Отмена</v-btn>
                <v-btn color="teal" text @click="confirmDialogSave">Сохранить</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
    import ItemFieldForm from './components/ItemFieldForm.vue';
    import axios from 'axios';

    export default {
        components: { ItemFieldForm },

        props: {
            show: Boolean,
            fullWidth: Boolean,
            editedItem: {
                type: Object,
                required: true
            },
            headers: {
                type: Array,
                required: Boolean
            },
            tableName: String,
        },

        computed: {
            visibleHeaders() {
                return this.headers.filter(h => ((h.name !== 'id') && (h.field_type !== 'formula') && !h.not_edit));
            },

            eventCreate() {
                return !this.editedItem.id
            }
        },

        watch: {
            // Очищаем валидацию при закрытии модального окна создания/редактирования записи
            show() {
                if(this.$refs.form) {
                    this.$refs.form.resetValidation();
                }
            },
        },

        methods: {
            /** Сохранить форму **/
            confirmDialogSave() {
                if (this.$refs.form.validate()) {
                    this.$emit('confirmDialogSave')
                }
            },
        }
    }
</script>

<style scoped>
    .fix-form {
        max-height: 700px;
        overflow-y: auto;
    }
</style>