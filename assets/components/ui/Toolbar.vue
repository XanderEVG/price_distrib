<template>
    <div>
        <v-toolbar color="blackblue" light fixed elevation="0" height="60" class="fxd top_index" v-resize="resize">
            <!-- Логотип -->
            <logo></logo>
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
            }
        },
        mounted() {
            this.path = this.$route.path;
            this.resize();
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
