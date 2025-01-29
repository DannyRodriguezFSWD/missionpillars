import Vue from 'vue'
import Vuex from 'vuex'
import crmmodal from '../modules/crmmodal'
import accounting_journal_entries from '../modules/accounting_journal_entries'
import accounting_transactions from '../modules/accounting_transactions'
import SimpleVueValidation from 'simple-vue-validator';
import tags from '../modules/tags'

Vue.use(Vuex)
Vue.use(SimpleVueValidation);

const debug = process.env.NODE_ENV !== 'production'

export default new Vuex.Store({
    state: {
        url: '',
        is_loading: false,
        show_loading_screen: true,
        current_date: null,
        helpers: {
            _amount: 0,
            _total_amount: 0,
            _string: '',
            _organization_purpose: null,
            _titles: {
                debit: null,
                credit: null
            },
        }
    },
    mutations: {
        SET_IS_LOADING_STATE: (state, value) => {
            state.is_loading = value
        },
        SET_CURRENT_DATE_STATE: (state, value) => {
            state.current_date = value
        },
        SET_HELPER_AMOUNT_STATE: (state, value) => {
            state.helpers._amount = value
        },
        SET_HELPER_STRING_STATE: (state, value) => {
            state.helpers._string = value
        },
        SET_HELPER_TOTAL_AMOUNT_STATE: (state, value) => {
            state.helpers._total_amount = value
        },
        SET_HELPER_ORGANIZATION_PURPOSE_STATE: (state, value) => {
            state.helpers._organization_purpose = value
        },
        SET_HELPER_TITLES_STATE: (state, value) => {
            state.helpers._titles = value
        },
        SET_SHOW_LOADING_SCREEN_STATE: (state, value) => {
            state.show_loading_screen = value
        }
    },
    modules: {
        crmmodal,
        JournalEntries: accounting_journal_entries,
        AccountingTransactions: accounting_transactions,
        tags,
    },
    strict: debug,
    actions: {
        setIsLoadingState: ({commit, dispatch, getters}, value) => {
            commit('SET_IS_LOADING_STATE', value)
        },
        setShowLoadingState: ({commit, dispatch, getters}, value) => {
            commit('SET_SHOW_LOADING_SCREEN_STATE', value)
        },
        setCurrentDate: ({commit, dispatch, getters}, payload) => {
            var today = new Date()
            var dd = today.getDate()
            var mm = today.getMonth() + 1 //January is 0!
            var yyyy = today.getFullYear()
            if (dd < 10) {
                dd = '0' + dd
            }
            if (mm < 10) {
                mm = '0' + mm
            }
            today = mm + '/' + dd + '/' + yyyy
            
            commit('SET_CURRENT_DATE_STATE', today)
            return new Promise((resolve, reject) => {
                resolve(today)
            })
        },
        setHelperAmountState: ({commit, dispatch, getters}, value) => {
            commit('SET_HELPER_AMOUNT_STATE', value)
        },
        setHelperTotalAmountState: ({commit, dispatch, getters}, value) => {
            commit('SET_HELPER_TOTAL_AMOUNT_STATE', value)
        },
        setHelperStringState: ({commit, dispatch, getters}, value) => {
            commit('SET_HELPER_STRING_STATE', value)
        },
        setHelperOrganizationPurposeState: ({commit, dispatch, getters}, value) => {
            commit('SET_HELPER_ORGANIZATION_PURPOSE_STATE', value)
        },
        setHelperTitlesState: ({commit, dispatch, getters}, value) => {
            commit('SET_HELPER_TITLES_STATE', value)
        },
        get: ({commit, dispatch, getters}, payload) => {
            commit('SET_IS_LOADING_STATE', getters.getShowLoadingState)
            var params = {
                params: payload.data
            }
            return new Promise((resolve, reject) => {
                axios.get(payload.url, params).then(response => {
                    commit('SET_IS_LOADING_STATE', false)
                    resolve(response)
                }).catch(error => {
                    commit('SET_IS_LOADING_STATE', false)
                    console.log(error)
                })
            })
        },
        post: ({commit, dispatch, getters}, payload) => {
            commit('SET_IS_LOADING_STATE', true)
            return new Promise((resolve, reject) => {
                axios.post(payload.url, payload.data).then(response => {
                    commit('SET_IS_LOADING_STATE', false)
                    resolve(response)
                }).catch(error => {
                    commit('SET_IS_LOADING_STATE', false)
                    resolve(error)
                })
            })
        },
        put: ({commit, dispatch, getters}, payload) => {
            commit('SET_IS_LOADING_STATE', true)
            return new Promise((resolve, reject) => {
                axios.put(payload.url, payload.data).then(response => {
                    commit('SET_IS_LOADING_STATE', false)
                    resolve(response)
                }).catch(error => {
                    commit('SET_IS_LOADING_STATE', false)
                    console.log(error)
                })
            })
        },
        destroy: ({commit, dispatch, getters}, payload) => {
            commit('SET_IS_LOADING_STATE', true)
            return new Promise((resolve, reject) => {
                axios.delete(payload.url, payload.data).then(response => {
                    commit('SET_IS_LOADING_STATE', false)
                    resolve(response)
                }).catch(error => {
                    commit('SET_IS_LOADING_STATE', false)
                    console.log(error)
                })
            })
        },
    },
    getters: {
        getIsLoadingState: state => {
            return state.is_loading
        },
        getCurrentDate: state => {
            return state.current_date
        },
        getHelperAmountState: state => {
            return state.helpers._amount
        },
        getHelperStringState: state => {
            return state.helpers._string
        },
        getHelperTotalAmountState: state => {
            return state.helpers._total_amount
        },
        getShowLoadingState: state => {
            return state.show_loading_screen
        },
        getHelperOrganizationPurposeState: state => {
            return state.helpers._organization_purpose
        },
        getHelperTitlesState: state => {
            return state.helpers._titles
        },
    }
});
