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
        shop_id: null,
    },
    getters: {
        getCityId: state => state.city_id,
        getShopId: state => state.shop_id,
    },
    mutations: {
        setCityId: (state, city_id) => state.city_id = city_id,
        setShopId: (state, shop_id) => state.shop_id = shop_id,
    },
    modules: {
        auth,
        entities,
    },
});
