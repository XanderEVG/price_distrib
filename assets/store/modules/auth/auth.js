import axios from 'axios';
import qs from 'qs';
import {
    CLEAR_ACCOUNT_INFORMATION,
    SET_ACCOUNT_INFORMATION,
    SET_LOGGED_IN,
    SET_LOGGED_OUT,
    SWITCH_SIDEBAR,
    UPDATE_RIGHTS_INFORMATION,
} from './mutations-types';
import { LOGIN_REQUEST, LOGOUT_REQUEST} from './action-types';

const state = {
    // Признак, аутентифицирован пользователь или нет.
    authenticated: false,
    // Информация о пользователе.
    miniSidebar: false,
    account: {
        // Информация о пользователе.
        user: {
            id: null,
            username: '',
            fio: '',
            role: []
        },
        // Права пользователя.
        availableCities: [],
        availableShops: [],
    },
};

const getters = {
    /**
     * Возвращает информацию аутентифицирован пользователь или нет.
     * @param state Состояние.
     * @returns {boolean}
     */
    isAuthenticatedUser: state => state.authenticated,

    /**
     * Возвращает информацию о пользователе.
     * @param state Состояние.
     * @returns {Object}
     */
    user: state => state.account.user,

    /**
     * Возвращает роль пользователя.
     * @param state Состояние.
     * @returns {Object}
     */
    roles: state => state.account.user.roles,

    /**
     * Возвращает информацию о доступных городах пользователя.
     * @param state Состояние.
     * @returns {Array}
     */
    availableCities: state => state.account.availableCities,

    /**
     * Возвращает информацию о доступных магазинах пользователя.
     * @param state Состояние.
     * @returns {Array}
     */
    availableShops: state => city_id => {
        return state.account.availableShops.filter(function(shop) {
            return shop.city.id == city_id;
        });
    },


    /**
     * Возвращает состояние сайдбара.
     * @param state Состояние.
     * @returns {bool}
     */
    getSidebarState: state => state.miniSidebar,

};

const actions = {
    /**
     * Аутентификация пользователя.
     * @param commit Запуск мутаций.
     * @param credentials Данные для входа (логин и пароль).
     * @returns {Promise<any>}
     */
    [LOGIN_REQUEST]: ({ commit }, credentials) => new Promise((resolve, reject) => {
        // Пробуем аутентифицировать пользователя.
        axios.post('/api/authentication/login', qs.stringify({
            username: credentials.username,
            password: credentials.password,
        // Обработка успешного выполнения запроса.
        })).then((response) => {
            // Если пользователь прошёл аутентификацию, сохраняем информацию о пользователе в хранилище и
            // перенаправляем на страницу выбора реестра.
            if (response.data.success) {
                commit(SET_ACCOUNT_INFORMATION, {
                    user: {
                        id: response.data.id,
                        username: response.data.username,
                        fio: response.data.fio,
                        roles: response.data.roles
                    },
                    availableCities: response.data.rights.cities,
                    availableShops: response.data.rights.shops,
                });

                commit(SET_LOGGED_IN);
                resolve();
            // Иначе, возвращаем вернувшееся сообщение об ошибке.
            } else {
                reject(new Error(response.data.error));
            }
        // Обработка ошибки отправки запроса.
        }).catch(() => reject(new Error('Не удалось выполнить запрос к серверу.')));
    }),
    /**
     * Выход из системы.
     * @param commit Запуск мутаций.
     * @returns {Promise<any>}
     */
    [LOGOUT_REQUEST]: ({ commit }) => new Promise((resolve, reject) => {
        // Сообщаем бэкенду о выходе из системы.
        axios.get('/api/authentication/logout').then((response) => {
            commit(CLEAR_ACCOUNT_INFORMATION);
            commit(SET_LOGGED_OUT);
            resolve();
        // Обработка ошибки отправки запроса.
        }).catch(() => reject(new Error('Не удалось выполнить запрос к серверу.')));
    })
};

const mutations = {
    /**
     * Очищает данные об аккаунте пользователя.
     * @param state Состояние.
     */
    [CLEAR_ACCOUNT_INFORMATION]: state => {
        state.account = {
            user: {
                id: null,
                username: '',
                fio: '',
                role: []
            },
            rights: {},
        };
        state.entities = null;
    },
    /**
     * Отмечает пользователя как аутентифицированного.
     * @param state Состояние.
     */
    [SET_LOGGED_IN]: state => state.authenticated = true,
    /**
     * Убирает отметку аутентифицированного пользователя.
     * @param state Состояние.
     */
    [SET_LOGGED_OUT]: state => state.authenticated = false,
    /**
     * Устанавливает данные о пользовательском аккаунте .
     * @param state Состояние.
     * @param data Данные о пользовательском аккаунте.
     */
    [SET_ACCOUNT_INFORMATION]: (state, data) => state.account = data,

    /**
     * Меняет значение сайдбара
     * @param state Состояние.
     * @param mini bool
     */
    [SWITCH_SIDEBAR]: (state, val) => state.miniSidebar = val,

    /**
     * Устанавливает данные о пользовательском аккаунте .
     * @param state Состояние.
     * @param data Данные о пользовательском аккаунте.
     */
    [UPDATE_RIGHTS_INFORMATION]: (state, data) => state.account.rights = data,
};

export default {
    state,
    getters,
    actions,
    mutations,
};
