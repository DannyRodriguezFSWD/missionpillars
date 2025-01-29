import Vue from 'vue'
import Vuex from 'vuex'
import crmmodal from '../../../modules/crmmodal'
import crmmodules from './modules/crmmodules'
import SimpleVueValidation from 'simple-vue-validator';

Vue.use(Vuex)
Vue.use(SimpleVueValidation);

const debug = process.env.NODE_ENV !== 'production'

export default new Vuex.Store({
    state: {
        url: '',
        isLoading: false,
        action: false,
        creditCardRequired: false
    },
    mutations: {
        SET_URL_STATE: (state, value) => {
            state.url = value
        },
        SET_IS_LOADING_STATE: (state, value) => {
            state.isLoading = value
        },
        SET_ACTION_STATE: (state, value) => {
            state.action = value
        },
        SET_CREDIT_CARD_REQUIRED_STATE: (state, value) => {
            state.creditCardRequired = value
        },
        SET_PROMOTION_CODE_STATE: (state, value) => {
            state.promotionCode = value
        },
    },
    modules: {
        crmmodal,
        crmmodules
    },
    strict: debug,
    actions: {
        setUrlStateAction: ({commit, dispatch, getters}, value) => {
            commit('SET_URL_STATE', value)
            return new Promise((resolve, reject) => {
                resolve(getters.getUrlState)
            })
        },
        isLoadingAction: ({commit, dispatch, getters}, value) => {
            commit('SET_IS_LOADING_STATE', value)
        }
    },
    getters: {
        getUrlState: state => {
            return state.url
        },
        getIsLoadingState: state => {
            return state.isLoading
        },
        getActionState: state => {
            return state.action
        }
    }
});