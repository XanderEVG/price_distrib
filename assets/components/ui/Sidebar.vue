<template>
    <v-navigation-drawer
        fixed
        left
        :mini-variant="mini"
        app
        permanent
        width="217"
        class="custom-sidebar"
    >
        <div>
            <div style="text-align: center; position: relative; padding: 15px 0;">
                <router-link :to="'/select_city'" class="button-green t10"  v-if="showSelectCityBtn">
                    <v-icon color="white">keyboard_arrow_left</v-icon>
                    <span v-show="!mini">Выбор магазина</span>
                </router-link>
            </div>
            <v-list dense>
                <div class="v-list__spacer">
                    <!-- Выводим элементы меню -->
                    <template v-for="(item, index) in items">
                        <!-- Если есть дочернии, отображаем в виде группы -->
                        <v-list-group      
                            v-if="item.children"
                            v-model="item.active"
                            :key="item.text"
                            class="nopadleft list-item-group-show"
                            append-icon="keyboard_arrow_down"
                            pl-4
                        >
                            <v-list-item slot="activator">
                                <v-list-item-action>
                                    <v-icon>{{ item.icon }}</v-icon>
                                </v-list-item-action>
                                <v-list-item-content >
                                    <v-list-item-title>
                                        {{ item.text }}
                                    </v-list-item-title>
                                </v-list-item-content>
                            </v-list-item>
                            <v-list-item
                                v-for="(child, i) in item.children"
                                :key="index + '.' + i"
                                :to="child.to ? { name: child.to, params: { } } : child.to"
                            >
                                <!-- Добавляем отступы для дочерних пунктов если находимся не в мини режиме -->
                                <v-list-item-action v-if="!mini" class="pl-3">
                                    <v-icon>{{ child.icon }}</v-icon>
                                </v-list-item-action>
                                <!-- Иначе без них -->
                                <v-list-item-action v-else>
                                    <v-icon>{{ child.icon }}</v-icon>
                                </v-list-item-action>
                                <v-list-item-content>
                                    <v-list-item-title>
                                        {{ child.text }}
                                    </v-list-item-title>
                                </v-list-item-content>
                            </v-list-item>
                        </v-list-group>
                        <!-- Иначе в виде одиночных элементов -->
                        <v-list-item
                            v-else
                            :key="index"
                            :to="item.to ?
                                {name: item.to, params: { } } :
                                item.to"
                        >
                            <v-list-item-action>
                                <v-icon>{{ item.icon }}</v-icon>
                            </v-list-item-action>
                            <v-list-item-content>
                                <v-list-item-title>
                                    {{ item.text }}
                                </v-list-item-title>
                            </v-list-item-content>
                        </v-list-item>
                    </template>
                </div>
            </v-list>
        </div>
        <div class="custom-sidebar__collapse vvIcon" @click="toggleMiniDrawer">
            <span v-show="!mini">Свернуть</span>
            <v-icon v-show="mini" dence medium class="v-icon-normal">label_important</v-icon>
        </div>
    </v-navigation-drawer>
</template>

<script>
    import { SWITCH_SIDEBAR } from '../../store/modules/auth/mutations-types';

    export default {
        name: "Sidebar",
        data (){
            return {
                mini: this.$store.getters.getSidebarState,
                path: '/select_city',
                pathName: 'Выбор магазина',
            }
        },
        methods: {
            toggleMiniDrawer () {
                this.$store.commit(SWITCH_SIDEBAR, !this.mini);
                this.mini = !this.mini;
            },
        },
        computed: {
            showSelectCityBtn() {
                return true;
            },
            items() {
                let items = [];

                let user_roles = this.$store.getters.roles;

                if (user_roles.includes('ROLE_USER')) {
                    items.push({icon: 'table_chart', text: 'Сводная', to: 'StatisticInfoView', header: true, active: true,});
                    items.push({icon: 'device_hub', text: 'Устройства', to: 'DevicesView'});
                    items.push({icon: 'price_change', text: 'Товары и цены', to: 'ProductsView'});
                }

                if (user_roles.includes('ROLE_ADMIN')) {
                    items.push({icon: 'location_city', text: 'Города', to: 'CitiesView'});
                    items.push({icon: 'shopping_cart', text: 'Магазины', to: 'ShopsView'});
                    items.push({icon: 'supervisor_account', text: 'Пользователи', to: 'UsersView'});
                    items.push({icon: 'settings', text: 'Настройки', to: 'SettingsView'});
                }

                return items;
            }
        }
    }
</script>

<style scoped>
    .vvIcon .v-icon.v-icon:after {
        transform: none;
    }

    .button-green {
        text-decoration: none;
        color: #fff;
        background: #21A357;
        display: block;
        padding: 10px 0;
        font-size: .875rem;
        opacity: .8;
    }

    .button-green:hover {
        opacity: 1;
    }
</style>
