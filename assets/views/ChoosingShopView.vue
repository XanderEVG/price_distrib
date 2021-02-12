<template>
    <v-main>
        <toolbar></toolbar>

        <v-container fluid class="full-container">
            <div class="search-container">
                <v-row justify="center" style="margin-bottom: 16px;">
                    <div style="width: 450px;" class="d-flex">
                        <!--Поиск магазина в списке.-->
                        <v-text-field
                                solo
                                autofocus
                                flat
                                class="search-input"
                                prepend-inner-icon="search"
                                label="Поиск магазина"
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
                            sm="10"
                            offset-md="4"
                            offset-lg="4"
                            offset-sm="1"
                            v-for="shop in shopsList"
                            :key="shop.id"
                    >
                        <v-card
                                flat
                                class="shop-card"
                                :data-id="shop.id"
                                :data-name="shop.name"
                                v-on:click="select_shop"
                        >
                            <v-card-title>{{shop.name}}</v-card-title>
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
        name: "ChoosingShopView",
        components: { Toolbar },
        data: () => {
            return {
                searchString: "",
                current_city_id: null
            };
        },
        methods: {
            select_shop(e) {
                let shop_id = e.target.closest(".shop-card").dataset.id;
                let shop_name = e.target.closest(".shop-card").dataset.name;
                this.$store.commit('setCurrentShopId', shop_id);
                this.$store.commit('setCurrentShopName', shop_name);
                this.$router.push('/statistic');
            },
        },
        computed: {
            /**
             * Возвращает список магазинов фильтруя их по наименованию согласно запроса в строке поиска.
             * @returns Array
             */
            shopsList() {
                const available_shops = this.$store.getters.availableShops(this.current_city_id);
                if (this.searchString === "") {
                    return available_shops;
                } else {
                    return available_shops.filter((shop) => {
                        const shopName = shop.name.toLowerCase();
                        const term = this.searchString.toLowerCase();

                        return shopName.indexOf(term) > -1;
                    });
                }
            },
        },
        created() {
            this.current_city_id = this.$store.getters.getCurrentCityId;
            const available_shops = this.$store.getters.availableShops(this.current_city_id);
            if (available_shops.length === 1) {
                let shop_id = available_shops[0].id;
                this.$store.commit('setShopId', shop_id)
                this.$router.push('/statistic');
            }
        },
    };
</script>

<style scoped>
</style>
