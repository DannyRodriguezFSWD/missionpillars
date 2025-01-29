const state = {
    modules: {
        installed: [],
        available: []
    },
    selected: null,
    plan: {
        free: {
            active: false,
            free: false
        },
        chms: {
            action: 'enable',
            active: false,
            free: false
        },
        accounting: {
            action: 'enable',
            active: false,
            free: false
        }
    },
    next_billing_date: new Date(),
}

const mutations = {//sync
    SET_STATE_MODULES: (state, value) => {
        state.modules = value
    },
    SET_STATE_SELECTED_MODULE: (state, value) => {
        state.selected = value
    },
    SET_STATE_CHMS_ACTION: (state, value) => {
        state.plan.chms.action = value
    },
    SET_STATE_ACCOUNTING_ACTION: (state, value) => {
        state.plan.accounting.action = value
    },
    SET_STATE_FREE_ACTIVE: (state, value) => {
        state.plan.free.active = value
    },
    SET_STATE_CHMS_ACTIVE: (state, value) => {
        state.plan.chms.active = value
    },
    SET_STATE_ACCOUNTING_ACTIVE: (state, value) => {
        state.plan.accounting.active = value
    },
    SET_STATE_FREE_MONTH: (state, value) => {
        state.plan.free.free = value
        state.plan.chms.free = value
    },
    SET_STATE_ACCOUNTING_FREE_MONTH: (state, value) => {
        state.plan.accounting.free = value
    },
    SET_STATE_BILLING_STARTS_AT: (state, value) => {
        state.next_billing_date = value
    },
}

const actions = {//async
    getCurrentModulesAction: ({commit, dispatch, rootState}, value) => {
        dispatch('setUrlStateAction', value, {root:true}).then( response => {
            dispatch('getAllCurrentModules')
        });
    },
    getAllCurrentModules: ({commit, dispatch, rootState, rootGetters}) => {
        let url = rootGetters.getUrlState
        dispatch('isLoadingAction', true, {root:true})
        axios.get(url).then( response => {
            dispatch('isLoadingAction', false, {root:true})
            commit('SET_STATE_MODULES', response.data.modules)
            if( response.data.canClaimFreeMonth == 1 ){//if they are allowed to get a free month
                commit('SET_STATE_FREE_MONTH', true)
            }
            else{
                commit('SET_STATE_FREE_MONTH', false)
            }

            if( response.data.canClaimAccountingFreeMonth == 1 ){//if they are allowed to get a free month
                commit('SET_STATE_ACCOUNTING_FREE_MONTH', true)
            }
            else{
                commit('SET_STATE_ACCOUNTING_FREE_MONTH', false)
            }
            commit('SET_STATE_BILLING_STARTS_AT', response.data.billing_start_at)
            response.data.modules.installed.forEach(element => {
                switch (element.id) {
                    case 2:
                        commit('SET_STATE_CHMS_ACTION', 'disable')
                        break;

                    case 3:
                        commit('SET_STATE_ACCOUNTING_ACTION', 'disable')
                        break;
                
                    default:
                    //free plan we dont do anything
                        break;
                }
            });
            return new Promise((resolve, reject) => {
                resolve(response.data.modules)
            })
        })
    },
    setSelectedModule: ({commit, dispatch, rootState, rootGetters}, value) => {
        commit('SET_STATE_SELECTED_MODULE', value)
    }
}

const getters = {
    
}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}