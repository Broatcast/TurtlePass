(function() {
    app.directive('formpostputpassword', function() {
        return {
            restrict: 'E',
            templateUrl: assetsUrl + "templates/directives/add_or_edit_password.html",
            scope: {
                password: '=password'
            },
            controller: ['$scope', '$rootScope', '$stateParams', '$translate', '$state', 'ngNotify', 'ApiFormatManager', 'PasswordManager', 'PasswordGroupManager', function($scope, $rootScope, $stateParams, $translate, $state, ngNotify, ApiFormatManager, PasswordManager, PasswordGroupManager) {

                $scope.data = {
                    custom_fields: []
                };
                $scope.formErrors = {};
                $scope.loaded = false;

                $scope.passwordInputType = {
                    password: 'password',
                    pin: 'password',
                    smtpPassword: 'password'
                };

                $scope.passwordType = 'plain';


                $scope.popoverIsOpen = {
                    password: false,
                    pin: false,
                    smtpPassword: false
                };

                $scope.closePopoverWindow = function() {
                    $scope.popoverIsOpen = {
                        password: false,
                        pin: false,
                        smtpPassword: false
                    };
                };

                $scope.addCustomField = function() {

                    if (!Array.isArray($scope.data.custom_fields)) {
                        $scope.data.custom_fields = [];
                    }

                    $scope.data.custom_fields.push({
                        name: '',
                        value: ''
                    });

                    return false;
                };

                $scope.deleteCustomField = function(index) {
                    $scope.data.custom_fields.splice(index, 1);
                    return false;
                };

                $scope.onPasswordTypeChange = function () {
                    $scope.passwordType = angular.element('#passwordType').val();
                    $scope.closePopoverWindow();
                };

                $scope.passwordOptions = {
                    password: {
                        passwordLength: 16,
                        addUpper: true,
                        addNumbers: true,
                        addSymbols: false
                    },
                    pin: {
                        passwordLength: 16,
                        addUpper: true,
                        addNumbers: true,
                        addSymbols: false
                    },
                    smtpPassword: {
                        passwordLength: 16,
                        addUpper: true,
                        addNumbers: true,
                        addSymbols: false
                    }
                };

                $scope.isLaddaWorking = false;

                $scope.editingMode = false;

                if ($stateParams.passwordId !== undefined) {

                    PasswordManager.getPassword($stateParams.passwordId).then(function (data) {
                        $scope.data = data;
                        $scope.loaded = true;

                        if (data.password_type !== undefined) {
                            $scope.passwordType = data.password_type;
                        } else {
                            $scope.passwordType = 'plain';
                        }
                    });

                    $scope.editingMode = true;

                } else {
                    $scope.data = {
                        name: '',
                        url: '',
                        username: '',
                        notice: '',
                        custom_fields: []
                    };
                    $scope.loaded = true;
                }

                $scope.togglePasswordVisibility = function(key) {
                    if ($scope.passwordInputType[key] !== undefined) {
                        if ($scope.passwordInputType[key] == 'password') {
                            $scope.passwordInputType[key] = 'text';
                        } else {
                            $scope.passwordInputType[key] = 'password';
                        }
                    }
                };

                $scope.submit = function() {

                    $scope.closePopoverWindow();

                    $scope.$broadcast('show-errors-check-validity');

                    if (!$scope.isLaddaWorking && $scope.modalForm.$valid) {

                        $scope.formErrors = {};

                        $scope.isLaddaWorking = true;

                        self.submitSuccessResponseAdd = function(response) {
                            ngNotify.set($translate.instant('PASSWORD_MANAGEMENT.TEXT.PASSWORD_SUCCESSFULLY_ADDED'), 'success');

                            $state.go('overview.group.details.main', {groupId: $stateParams.groupId, passwordId: response.id});

                            $rootScope.$broadcast('reloadPasswordOverview', []);
                        };

                        self.submitSuccessResponseEdit = function(response) {
                            ngNotify.set($translate.instant('PASSWORD_MANAGEMENT.TEXT.PASSWORD_SUCCESSFULLY_UPDATED'), 'success');

                            $rootScope.$broadcast('passwordChanged', [response.id]);

                            $state.go('overview.group.details.main', {groupId: $stateParams.groupId, passwordId: response.id});

                            $rootScope.$broadcast('reloadPasswordOverview', []);
                        };

                        self.submitErrorResponse = function(res) {

                            if (res.status == 400 && res.data.errors !== undefined) {
                                $scope.modalForm.$setUntouched();
                                $scope.modalForm.$setPristine();
                                $scope.$broadcast('show-errors-reset');

                                $scope.formErrors = ApiFormatManager.formatApiFormErrors(res.data.errors.children);
                            }

                            $scope.isLaddaWorking = false;
                        };

                        if ($scope.editingMode) {

                            if ($scope.passwordType === 'plain') {
                                PasswordManager.putPlainPassword($stateParams.passwordId, $scope.data).then(self.submitSuccessResponseEdit, self.submitErrorResponse);
                            } else if ($scope.passwordType === 'bankaccount') {
                                PasswordManager.putBankAccountPassword($stateParams.passwordId, $scope.data).then(self.submitSuccessResponseEdit, self.submitErrorResponse);
                            } else if ($scope.passwordType === 'email') {
                                PasswordManager.putEmailPassword($stateParams.passwordId, $scope.data).then(self.submitSuccessResponseEdit, self.submitErrorResponse);
                            } else if ($scope.passwordType === 'server') {
                                PasswordManager.putServerPassword($stateParams.passwordId, $scope.data).then(self.submitSuccessResponseEdit, self.submitErrorResponse);
                            } else if ($scope.passwordType === 'credit_card') {
                                PasswordManager.putCreditCardPassword($stateParams.passwordId, $scope.data).then(self.submitSuccessResponseEdit, self.submitErrorResponse);
                            } else if ($scope.passwordType === 'software_license') {
                                PasswordManager.putSoftwareLicensePassword($stateParams.passwordId, $scope.data).then(self.submitSuccessResponseEdit, self.submitErrorResponse);
                            }
                        } else {

                            if ($scope.passwordType === 'plain') {
                                PasswordGroupManager.postPlainPassword($stateParams.groupId, $scope.data).then(self.submitSuccessResponseAdd, self.submitErrorResponse);
                            } else if ($scope.passwordType === 'bankaccount') {
                                PasswordGroupManager.postBankAccountPassword($stateParams.groupId, $scope.data).then(self.submitSuccessResponseAdd, self.submitErrorResponse);
                            } else if ($scope.passwordType === 'email') {
                                PasswordGroupManager.postEmailPassword($stateParams.groupId, $scope.data).then(self.submitSuccessResponseAdd, self.submitErrorResponse);
                            } else if ($scope.passwordType === 'server') {
                                PasswordGroupManager.postServerPassword($stateParams.groupId, $scope.data).then(self.submitSuccessResponseAdd, self.submitErrorResponse);
                            } else if ($scope.passwordType === 'credit_card') {
                                PasswordGroupManager.postCreditCardPassword($stateParams.groupId, $scope.data).then(self.submitSuccessResponseAdd, self.submitErrorResponse);
                            } else if ($scope.passwordType === 'software_license') {
                                PasswordGroupManager.postSoftwareLicensePassword($stateParams.groupId, $scope.data).then(self.submitSuccessResponseAdd, self.submitErrorResponse);
                            }

                        }
                    }
                };
            }]
        };
    });
})();
