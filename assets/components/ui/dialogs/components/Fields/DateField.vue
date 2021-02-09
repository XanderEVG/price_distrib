<template>
    <v-menu
        v-model="header.show_calendar"
        :close-on-content-click="false"
        offset-y
    >
        <template v-slot:activator="{ on }">
            <v-text-field
                :value="formatDate"
                class="text-input"
                readonly
                clearable
                solo
                flat
                @click:clear="editedItem[header.name] = null"
                v-on="(!header.readonly) ? on : null"
                :rules="getRules(header)"
            ></v-text-field>
        </template>
        <v-date-picker
            v-model="editedItem[header.name]"
            @input="header.show_calendar = false"
            color="blue-grey lighten-1"
            landscape
            class="pa-0 ma-0"
            locale="ru"
        ></v-date-picker>
    </v-menu>
</template>

<script>

    export default {
        props: ['header', 'editedItem'],

        computed: {
            // Приведение даты к нужному формату
            formatDate() {
                let value = this.editedItem[this.header.name];
                if (!value) return null;

                const [year, month, day] = value.split('-');
                return `${day}.${month}.${year}`
            }
        },
    }
</script>

<style scoped>

</style>