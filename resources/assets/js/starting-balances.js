import Vue from 'vue';
import VueFlashMessage from 'vue-flash-message';

Vue.use(VueFlashMessage)

Vue.component(
    'accounting-starting-balance',
    require('./components/accounting/StartingBalance.vue').default
);

const app = new Vue({
    el: '#main'
});