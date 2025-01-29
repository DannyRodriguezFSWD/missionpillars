require('vue-flash-message/src/FlashMessage.css');
import Vuex from 'vuex'
window.Vue = require('vue');
Vue.use(Vuex)

import store from './components/crm/Store';
Vue.component(
    'accounting-registers', require('./components/accounting/Registers.vue').default
);

import VueFlashMessage from 'vue-flash-message';
Vue.use(VueFlashMessage)

const app = new Vue({
    el: '#main',
    store
});
