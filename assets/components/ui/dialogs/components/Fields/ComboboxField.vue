<template>
  <v-autocomplete
    v-model="editedItem[header.name]"
    :items="header.items"
    :multiple="header.multiple"
    no-data-text="Нет данных"
    color="teal"
    class="martop select"
    item-text="name"
    item-value="id"
    solo
    flat
    :rules="getRules(header)"
    :readonly="header.readonly"
    dense
    clearable
  ></v-autocomplete>
</template>

<script>
export default {
  props: ["header", "editedItem"],
};
</script>

<style scoped>
</style>