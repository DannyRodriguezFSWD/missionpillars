const state = {
    current_record: null
}

const mutations = {//sync
    SET_CURRENT_RECORD: (state, value) => {
        state.current_record = value
    }
}

const actions = {//async
    setCurrentRecord: ({commit, dispatch}, value) => {
        commit('SET_CURRENT_RECORD', value)
    },
}

const getters = {
    getCurrentRecord: state => {
        return state.current_record
    },
}

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
}