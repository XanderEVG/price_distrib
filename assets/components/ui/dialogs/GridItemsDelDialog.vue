<template>
    <v-dialog v-model="show" max-width="290">
        <v-card>
            <v-toolbar dark dense flat color="teal">
                <v-icon left>done</v-icon>
                <v-toolbar-title class="white--text">Удаление строк</v-toolbar-title>
            </v-toolbar>

            <v-card-text>
                Вы действительно хотите
                <span class="font-weight-medium">удалить</span>
                выбранные записи?
            </v-card-text>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn text @click="$emit('closeDialogDel')">Отмена</v-btn>
                <v-btn color="teal" text @click="$emit('confirmDialogDel')">Да</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
    export default {
        props: ['show']
    }
</script>