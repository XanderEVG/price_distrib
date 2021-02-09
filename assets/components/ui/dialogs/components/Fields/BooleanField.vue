<template>
    <v-checkbox
        v-model="boolVal"
        color="teal"
        :readonly="header.readonly"
    ></v-checkbox>
</template>

<script>
    export default {
        props: ['header', 'editedItem'],

        computed: {
            boolVal: {
                get() {
                    return (this.editedItem[this.header.name] === true) ||
                        (this.editedItem[this.header.name] === "true")
                },
                set(val) {
                    this.editedItem[this.header.name] = val
                }
            }
        }
    }
</script>

<style scoped>

</style>