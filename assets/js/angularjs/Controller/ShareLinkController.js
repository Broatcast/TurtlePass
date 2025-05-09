(function() {
    app.controller('ShareLinkViewController', ["$scope", "$stateParams", "$translate", "ngNotify", "PasswordManager", function($scope, $stateParams, $translate, ngNotify, PasswordManager) {

        var self = this;

        $scope.loading = true;
        $scope.password = {};
        $scope.isLaddaWorking = false;
        $scope.accessDenied = false;

        $scope.passwordInputType = {
            password: 'password',
            pin: 'password',
            smtpPassword: 'password'
        };

        $scope.togglePasswordVisibility = function(key) {
            if ($scope.passwordInputType[key] !== undefined) {
                if ($scope.passwordInputType[key] == 'password') {
                    $scope.passwordInputType[key] = 'text';
                } else {
                    $scope.passwordInputType[key] = 'password';
                }
            }
        };

        this.onCopied = function(entity) {
            ngNotify.set($translate.instant('PASSWORD_MANAGEMENT.ENTRY_COPIED', { entry: $translate.instant(entity) }), 'success');
        };

        this.onCopyFailed = function(err, entry, text) {
            ngNotify.set($translate.instant('PASSWORD_MANAGEMENT.COPY_FAILED'), 'error');
            swal($translate.instant(entry), text, "info");
        };

        self.reloadData = function() {

            PasswordManager.getPasswordByShareLinkId($stateParams.passwordShareLinkId, $stateParams.securityToken).then(function (data) {
                $scope.password = data.password;
                $scope.data = data;
                $scope.loading = false;

                if (data.password_type !== undefined) {
                    $scope.passwordType = data.password_type;
                } else {
                    $scope.passwordType = 'plain';
                }
            }, function(res) {
                if (res.status == 403) {
                    $scope.accessDenied = true;
                    $scope.loading = false;
                }
            });
        };

        self.reloadData();

        $scope.submit = function() {

            $scope.$broadcast('show-errors-check-validity');

            if (!$scope.isLaddaWorking && $scope.data.mode === 'read_write' && $scope.modalForm.$valid) {

                $scope.formErrors = {};

                $scope.isLaddaWorking = true;

                self.submitSuccessResponseEdit = function (response) {
                    ngNotify.set($translate.instant('PASSWORD_MANAGEMENT.TEXT.PASSWORD_SUCCESSFULLY_UPDATED'), 'success');
                    $scope.isLaddaWorking = false;
                };

                self.submitErrorResponse = function (res) {

                    if (res.status == 400 && res.data.errors !== undefined) {
                        $scope.modalForm.$setUntouched();
                        $scope.modalForm.$setPristine();
                        $scope.$broadcast('show-errors-reset');

                        $scope.formErrors = ApiFormatManager.formatApiFormErrors(res.data.errors.children);
                    }

                    $scope.isLaddaWorking = false;
                };

                if ($scope.passwordType === 'plain') {
                    PasswordManager.putPlainPasswordByShareLinkId($stateParams.passwordShareLinkId, $stateParams.securityToken, $scope.password).then(self.submitSuccessResponseEdit, self.submitErrorResponse);
                } else if ($scope.passwordType === 'bankaccount') {
                    PasswordManager.putBankAccountPasswordByShareLinkId($stateParams.passwordShareLinkId, $stateParams.securityToken, $scope.password).then(self.submitSuccessResponseEdit, self.submitErrorResponse);
                } else if ($scope.passwordType === 'email') {
                    PasswordManager.putEmailPasswordByShareLinkId($stateParams.passwordShareLinkId, $stateParams.securityToken, $scope.password).then(self.submitSuccessResponseEdit, self.submitErrorResponse);
                } else if ($scope.passwordType === 'server') {
                    PasswordManager.putServerPasswordByShareLinkId($stateParams.passwordShareLinkId, $stateParams.securityToken, $scope.password).then(self.submitSuccessResponseEdit, self.submitErrorResponse);
                } else if ($scope.passwordType === 'credit_card') {
                    PasswordManager.putCreditCardPasswordByShareLinkId($stateParams.passwordShareLinkId, $stateParams.securityToken, $scope.password).then(self.submitSuccessResponseEdit, self.submitErrorResponse);
                } else if ($scope.passwordType === 'software_license') {
                    PasswordManager.putSoftwareLicensePasswordByShareLinkId($stateParams.passwordShareLinkId, $stateParams.securityToken, $scope.password).then(self.submitSuccessResponseEdit, self.submitErrorResponse);
                }
            }
        }
    }]);
})();
