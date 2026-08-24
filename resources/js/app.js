import $ from 'jquery';
import Toastify from 'toastify-js';
import 'toastify-js/src/toastify.css';
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';
import { Indonesian } from 'flatpickr/dist/l10n/id.js';
import './bootstrap';

window.$ = $;
window.jQuery = $;
window.Toastify = Toastify;
window.flatpickr = flatpickr;
window.flatpickrIndonesian = Indonesian;
