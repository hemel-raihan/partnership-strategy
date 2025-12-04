
require('./bootstrap');
require('./validation/index');

import Vue from 'vue'
import App from './views/App'
import router from './router/index';
import store from './store/index'
import {baseurl} from './base_url'
import excel from 'vue-excel-export'


Vue.use(excel)

window.printThis = require('print-this');

// main origin
Vue.prototype.mainOrigin = baseurl

import Toaster from 'v-toaster'
import 'v-toaster/dist/v-toaster.css'
import "vue-search-select/dist/VueSearchSelect.css"

Vue.use(Toaster, {timeout: 5000})




export const bus = new Vue();


Vue.component('skeleton-loader', require('./components/loaders/Straight').default);
Vue.component('submit-form', require('./components/buttons/Submit').default);
Vue.component('submit-form-2', require('./components/buttons/Submit2').default);
Vue.component('datatable', require('./components/datatable/Index').default);
Vue.component('advanced-datatable', require('./components/datatable/Advanced').default);
Vue.component('data-export', require('./components/datatable/Export').default);
Vue.component('data-export-csv', require('./components/datatable/ExportCSV').default);
Vue.component('breadcrumb', require('./components/layouts/Breadcrumb').default);
// Vue.component('barchart', require('./components/chart/Bar').default);
Vue.component('tree-view', require('./components/users/Permisions').default);
Vue.component('outlet-permissions', require('./components/users/OutletPermissions').default);


const app = new Vue({
    el: '#app',
    store: store,
    components: {App},
    router,
});
