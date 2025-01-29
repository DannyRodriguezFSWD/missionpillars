
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

/* Core Ui Js Overrides
 * Adding functionality to Core ui
 * Move this to a separate file when it gets bigger
 */

// Dispatching an event when core ui sidebar _setActiveLink triggered.
(function(){
    let coreui_sidebar_proto_setActiveLink = coreui.Sidebar.prototype._setActiveLink;
    coreui.Sidebar.prototype._setActiveLink = function (){
        coreui_sidebar_proto_setActiveLink.apply(this,arguments);
        document.dispatchEvent(new CustomEvent('coreui_sidebar_setActiveLink_fired'))
    }
})()

/* End of Coreui */