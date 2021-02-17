import Vue from 'vue';
import Vuex from 'vuex';
import createPersistedState from 'vuex-persistedstate';
import auth from './modules/auth/auth';
import entities from './modules/data/entities';


Vue.use(Vuex);

export default new Vuex.Store({
    plugins: [createPersistedState()],
    state: {
        city_id: null,
        city_name: null,
        shop_id: null,
        shop_name: null,
    },
    getters: {
        getCurrentCityId: state => state.city_id,
        getCurrentCityName: state => state.city_name,
        getCurrentShopId: state => state.shop_id,
        getCurrentShopName: state => state.shop_name,
    },
    mutations: {
        setCurrentCityId: (state, city_id) => state.city_id = parseInt(city_id),
        setCurrentCityName: (state, city_name) => state.city_name = city_name,
        setCurrentShopId: (state, shop_id) => state.shop_id = parseInt(shop_id),
        setCurrentShopName: (state, shop_name) => state.shop_name = shop_name,
    },
    modules: {
        auth,
        entities,
    },
});
