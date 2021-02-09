<template>
    <v-text-field
        v-model="editedItem[header.name]"
        color="teal"
        class="text-input"
        type="number"
        solo
        flat
        :rules="getRules(header)"
        :readonly="header.readonly"
    ></v-text-field>
</template>

<script>
export default {
  props: ["header", "editedItem"],
};
</script>

<style scoped>
</style>