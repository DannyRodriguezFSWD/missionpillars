import Vue from 'vue'
import Vuex from 'vuex'
Vue.use(Vuex)
import store from './components/crm/Store';

Vue.component(
    'crm-transactions',
    require('./components/crm/crm-transactions.vue').default
);

const app = new Vue({
    el: '#crm-transactions',
    store
});