'use strict';

app.controller('AuthController', ["$scope", "$translate", "ngNotify", "SecretManager", function($scope, $translate, ngNotify, SecretManager) {
    var self = this;

    this.hasSecret = false;
    this.step = 1;
    this.code = '';

    this.secretData = null;
    this.loaded = false;
    this.formError = false;

    this.isLaddaWorking = false;

    SecretManager.getSecret().then(function (data) {
        if (data.has_secret === true) {
            self.hasSecret = true;
            self.loaded = true;
        } else {
            self.loadSecret();
        }
    });

    this.loadSecret = function() {
        self.hasSecret = false;
        self.loaded = false;
        self.formError = false;
        self.step = 1;
        self.code = '';
        self.isLaddaWorking = false;
        SecretManager.postSecret().then(function (data) {
            self.secretData = data;
            self.loaded = true;
        });
    };

    this.submit = function() {
        $scope.$broadcast('show-errors-check-validity');

        if (!self.isLaddaWorking && $scope.authForm.$valid) {
            self.formError = false;
            self.isLaddaWorking = true;

            SecretManager.putSecret(self.secretData.secret, self.code).then(function (data) {
                self.hasSecret = true;
            }, function(res) {
                if (res.status == 400) {
                    self.formError = true;
                }

                self.isLaddaWorking = false;
            });

        }
    };

    this.disable = function() {
        swal({
            title: $translate.instant('TEXT.ARE_YOU_SURE'),
            text: $translate.instant('2_FACTOR_AUTH.TEXT.DELETE_INFORMATION'),
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: $translate.instant('WORDS.YES'),
            cancelButtonText: $translate.instant('WORDS.CLOSE')
        }).then(function () {
            SecretManager.deleteSecret().then(function() {
                ngNotify.set($translate.instant('2_FACTOR_AUTH.TEXT.DELETE_COMPLETE'), 'success');

                self.loadSecret();
            }, function(res) {
                self.loadSecret();
            });
        }, function (dismiss) {});
    };
}]);
