<template>
    <v-tooltip bottom>
        <template v-slot:activator="{ on }">
            <v-btn text icon @click="$emit('getDataFromApi')" v-on="on">
                <v-icon>cached</v-icon>
            </v-btn>
        </template>
        <span>Обновить</span>
    </v-tooltip>
</template>

<script>
    export default {
    }
</script>

<style scoped>

</style>