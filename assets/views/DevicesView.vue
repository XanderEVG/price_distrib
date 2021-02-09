<template>
    <v-container fluid class="no-pad coffie" light>
        <v-row class="not-full-height">
            <v-col class="col-12 col-md-6 login-right-col d-flex">
                <v-overlay :value="overlay">
                    <v-progress-circular indeterminate size="64"></v-progress-circular>
                </v-overlay>


                <v-row>
                    <v-col class="col-12 col-md-6 login-right-col d-flex">
                     Здесь будет размещена информация о устройствах
                    </v-col>
                </v-row>
                
            </v-col>
        </v-row>
    </v-container>
</template>

<script>
    import NotificationsMixin from "../mixins/notifications.js";
    import axios from 'axios';

    export default {
        data: () => {
            return {
                overlay: false,
            };
        },
        methods: {

        },
        mounted() {

        },
    };
</script>

<style scoped>
</style>
