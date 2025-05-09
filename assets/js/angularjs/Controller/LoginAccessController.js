(function() {
    app.controller('LoginAccessOverviewCtrl', ["$scope", "$uibModal", "$translate", "NgTableParams", "LoginAccessManager", "ApiFormatManager", function($scope, $uibModal, $translate, NgTableParams, LoginAccessManager, ApiFormatManager) {

        var self = this;

        self.loading = true;

        this.reloadData = function() {
            $scope.tableParams = new NgTableParams({
                page: 1,
                count: 10
            }, {
                total: 0,
                getData: function(params) {
                    return LoginAccessManager.getLoginAccesses(ApiFormatManager.formatNGTableParametersToRest(params)).then(function(data) {
                        params.total(data.total);
                        self.loading = false;
                        return data;
                    });
                }
            });
        };

        this.reloadData();

        this.add = function() {
            var addModal = $uibModal.open({
                animation: true,
                templateUrl: assetsUrl + "templates/modal/add_or_edit_login_access.html?v=2",
                controller: 'ModalAddOrEditLoginAccessController',
                size: "mg",
                backdrop: 'static',
                resolve: {
                    loginAccess: null
                }
            });

            addModal.result.then(function (response) {
                self.reloadData();
            });
        };

        this.edit = function(loginAccess) {
            var editModal = $uibModal.open({
                animation: true,
                templateUrl: assetsUrl + "templates/modal/add_or_edit_login_access.html?v=2",
                controller: 'ModalAddOrEditLoginAccessController',
                size: "mg",
                backdrop: 'static',
                resolve: {
                    loginAccess: loginAccess
                }
            });

            editModal.result.then(function (response) {
                self.reloadData();
            });
        };

        this.delete = function(loginAccess) {
            swal({
                title: $translate.instant('TEXT.ARE_YOU_SURE'),
                text: $translate.instant('LOGIN_ACCESS_MANAGEMENT.TEXT.DELETE_INFORMATION'),
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: $translate.instant('WORDS.YES'),
                cancelButtonText: $translate.instant('WORDS.CLOSE')
            }).then(function () {
                LoginAccessManager.deleteLoginAccess(loginAccess).then(function() {
                    self.reloadData();
                    swal($translate.instant('TEXT.SUCCESSFULLY_DELETED'), $translate.instant('LOGIN_ACCESS_MANAGEMENT.TEXT.DELETE_COMPLETE'), "success");
                }, function(res) {
                    if (res.status == 404) {
                        swal($translate.instant('TEXT.RESOURCE_NOT_FOUND'), $translate.instant('TEXT.DELETE_NOT_FOUND'), "error");
                    } else {
                        swal($translate.instant('TEXT.UNKNOWN_ERROR'), $translate.instant('TEXT.UNKNOWN_ERROR_INFORMATION'), "error");
                    }
                });
            }, function (dismiss) {});
        };
    }]);

    app.controller('ModalAddOrEditLoginAccessController', ["$scope", "$uibModalInstance", "ApiFormatManager", "LoginAccessManager", "loginAccess", function($scope, $uibModalInstance, ApiFormatManager, LoginAccessManager, loginAccess) {

        $scope.isLaddaWorking = false;

        $scope.formErrors = {};

        $scope.editingMode = false;

        var self = this;

        if (loginAccess !== null) {
            $scope.data = loginAccess;
            $scope.data['whitelist'] = loginAccess ? '1' : '0';
            $scope.editingMode = true;

        } else {
            $scope.data = {
                from_ip: '',
                to_ip: '',
                whitelist: "1"
            };
        }

        $scope.submit = function() {

            $scope.$broadcast('show-errors-check-validity');

            if (!$scope.isLaddaWorking && $scope.modalForm.$valid) {

                $scope.formErrors = {};

                $scope.isLaddaWorking = true;

                self.submitSuccessResponse = function(response) {
                    $uibModalInstance.close(response);
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
                    LoginAccessManager.putLoginAccess(loginAccess.id, $scope.data).then(self.submitSuccessResponse, self.submitErrorResponse);
                } else {
                    LoginAccessManager.postLoginAccess($scope.data).then(self.submitSuccessResponse, self.submitErrorResponse);
                }

            }

        };

        $scope.cancel = function() {
            $uibModalInstance.dismiss('cancel');
        };
    }]);
})();
