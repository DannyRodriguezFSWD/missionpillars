const state = {
    modal: {
        show: false,
        header: 'Alert',
        body: ''
    }
}

const mutations = {//sync
    SHOW_MODAL: (state, value) => {
        state.modal.show = value
    },
    SET_MODAL_BODY: (state, value) => {
        state.modal.body = value
    },
    SET_MODAL_HEADER: (state, value) => {
        state.modal.header = value
    }
}

const actions = {//async
    showModalAction: ({commit, dispatch}, value) => {
        commit('SHOW_MODAL', value)
    },
    setModalHeader: ({commit, dispatch}, value) => {
        commit('SET_MODAL_HEADER', value)
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