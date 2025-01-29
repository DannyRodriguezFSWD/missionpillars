import Vue from 'vue'
import Vuex from 'vuex'
Vue.use(Vuex)
import store from './components/crm/Store';

Vue.component(
    'crm-login',
    require('./components/Login.vue').default
);

const app = new Vue({
    el: '#crm-login',
    store
});