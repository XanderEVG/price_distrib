<template>
  <v-autocomplete
    v-model="editedItem[header.name]"
    :items="header.items"
    :multiple="header.multiple"
    no-data-text="Нет данных"
    color="teal"
    class="martop select"
    :item-text="getItemText(header)"
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
    getItemText(header) {
      if(header.name === "devices") {
        return "mac";
      }
      return "name";
    },
    changed(field_name, value) {
      if (this.tableName === "users" && field_name === "cities") {
        let idx = value.map(a => a.id);
        this.$emit('changedCityIdx', idx)
      }

      if (this.tableName === "products" && field_name === "city") {
        this.$emit('changedCityIdx', value.id)
      }
    }
  }
};
</script>

<style scoped>
</style>