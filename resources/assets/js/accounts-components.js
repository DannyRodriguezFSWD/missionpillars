require('vue-flash-message/src/FlashMessage.css');

window.Vue = require('vue');
// import VModal from 'vue-js-modal';
// Vue.use(VModal, { dynamic: true, injectModalsContainer: true })

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.component('groups', require('./components/Groups.vue').default);
// Vue.component('chrome-picker', require('vue-color/src/components/Chrome'));

Vue.component(
    'passport-clients',
    require('./components/passport/Clients.vue').default
);

Vue.component(
    'passport-authorized-clients',
    require('./components/passport/AuthorizedClients.vue').default
);

Vue.component(
    'passport-personal-access-tokens',
    require('./components/passport/PersonalAccessTokens.vue').default
);
import VueFlashMessage from 'vue-flash-message';
Vue.use(VueFlashMessage)

const app = new Vue({
    el: '#main'
});