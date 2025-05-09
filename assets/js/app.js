require('../scss/app.scss');

var $ = require('jquery');
global.$ = global.jQuery = $;

require('bootstrap');
require('ladda');
var moment = require('moment');
global.moment = moment;

import swal from 'sweetalert2/dist/sweetalert2.js';
global.swal = swal;

require('angular');
require('angular-route');
require('angular-ladda');
require('angular-sanitize');
require('angular-translate');
require('angular-translate-loader-static-files');
require('angular-dynamic-locale');
require('restangular');
require('ui-bootstrap4');
require('ui-select');
require('@uirouter/angularjs');
require('ng-table');
require('angular-loading-bar');
require('angular-animate');
require('checklist-model');
require('ng-notify');
require('ng-infinite-scroll');
require('angular-ui-tree');
require('angular-clipboard');
require('./angularjs/Libraries/ng-password-generator');
require('tempusdominus-bootstrap-4');

require('expose-loader?entry!./angularjs/angularjs-app.js');
function importAll (r) {
    r.keys().forEach(function(file) {
        if (!file.includes('angularjs-app.js')) {
            r(file);
        }
    });
}
importAll(require.context("imports-loader?app=>entry.app!./angularjs/", true, /.*\.js/));

require.context("file-loader?name=[path][name].[ext]!../i18n/", true, /.*\.json/);
require.context("file-loader?name=[path][name].[ext]!../templates/", true, /.*\.html/);

require("file-loader?name=angular-i18n/angular-locale_de.js!angular-i18n/angular-locale_de.js");
require("file-loader?name=angular-i18n/angular-locale_en.js!angular-i18n/angular-locale_en.js");

$( ".sidebar-toggle" ).click(function() {
    $( ".sidebar" ).toggle();
});
