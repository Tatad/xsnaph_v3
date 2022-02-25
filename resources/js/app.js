/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue').default;

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

Vue.component('example-component', require('./components/ExampleComponent.vue').default);
Vue.component('register-organisation', require('./components/RegisterOrganisation.vue').default);
Vue.component('excel-uploader', require('./components/ExcelUploader.vue').default);
Vue.component('sales-records', require('./components/SalesRecords.vue').default);
Vue.component('purchases-summary', require('./components/PurchasesSummary.vue').default);
Vue.component('quarterly-slsp-summary', require('./components/QuarterlySLSPSummary.vue').default);
Vue.component('report-2307-summary', require('./components/Report2307Summary.vue').default);
Vue.component('yearpicker', require('./components/YearPicker.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
    data(){
        return{
            fileName: 'Choose File'
        }
    },
    methods:{
        onFileChange(event){
           var fileData =  event.target.files[0];
           this.fileName=fileData.name;
        }
    }
});