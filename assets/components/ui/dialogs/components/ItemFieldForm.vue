<template>
    <!-- Отображаемое поле в зависимости от типа -->
    <div class="form-line">
        <v-row class="align-center" no-gutters>
            <v-col cols="12" :sm="(fullWidth) ? '1' : '4'">
                <div class="form-label">{{ header.ru_name }}:</div>
            </v-col>
            <v-col cols="12" :class="[
                    {'col-sm-11 pr15': !header.notLabel && fullWidth},
                    {'col-sm-8 pr15': !header.notLabel && !fullWidth}
                ]">

                <text-field
                    v-if="header.field_type === 'text'"
                    :header="header"
                    :edited-item="editedItem"
                    :event-create="eventCreate"
                ></text-field>

                <grid-field
                    v-else-if="header.field_type === 'grid'"
                    :header="header"
                    :edited-item="editedItem"
                ></grid-field>

                <integer-field
                    v-else-if="header.field_type === 'integer' || header.field_type === 'float'"
                    :header="header"
                    :edited-item="editedItem"
                ></integer-field>

                <boolean-field
                    v-else-if="header.field_type === 'boolean'"
                    :header="header"
                    :edited-item="editedItem"
                ></boolean-field>

                <file-field
                    v-else-if="header.field_type === 'file'"
                    :header="header"
                    :edited-item="editedItem"
                ></file-field>

                <textarea-field
                    v-else-if="header.field_type === 'textarea'"
                    :header="header"
                    :edited-item="editedItem"
                ></textarea-field>

                <date-field
                    v-else-if="header.field_type === 'date'"
                    :header="header"
                    :edited-item="editedItem"
                ></date-field>

                <combobox-field
                        v-else-if="header.field_type === 'combobox'"
                        :header="header"
                        :edited-item="editedItem"
                ></combobox-field>
            </v-col>
        </v-row>
    </div>
</template>

<script>
    import TextField from './Fields/TextField.vue';
    import IntegerField from './Fields/IntegerField.vue';
    import BooleanField from './Fields/BooleanField.vue';
    import ComboboxField from './Fields/ComboboxField.vue';
    import FileField from './Fields/FileField.vue';
    import TextareaField from './Fields/TextareaField.vue';
    import DateField from './Fields/DateField.vue';
    import GridField from './Fields/GridField.vue';

    export default {
        components: {
            TextField, IntegerField, BooleanField, ComboboxField, FileField, TextareaField, DateField, GridField
        },

        props: ['header', 'editedItem', 'eventCreate', 'fullWidth'],

    };
</script>

<style scoped>

</style>
