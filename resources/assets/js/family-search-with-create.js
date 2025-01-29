import Vue from 'vue'
import Vuex from 'vuex'
Vue.use(Vuex)
import store from './components/crm/Store';

Vue.component('family-search-with-create', require('./components/FamilySearchWithCreate/FamilySearchWithCreate').default);

const app = new Vue({
    el: '#family-search-with-create',
    store
});
