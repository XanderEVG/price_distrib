<template>
    <v-menu offset-y>
        <template v-slot:activator="{ on }">
            <v-toolbar-title v-on="on" class="toolbar-title-custom">
                <span>{{ user.name }}</span>
                <i class="material-icons" v-if="!mobile">keyboard_arrow_down</i>
                 <i class="material-icons" v-else>keyboard_arrow_right</i>
            </v-toolbar-title>
        </template>
        <v-list dense>
            <v-list-item @click="logout">
                <v-list-item-title>Выход</v-list-item-title>
            </v-list-item>
        </v-list>
    </v-menu>
</template>

<script>
    import { LOGOUT_REQUEST } from '../../store/modules/auth/action-types';

    export default {
        name: 'UserMenu',
        props: ['mobile'],
        methods: {
            /**
             * Выход из системы.
             */
            logout() {
                this.$store.dispatch(LOGOUT_REQUEST, this.credentials).then(() => {
                    // TODO: Реализовать обновление CSRF-токена по другому если это возможно.
                    window.location = '/login';
                    // this.$router.push('/login');
                }).catch((error) => {
                    this.showNotification({ text: error.message, type: 'error' });
                });
            }
        },
        computed: {
            user() {
                return this.$store.getters.user;
            }
        },
    };
</script>

<style scoped>

</style>
