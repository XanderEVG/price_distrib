import axios from 'axios';
import {
    SET_ENTITIES_LIST,
    SET_ENTITY,
    SELECT_ENTITY,
    CREATE_ENTITY,
    DELETE_ENTITY,
    SET_ENTITIES_TOTAL,
} from './mutations-types';

import { UPDATE_ENTITIES } from './action-types';

const state = {
    // Список таблиц
    entities: {
        available: null,
        total: null,
        current: null,
    },
};

const getters = {
    entities: state => state.entities.available,
    total: state => state.entities.total,
    currentEntity: state => state.entities.current,
};

const actions = {
    [UPDATE_ENTITIES]: ({ commit }, reestrId) => new Promise((resolve, reject) => {
        axios.get('/api/entities/get_list', {
            params: {
                reestr_id: reestrId,
                moderate: false,
                start: 0,
                limit: 1000,
                filters: '{}',
                sort: '',
                sort_dir: '',
            },
        }).then(response => {
            if (response.data.rez) {
                commit(SET_ENTITIES_LIST, response.data.items);
                commit(SET_ENTITIES_TOTAL, response.data.total);
                resolve();
            } else {
                reject(new Error(response.data.msg));
            }
        });
    }),
};

const mutations = {
    [SET_ENTITIES_LIST]: (state, data) => state.entities.available = data,
    [SET_ENTITIES_TOTAL]: (state, data) => state.entities.total = data,
    [SET_ENTITY]: (state, data) => {
        const foundId = state.entities.available.findIndex(x => x.id === data.id);
        state.entities.available[foundId] = data;
    },
    [SELECT_ENTITY]: (state, entityId) => state.entities.current = entityId,
    [CREATE_ENTITY]: (state, data) => state.entities.available.unshift(data),
    [DELETE_ENTITY]: (state, id) => state.entities.available = state.entities.available.filter(e => e.id !== id),
};

export default {
    state,
    getters,
    actions,
    mutations,
};
