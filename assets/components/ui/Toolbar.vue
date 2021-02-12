<template>
    <div>
        <v-toolbar color="blackblue" light fixed elevation="0" height="60" class="fxd top_index" v-resize="resize">
            <!-- Логотип -->
            <logo></logo>

            <!-- Название города -->
            <div v-if="current_city_id" class="current_city">{{current_city_name}}</div>

            <!-- Название Магазина -->
            <div v-if="current_shop_id" class="current_shop">{{current_shop_name}}</div>

            <!-- Разрыв  -->
            <v-spacer></v-spacer>


            <!-- Меню пользователя -->
            <user-menu v-if="!mobile" :mobile="mobile"></user-menu>
            <!-- А в мобилках гамбургер -->
            <hamburger :mobile="mobile" :path="path" v-else></hamburger>
        </v-toolbar>
    </div>
</template>

<script>
    import UserMenu from './UserMenu';
    import Logo from './Logo';
    import Hamburger from './Hamburger';

    export default {
        name: "Toolbar",
        components: { UserMenu, Logo, Hamburger },
        data() {
            return {
                path: null,
                mobile: false,
                current_city_id: null,
                current_city_name: null,
                current_shop_id: null,
                current_shop_name: null,
            }
        },
        mounted() {
            this.path = this.$route.path;
            this.resize();
            this.current_city_id = this.$store.getters.getCurrentCityId;
            this.current_city_name = this.$store.getters.getCurrentCityName;
            this.current_shop_id = this.$store.getters.getCurrentShopId;
            this.current_shop_name = this.$store.getters.getCurrentShopName;
        },
        methods: {
            resize() {
                let elWidth = window.innerWidth;

                if (elWidth <= 960) {
                    this.mobile = true
                } else {
                    this.mobile = false
                }

            }
        }
    }
</script>

<style scoped lang="scss">
    .top_index {
        z-index: 100;
    }
</style>
