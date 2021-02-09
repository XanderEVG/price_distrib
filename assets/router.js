import Vue from 'vue';
import Router from 'vue-router';

import ExtLayout from './components/layouts/ExtLayout.vue';
import LoginView from './views/LoginView.vue';
import PageNotFoundView from './views/PageNotFoundView.vue';
import ChoosingCityView from './views/ChoosingCityView.vue';
import ChoosingShopView from './views/ChoosingShopView.vue';
import StatisticInfoView from './views/StatisticInfoView.vue';
import UsersView from './views/UsersView.vue';
import DevicesView from './views/DevicesView.vue';
import ProductsView from './views/ProductsView.vue';
import CitiesView from './views/CitiesView.vue';
import ShopsView from './views/ShopsView.vue';
import SettingsView from './views/SettingsView.vue';

import store from './store/index';

Vue.use(Router);

export default new Router({
    mode: 'history',
    routes: [
        {
            path: '/',
            name: 'ExtLayout',
            component: ExtLayout,
            redirect: {name: 'StatisticInfoView'},
            children: [
                {
                    path: '/statistic',
                    name: 'StatisticInfoView',
                    component: StatisticInfoView,
                },
                {
                    path: '/devices',
                    name: 'DevicesView',
                    component: DevicesView,
                },
                {
                    path: '/products',
                    name: 'ProductsView',
                    component: ProductsView,
                },
                {
                    path: '/cities',
                    name: 'CitiesView',
                    component: CitiesView,
                },
                {
                    path: '/shops',
                    name: 'ShopsView',
                    component: ShopsView,
                },
                {
                    path: '/users',
                    name: 'UsersView',
                    component: UsersView,
                },
                {
                    path: '/settings',
                    name: 'SettingsView',
                    component: SettingsView,
                },
            ]
        },
        // Страница входа в систему.
        {
            path: '/login',
            name: 'LoginView',
            component: LoginView,
        },
        // Страница выбора текущего города
        {
            path: '/select_city',
            name: 'ChoosingCityView',
            component: ChoosingCityView,
        },
        // Страница выбора текущего магазина
        {
            path: '/select_shop',
            name: 'ChoosingShopView',
            component: ChoosingShopView,
        },
        // Обработка перехода не неизвестные адреса.
        {
            path: '*',
            component: PageNotFoundView,
            name: 'PageNotFoundView',
        },
    ],
});