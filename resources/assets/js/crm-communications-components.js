import Vue from 'vue'
import Vuex from 'vuex'
Vue.use(Vuex)

import store from './components/communications/sms/store.js';
import LiquorTree from 'liquor-tree'
Vue.use(LiquorTree)

import 'beautify-scrollbar/dist/index.css';
import V2LazyList from 'v2-lazy-list';
Vue.use(V2LazyList);

Vue.component(
    'crm-communications-viewport',
    require('./components/communications/sms/crm-communications-viewport.vue').default
);

Vue.component(
    'crm-buy-phone-number',
    require('./components/communications/sms/crm-buy-phone-number.vue').default
);

Vue.component(
    'crm-sms-settings-notifications',
    require('./components/crm/settings/sms/notifications.vue').default
);

const app = new Vue({
    el: '#crm-communications-viewport',
    store
});
