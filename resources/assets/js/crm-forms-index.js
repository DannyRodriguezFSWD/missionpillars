window.Vue = require('vue');

Vue.component('crm-forms', require('./components/crm/crm-forms.vue').default);

const app = new Vue({
    el: '#main'
});