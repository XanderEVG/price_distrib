<template>
    <v-main>
        <sidebar></sidebar>
        <toolbar></toolbar>
        <div class="router-view__wrap">
            <router-view></router-view>
        </div>
    </v-main>
</template>

<script>
    import Sidebar from "../ui/Sidebar.vue";
    import Toolbar from "../ui/Toolbar.vue";

    export default {
        name: "ExtLayout",
        components: { Sidebar, Toolbar },
    }
</script>

<style scoped>

</style>
