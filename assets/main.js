import Vue from 'vue';
import Vuetify from 'vuetify';
import vClickOutside from 'v-click-outside';
import App from './App.vue';
import 'vuetify/dist/vuetify.min.css';
import router from './router';
import store from './store/index';
import axios from 'axios';
import NotificationsMixin from './mixins/notifications';
import RulesMixin from './mixins/rules';
import '../assets/css/style.sass';
import 'vue2-datepicker/index.css';
import 'vue2-datepicker/locale/ru';
import vueHeadful from 'vue-headful';

Vue.use(Vuetify);
Vue.use(vClickOutside);

Vue.component('vue-headful', vueHeadful);

// Передаём в хидере каждого запроса CSRF-токен.
axios.defaults.headers.common = {
    'X-CSRF-TOKEN': document.getElementsByName('csrf-token')[0].getAttribute('content'),
};

// Глобальный хук для всех маршрутов проверяющий авторизован или нет пользователь.
// Если нет, то перенаправляет на страницу входа.
router.beforeEach((to, from, next) => {
    if ((to.fullPath !== '/login') && (to.fullPath !== '/pass_recovery') && (to.fullPath !== '/pass_change') &&
        (!store.getters.isAuthenticatedUser)) {
        next('/login');
        location.reload()
    } else {
        next();
    }
});

/** Если любой из запросов вернул авторизации,то перенаправляем на страницу логина */
axios.interceptors.response.use(((response) => {
    if (!response.data.success && response.data.critical_error && response.data.critical_error === 'NOT_LOGIN') {
        router.push('/login');
        location.reload()
    }
    return response
}), error => Promise.reject(error));

// Используем глобальные миксины для вывода сообщений
Vue.mixin(NotificationsMixin);
Vue.mixin(RulesMixin);


const vuetify = new Vuetify({
    theme: {
        themes: {
            light: {
                coffie: '#FBFAF9',
                blackblue: '#253F4B',
                teal: '#253F4B',
            },
        },
    },
    icons: {
        iconfont: 'mdiSvg'
    }
});

// Используем EventBus для доступа к дочерним данным из родительского или между дочерними
export const eventBus = new Vue();

new Vue({
    vuetify: vuetify,
    router,
    store,
    template: '<App/>',
    components: { App },
}).$mount('#app');
