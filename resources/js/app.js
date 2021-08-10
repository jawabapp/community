import Vue from "vue";
import axios from "axios";
import VueJsonPretty from "vue-json-pretty";

window.$ = window.jQuery = require("jquery");

require("./bootstrap");

let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    axios.defaults.headers.common["X-CSRF-TOKEN"] = token.content;
}

Vue.component("vue-json-pretty", VueJsonPretty);
Vue.component('community-multiple-select', require('./components/MultipleSelectComponent').default);
Vue.component('community-assign-tag-group', require('./components/AssignTagGroupComponent').default);

new Vue({
    el: "#community"
});