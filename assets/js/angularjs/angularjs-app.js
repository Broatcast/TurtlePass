var assetsUrl = __webpack_public_path__ + 'assets/';
global.assetsUrl = assetsUrl;

var app = angular.module('turtlepass', [
    'pascalprecht.translate',
    'ui.router',
    'ui.bootstrap',
    'restangular',
    'angular-ladda',
    'ngTable',
    'ngSanitize',
    'angular-loading-bar',
    'angular-clipboard',
    'ngAnimate',
    'checklist-model',
    'ngNotify',
    'ui.tree',
    'ngPasswordGenerator',
    'ui.select',
    'infinite-scroll',
    'tmh.dynamicLocale'
]).config(["$stateProvider", "$urlRouterProvider", "$translateProvider", "RestangularProvider", "laddaProvider", "cfpLoadingBarProvider", "MY_GLOBAL_SETTINGS", "tmhDynamicLocaleProvider", function(
    $stateProvider,
    $urlRouterProvider,
    $translateProvider,
    RestangularProvider,
    laddaProvider,
    cfpLoadingBarProvider,
    MY_GLOBAL_SETTINGS,
    tmhDynamicLocaleProvider) {

    $translateProvider.useStaticFilesLoader({
        prefix: assetsUrl + 'i18n/',
        suffix: '.json'
    });

    $translateProvider.preferredLanguage(MY_GLOBAL_SETTINGS.preferredLanguage);
    $translateProvider.useSanitizeValueStrategy('sanitizeParameters');

    tmhDynamicLocaleProvider.localeLocationPattern('/build/angular-i18n/angular-locale_{{locale}}.js');

    cfpLoadingBarProvider.includeSpinner = false;

    laddaProvider.setOption({
        style: 'expand-right'
    });

    RestangularProvider.setBaseUrl(MY_GLOBAL_SETTINGS.baseUrl);

    if (typeof MY_GLOBAL_SETTINGS.token !== "undefined" && MY_GLOBAL_SETTINGS.token !== "") {
        RestangularProvider.setDefaultHeaders({
            "Authorization": "Bearer " + MY_GLOBAL_SETTINGS.token,
            "Accept-Language": MY_GLOBAL_SETTINGS.user.language.id
        });
    }

    RestangularProvider.addResponseInterceptor(function(data, operation) {
        var extractedData;
        if (operation === "getList") {
            if (data._embedded !== undefined && data._embedded.items !== undefined) {
                extractedData = data._embedded.items;
                if (data.limit !== undefined) {
                    extractedData.limit = data.limit;
                }
                if (data.total !== undefined) {
                    extractedData.total = data.total;
                }
                if (data.pages !== undefined) {
                    extractedData.pages = data.pages;
                }
            } else {
                extractedData = data;
            }
        } else {
            extractedData = data;
        }
        return extractedData;
    });

    $urlRouterProvider.otherwise("/overview");

    $stateProvider

    // Overview
        .state('overview', {
            url: "/overview",
            templateUrl: assetsUrl + "templates/overview.html?v=2"
        })
        .state('overview.group', {
            url: "/group/:groupId",
            templateUrl: assetsUrl + "templates/overview/filter.html?v=2"
        })
        .state('overview.group.add', {
            url: "/add",
            templateUrl: assetsUrl + "templates/overview/add.html?v=2"
        })
        .state('overview.group.details', {
            url: "/details/:passwordId",
            templateUrl: assetsUrl + "templates/overview/details.html?v=2"
        })
        .state('overview.group.details.main', {
            url: "/main",
            templateUrl: assetsUrl + "templates/overview/details/main.html?v=2"
        })
        .state('overview.group.details.icon', {
            url: "/icon",
            templateUrl: assetsUrl + "templates/overview/details/icon.html?v=2"
        })
        .state('overview.group.details.edit', {
            url: "/edit",
            templateUrl: assetsUrl + "templates/overview/details/edit.html?v=2"
        })
        .state('overview.group.details.edit_permissions', {
            url: "/edit-permissions",
            templateUrl: assetsUrl + "templates/overview/details/edit_password_access.html?v=2"
        })
        .state('overview.group.details.edit_group_permissions', {
            url: "/edit-group-permissions",
            templateUrl: assetsUrl + "templates/overview/details/edit_group_password_access.html?v=2"
        })
        .state('overview.group.details.logs', {
            url: "/logs",
            templateUrl: assetsUrl + "templates/overview/details/logs.html?v=2"
        })
        .state('overview.group.details.shares', {
            url: "/shares",
            templateUrl: assetsUrl + "templates/overview/details/shares.html?v=2"
        })

        // User
        .state('user', {
            url: "/user",
            templateUrl: assetsUrl + "templates/user.html?v=2"
        })

        // User Group
        .state('user_group', {
            url: "/usergroup",
            templateUrl: assetsUrl + "templates/usergroup.html?v=2"
        })

        // Profile
        .state('profile', {
            url: "/profile",
            templateUrl: assetsUrl + "templates/profile.html?v=2"
        })
        .state('profile.edit_own', {
            url: "/editown",
            templateUrl: assetsUrl + "templates/profile/edit_own.html?v=2"
        })
        .state('profile.change_password', {
            url: "/change_password",
            templateUrl: assetsUrl + "templates/profile/change_password.html?v=2"
        })
        .state('profile.global_settings', {
            url: "/global-settings",
            templateUrl: assetsUrl + "templates/profile/global_settings.html?v=2"
        })
        .state('profile.tokens', {
            url: "/tokens",
            templateUrl: assetsUrl + "templates/profile/tokens.html?v=2"
        })
        .state('profile.token_add', {
            url: "/token/add",
            templateUrl: assetsUrl + "templates/profile/token/add.html?v=2"
        })
        .state('profile.check_update', {
            url: "/check-update",
            templateUrl: assetsUrl + "templates/profile/check_update.html?v=2"
        })
        .state('profile.login_accesses', {
            url: "/login-accesses",
            templateUrl: assetsUrl + "templates/profile/login_accesses.html?v=2"
        })
        .state('profile.auth', {
            url: "/auth",
            templateUrl: assetsUrl + "templates/profile/auth.html?v=2"
        })

        // Password Share
        .state('password_share', {
            url: "/password-share/:passwordShareLinkId/:securityToken",
            templateUrl: assetsUrl + "templates/password_share.html?v=2"
        })
    ;
}]).run([
    "$rootScope", "$state", "$stateParams", function($rootScope, $state, $stateParams) {

        $rootScope.$state = $state;
        $rootScope.currentUser = JS_CONFIGURATION.userInformation;
        $rootScope.version = JS_CONFIGURATION.version;
        $rootScope.isAdmin = ($rootScope.currentUser.admin !== undefined && $rootScope.currentUser.admin);

        return $rootScope.$stateParams = $stateParams;
    }
]).constant("MY_GLOBAL_SETTINGS", {
    user: JS_CONFIGURATION.userInformation,
    token: JS_CONFIGURATION.userToken,
    preferredLanguage: JS_CONFIGURATION.locale,
    baseUrl: JS_CONFIGURATION.baseUrl
});

module.exports = {
    app: app,
    assetsUrl: assetsUrl
};
