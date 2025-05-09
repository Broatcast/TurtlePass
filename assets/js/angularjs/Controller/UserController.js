(function() {
    app.controller('UserOverviewCtrl', ["$translate", "$scope", "ngNotify", "NgTableParams", "UserManager", "ApiFormatManager", "$uibModal", function($translate, $scope, ngNotify, NgTableParams, UserManager, ApiFormatManager, $uibModal) {

        var self = this;

        this.loaded = false;

        this.reloadData = function() {
            $scope.tableParams = new NgTableParams({
                page: 1,
                count: 10
            }, {
                total: 0,
                getData: function (params) {
                    return UserManager.getUsers(ApiFormatManager.formatNGTableParametersToRest(params)).then(function (data) {
                        params.total(data.total);
                        self.loaded = true;

                        return data;
                    });
                }
            });
        };

        this.reloadData();

        this.addUser = function() {
            var addUserModal = $uibModal.open({
                animation: true,
                templateUrl: assetsUrl + "templates/modal/add_or_edit_user.html?v=2",
                controller: 'ModalAddOrEditUserController',
                size: "lg",
                backdrop: 'static',
                resolve: {
                    user: null
                }
            });

            addUserModal.result.then(function (response) {
                self.reloadData();
            }, function() {});
        };

        this.editUser = function(user) {
            var editUserModal = $uibModal.open({
                animation: true,
                templateUrl: assetsUrl + "templates/modal/add_or_edit_user.html?v=2",
                controller: 'ModalAddOrEditUserController',
                size: "lg",
                backdrop: 'static',
                resolve: {
                    user: user
                }
            });

            editUserModal.result.then(function (response) {
                self.reloadData();
            }, function() {});
        };

        this.deactivate = function(user) {
            swal({
                title: $translate.instant('TEXT.ARE_YOU_SURE'),
                text: $translate.instant('USER_MANAGEMENT.TEXT.DEACTIVATE_USER_INFORMATION'),
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: $translate.instant('WORDS.YES'),
                cancelButtonText: $translate.instant('WORDS.CLOSE')
            }).then(function() {
                UserManager.deactivateUser(user.id).then(function() {
                    self.reloadData();

                    ngNotify.set($translate.instant('USER_MANAGEMENT.TEXT.DEACTIVATE_COMPLETE'), 'success');

                }, function(res) {
                    if (res.status == 404) {
                        swal($translate.instant('TEXT.RESOURCE_NOT_FOUND'), $translate.instant('TEXT.DELETE_NOT_FOUND'), "error");
                    } else {
                        swal($translate.instant('TEXT.UNKNOWN_ERROR'), $translate.instant('TEXT.UNKNOWN_ERROR_INFORMATION'), "error");
                    }
                });
            }, function(dismiss) {});
        };

        this.activate = function(user) {
            swal({
                title: $translate.instant('TEXT.ARE_YOU_SURE'),
                text: $translate.instant('USER_MANAGEMENT.TEXT.ACTIVATE_USER_INFORMATION'),
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: $translate.instant('WORDS.YES'),
                cancelButtonText: $translate.instant('WORDS.CLOSE')
            }).then(function() {
                UserManager.activateUser(user.id).then(function() {

                    self.reloadData();

                    ngNotify.set($translate.instant('USER_MANAGEMENT.TEXT.ACTIVATE_COMPLETE'), 'success');

                }, function(res) {
                    if (res.status == 404) {
                        swal($translate.instant('TEXT.RESOURCE_NOT_FOUND'), $translate.instant('TEXT.DELETE_NOT_FOUND'), "error");
                    } else {
                        swal($translate.instant('TEXT.UNKNOWN_ERROR'), $translate.instant('TEXT.UNKNOWN_ERROR_INFORMATION'), "error");
                    }
                });
            }, function(dismiss) {});
        };

        this.delete = function(user) {
            swal({
                title: $translate.instant('TEXT.ARE_YOU_SURE'),
                text: $translate.instant('USER_MANAGEMENT.TEXT.DELETE_INFORMATION'),
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: $translate.instant('WORDS.YES'),
                cancelButtonText: $translate.instant('WORDS.CLOSE')
            }).then(function() {
                UserManager.deleteUser(user.id).then(function() {
                    self.reloadData();

                    ngNotify.set($translate.instant('USER_MANAGEMENT.TEXT.DELETE_COMPLETE'), 'success');

                }, function(res) {
                    if (res.status == 404) {
                        swal($translate.instant('TEXT.RESOURCE_NOT_FOUND'), $translate.instant('TEXT.DELETE_NOT_FOUND'), "error");
                    } else {
                        swal($translate.instant('TEXT.UNKNOWN_ERROR'), $translate.instant('TEXT.UNKNOWN_ERROR_INFORMATION'), "error");
                    }
                });
            }, function(dismiss) {});
        };

        this.disableSecret = function(user) {
            swal({
                title: $translate.instant('TEXT.ARE_YOU_SURE'),
                text: $translate.instant('2_FACTOR_AUTH.TEXT.DELETE_INFORMATION'),
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: $translate.instant('WORDS.YES'),
                cancelButtonText: $translate.instant('WORDS.CLOSE')
            }).then(function() {
                UserManager.disableSecret(user.id).then(function() {
                    self.reloadData();

                    ngNotify.set($translate.instant('2_FACTOR_AUTH.TEXT.DELETE_COMPLETE'), 'success');

                }, function(res) {
                    if (res.status == 404) {
                        swal($translate.instant('TEXT.RESOURCE_NOT_FOUND'), $translate.instant('TEXT.DELETE_NOT_FOUND'), "error");
                    } else {
                        swal($translate.instant('TEXT.UNKNOWN_ERROR'), $translate.instant('TEXT.UNKNOWN_ERROR_INFORMATION'), "error");
                    }
                });
            }, function(dismiss) {});
        };
    }]);

    app.controller('UserDropdownCtrl', ["MY_GLOBAL_SETTINGS", function(MY_GLOBAL_SETTINGS) {
        var self = this;
        this.currentUser = MY_GLOBAL_SETTINGS.user;

        this.status = {
            isopen: false
        };

        this.toggleDropdown = function($event) {
            $event.preventDefault();
            $event.stopPropagation();
            self.status.isopen = !self.status.isopen;
        };
    }]);

    app.controller('ModalAddOrEditUserController', ["$scope", "$uibModalInstance", "$translate", "ngNotify", "ApiFormatManager", "UserManager", "UserGroupManager", "user", function($scope, $uibModalInstance, $translate, ngNotify, ApiFormatManager, UserManager, UserGroupManager, user) {

        $scope.isLaddaWorking = false;

        $scope.formErrors = {};

        $scope.editingMode = false;

        var self = this;

        if (user !== null) {
            $scope.data = user;

            var userGroups = [];
            angular.forEach(user.user_groups, function (value, key) {
                if (value.id !== undefined) {
                    userGroups.push(value.id);
                }
            });

            if (userGroups.length > 0) {
                $scope.data.user_groups = userGroups;
            }

            $scope.editingMode = true;

        } else {
            $scope.data = {
                id: 0,
                username: '',
                password: '',
                first_name: '',
                last_name: '',
                email: ''
            };
        }
        $scope.groups = [];

        UserGroupManager.getUserGroups().then(function (data) {
            $scope.groups = data;

            $scope.loaded = true;
        });

        $scope.submit = function() {

            $scope.$broadcast('show-errors-check-validity');

            if (!$scope.isLaddaWorking && $scope.modalForm.$valid) {

                $scope.formErrors = {};

                $scope.isLaddaWorking = true;

                self.submitSuccessResponseAdd = function(response) {
                    $uibModalInstance.close(response);
                    ngNotify.set($translate.instant('USER_MANAGEMENT.TEXT.USER_SUCCESSFULLY_ADDED'), 'success');
                };

                self.submitSuccessResponseEdit = function(response) {
                    $uibModalInstance.close(response);
                    ngNotify.set($translate.instant('USER_MANAGEMENT.TEXT.USER_SUCCESSFULLY_UPDATED'), 'success');
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
                    UserManager.putUser(user.id, $scope.data).then(self.submitSuccessResponseEdit, self.submitErrorResponse);
                } else {
                    UserManager.postUser($scope.data).then(self.submitSuccessResponseAdd, self.submitErrorResponse);
                }

            }

        };

        $scope.cancel = function() {
            $uibModalInstance.dismiss('cancel');
        };
    }]);

    app.controller('EditOwnDataController', ["MY_GLOBAL_SETTINGS", "$scope", "$translate", "ApiFormatManager", "UserManager", function(MY_GLOBAL_SETTINGS, $scope, $translate, ApiFormatManager, UserManager) {

        var self = this;

        this.data = {
            'email' : MY_GLOBAL_SETTINGS.user.email,
            'first_name' : MY_GLOBAL_SETTINGS.user.first_name,
            'last_name' : MY_GLOBAL_SETTINGS.user.last_name
        };

        this.formErrors = {};

        this.laddaLoading = false;

        this.submit = function() {

            $scope.$broadcast('show-errors-check-validity');

            self.formErrors = {};

            self.laddaLoading = true;
            UserManager.putOwnUser(this.data).then(function(data) {
                self.laddaLoading = false;
                self.data = data;
                $scope.editOwnDataForm.$setUntouched();
                $scope.editOwnDataForm.$setPristine();
                $scope.$broadcast('show-errors-reset');
                swal($translate.instant('SETTINGS.TEXT.OWN_USER_DATA_CHANGED'), $translate.instant('SETTINGS.TEXT.OWN_USER_DATA_INFORMATION'), "success");
            }, function(res) {
                if (res.status == 400 && res.data.errors !== undefined) {
                    $scope.editOwnDataForm.$setUntouched();
                    $scope.editOwnDataForm.$setPristine();
                    $scope.$broadcast('show-errors-reset');
                    self.formErrors = ApiFormatManager.formatApiFormErrors(res.data.errors.children);
                }
                self.laddaLoading = false;
            });
        };
    }]);


})();
