<template>
    <v-textarea
        v-model="editedItem[header.name]"
        flat
        outlined
        solo
        :rules="getRules(header)"
        :readonly="header.readonly"
    ></v-textarea>
</template>

<script>
    export default {
        props: ['header', 'editedItem'],
    }
</script>

<style scoped>

</style>