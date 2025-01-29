import Vue from 'vue';
import TransactionDetails from './components/accounting/bank_integrations/TransactionDetails.vue'
import Vuex from 'vuex'
import store from './components/crm/Store';
Vue.use(Vuex)

Vue.component(
    'bank-integration-acc-list',
    require('./components/accounting/bank_integrations/BankAccounts.vue').default
)

const app = new Vue({
    el: '#main',
    store
});