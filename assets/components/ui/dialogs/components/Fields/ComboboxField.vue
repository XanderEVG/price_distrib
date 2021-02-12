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
    return-object
    :rules="getRules(header)"
    :readonly="header.readonly"
    @change="changed(header.name, editedItem[header.name])"
    dense
    clearable
  ></v-autocomplete>
</template>

<script>
export default {
  props: ["header", "editedItem", "tableName"],
  methods: {
    changed(field_name, value) {
      if(this.tableName === "users" && field_name === "cities") {
        let idx = value.map(a => a.id);
        this.$emit('changedCityIdx', idx)
      }
    }
  }
};
</script>

<style scoped>
</style>