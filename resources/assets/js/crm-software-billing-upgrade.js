import Vue from 'vue'
import Vuex from 'vuex'
import store from './components/crm/settings/billing/store.js';

Vue.use(Vuex)

Vue.component(
    'crm-billing-software-upgrade',
    require('./components/crm/settings/billing/software_upgrade.vue').default
);

Vue.component(
    'crm-billing-payment-options',
    require('./components/crm/settings/billing/payment_options.vue').default
);

const app = new Vue({
    el: '#crm-billing-software-upgrade',
    store
});