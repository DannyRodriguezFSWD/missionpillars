import Vue from 'vue'
import Vuex from 'vuex'
Vue.use(Vuex)
import store from './components/crm/Store';

Vue.component('people-search-with-create2', require('./components/PeopleSearchWithCreate/PeopleSearchWithCreate').default);

const app = new Vue({
    el: '#people-search-with-create2',
    store
});
