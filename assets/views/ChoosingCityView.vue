<template>
    <v-main>
        <toolbar></toolbar>

        <v-container fluid class="full-container">
            <div class="search-container">
                <v-row justify="center" style="margin-bottom: 16px;">
                    <div style="width: 450px;" class="d-flex">
                        <!--Поиск города в списке.-->
                        <v-text-field
                                solo
                                autofocus
                                flat
                                class="search-input"
                                prepend-inner-icon="search"
                                label="Поиск города"
                                v-model="searchString"
                                height="50"
                        ></v-text-field>
                    </div>
                </v-row>

                <v-row style="margin-bottom: 20px;">
                    <v-col
                            class=""
                            cols="12"
                            md="4"
                            lg="4"
                            sm="12"
                            v-for="city in citiesList"
                            :key="city.id"
                    >
                        <v-card
                                flat
                                class="city-card"
                                :data-id="city.id"
                                :data-name="city.name"
                                v-on:click="select_city"
                        >
                            <v-card-title>{{city.name}}</v-card-title>
                        </v-card>
                    </v-col>
                </v-row>
            </div>
        </v-container>

        <!-- Вывод сообщений. -->
        <v-snackbar v-model="notification.show" :color="notification.type" top>{{
            notification.text
            }}</v-snackbar>
    </v-main>
</template>

<script>
    import NotificationsMixin from "../mixins/notifications.js";
    import Toolbar from "../components/ui/Toolbar";
    export default {
        mixins: [NotificationsMixin],
        name: "ChoosingCityView",
        components: { Toolbar },
        data: () => {
            return {
                searchString: ""
            };
        },
        methods: {
            select_city(e) {
                let city_id = e.target.closest(".city-card").dataset.id;
                let city_name = e.target.closest(".city-card").dataset.name;
                this.$store.commit('setCurrentCityId', city_id);
                this.$store.commit('setCurrentCityName', city_name);
                this.$router.push('/select_shop');
            },
        },
        computed: {
            /**
             * Возвращает список городов фильтруя их по наименованию согласно запроса в строке поиска.
             * @returns Array
             */
            citiesList() {
                const available_cities = this.$store.getters.availableCities;
                if (this.searchString === "") {
                    return available_cities;
                } else {
                    return available_cities.filter((city) => {
                        const cityName = city.name.toLowerCase();
                        const term = this.searchString.toLowerCase();

                        return cityName.indexOf(term) > -1;
                    });
                }
            },
        },
        created() {
            const available_cities = this.$store.getters.availableCities;

            if (available_cities.length === 1) {
                let city_id = available_cities[0].id;
                this.$store.commit('setCityId', city_id)
                this.$router.push('/select_shop');
            }
        },
    };
</script>

<style scoped>
</style>
