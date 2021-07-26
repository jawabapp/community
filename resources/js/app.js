import Vue from "vue";
import axios from "axios";
import VueJsonPretty from "vue-json-pretty";

window.$ = window.jQuery = require("jquery");

require("bootstrap");

let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
  axios.defaults.headers.common["X-CSRF-TOKEN"] = token.content;
}

Vue.component("vue-json-pretty", VueJsonPretty);

new Vue({
  el: "#community"
});
