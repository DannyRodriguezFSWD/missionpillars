// store module to work with tags-multiselect.vue 
export default {
    state: {
        tag_options: [],
    },
    mutations: {
        SET_TAG_OPTIONS: (state, value) => {
            state.tag_options = value
        },
        
        PUSH_TAG: (state, value) => {
            state.tag_options.push(value)
        }
    },
    actions: {
        setTagOptions: ({commit, dispatch, getters}, value) => {
            commit('SET_IS_LOADING_STATE', getters.getShowLoadingState)
            if (!value || !value.length) {
                axios.get("/crm/ajax/tags").then(response => {
                    commit('SET_IS_LOADING_STATE', false)
                    value = response.data
                    commit('SET_TAG_OPTIONS', value)
                }).catch(error => {
                    commit('SET_IS_LOADING_STATE', false)
                    console.log(error)
                })
            } 
        },
        pushNewTag: ({commit, dispatch, getters}, value) => {
            commit('PUSH_TAG', value)
        },
        /**
         * Create a new tag, add it to an options object and select it
         * @param  {[type]} commit  automagically added by Vuex store
         * @param  {object} payload Object with properties tag_name & selected
         */
        newTag: ({commit}, payload) => {
            axios.post("/crm/ajax/tags",{'tag': payload.tag, 'includeFolder': payload.includeFolder })
            .then( response => {
                // create tags-multiselect compatible tag object
                let new_tag = response.data
                new_tag.id = response.data.id
                new_tag.label = response.data.name
                commit('PUSH_TAG', new_tag)
                
                // optional function to handle 
                if (payload.addFunction && typeof payload.addFunction == 'function') { payload.addFunction(new_tag); return }
                
                // add new tag to tags-multiselect
                if (payload.value && 'selected' in payload.value) payload.value.selected =  new_tag
                else if (Array.isArray(payload.values)) payload.values.push(new_tag)
                else console.log('new tag created but not selected', new_tag)
            })
            .catch( error => console.log('error',error) )
        },
    },
    getters: {
        getTagOptions: state => {
            return state.tag_options ?state.tag_options : []
        },
    },
}
