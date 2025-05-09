(function() {
    app.controller('SettingNavController', ["MY_GLOBAL_SETTINGS", "$scope", function(MY_GLOBAL_SETTINGS, $scope) {

        $scope.passwordChangeAvailable = MY_GLOBAL_SETTINGS.user.type !== 'ldap_user';

        $scope.goToRecaptchaConfiguration = function() {
            var pathname = window.location.pathname;

            if (pathname === '') {
                pathname = '/';
            }

            window.location = window.location.protocol+"//"+window.location.host+pathname+"recaptcha";
        };

        $scope.goToExport = function() {
            var pathname = window.location.pathname;

            if (pathname === '') {
                pathname = '/';
            }

            window.location = window.location.protocol+"//"+window.location.host+pathname+"csv-export";
        };
    }]);

    app.controller('SettingEditCtrl', ["$translate", "$scope", "ngNotify", "SettingManager", "ApiFormatManager", function($translate, $scope, ngNotify, SettingManager, ApiFormatManager) {

        var self = this;

        this.settings = null;
        this.loaded = false;

        SettingManager.getSettings({limit: 100}).then(function (data) {
            self.settings = data;
            self.loaded = true;
        });

        this.formErrors = {};

        this.laddaLoading = false;

        this.submit = function() {

            self.formErrors = {};

            if ($scope.updateSettingsForm.$valid) {
                self.laddaLoading = true;

                var formatted = {};

                _.forEach(self.settings, function(setting) {
                    formatted[setting.id] = setting.value;
                });

                SettingManager.postSettings(formatted).then(function() {
                    self.laddaLoading = false;

                    $scope.updateSettingsForm.$setUntouched();
                    $scope.updateSettingsForm.$setPristine();
                    $scope.$broadcast('show-errors-reset');

                    ngNotify.set($translate.instant('SETTINGS.TEXT.GLOBAL_SETTINGS_CHANGED'), 'success');

                }, function(res) {
                    if (res.status == 400 && res.data.errors !== undefined) {
                        $scope.updateSettingsForm.$setUntouched();
                        $scope.updateSettingsForm.$setPristine();
                        $scope.$broadcast('show-errors-reset');
                        self.formErrors = ApiFormatManager.formatApiFormErrors(res.data.errors.children);
                    }
                    self.laddaLoading = false;
                });
            }
        };

    }]);
})();
