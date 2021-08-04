import Vue from "vue";
import axios from "axios";
import VueJsonPretty from "vue-json-pretty";
import {
    MultiSelectComponent,
    MultiSelectPlugin,
    DropDownListComponent,
    DropDownListPlugin
} from '@syncfusion/ej2-vue-dropdowns';
import { MultiSelect, CheckBoxSelection } from '@syncfusion/ej2-dropdowns';

MultiSelect.Inject(CheckBoxSelection);
Vue.use(MultiSelectPlugin);

Vue.component(MultiSelectPlugin.name, MultiSelectComponent);
Vue.component(DropDownListPlugin.name, DropDownListComponent);
window.$ = window.jQuery = require("jquery");

require("./bootstrap");

let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    axios.defaults.headers.common["X-CSRF-TOKEN"] = token.content;
}

Vue.component("vue-json-pretty", VueJsonPretty);
Vue.component('multiple-select', require('./components/MultipleSelectComponent').default);

new Vue({
    el: "#community"
});