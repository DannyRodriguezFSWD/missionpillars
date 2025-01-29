import Vue from 'vue'
import Vuex from 'vuex'
Vue.use(Vuex)
import store from './components/crm/Store';

Vue.component('group-search-with-create', require('./components/GroupSearchWithCreate/GroupSearchWithCreate').default);

const app = new Vue({
    el: '#group-search-with-create',
    store
});
