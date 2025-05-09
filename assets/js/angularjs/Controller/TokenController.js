(function() {
    app.controller('TokenOverviewCtrl', ["$scope", "$uibModal", "$translate", "NgTableParams", "TokenManager", "ApiFormatManager", function($scope, $uibModal, $translate, NgTableParams, TokenManager, ApiFormatManager) {
        
        var self = this;

        self.loading = true;

        this.reloadData = function() {
            $scope.tableParams = new NgTableParams({
                page: 1,
                count: 10
            }, {
                total: 0,
                getData: function(params) {
                    return TokenManager.getTokens(ApiFormatManager.formatNGTableParametersToRest(params)).then(function(data) {
                        params.total(data.total);
                        self.loading = false;

                        return data;
                    });
                }
            });
        };

        this.reloadData();

        this.addToken = function() {
            var addTokenModal = $uibModal.open({
                animation: true,
                templateUrl: assetsUrl + "templates/modal/add_or_edit_token.html?v=2",
                controller: 'ModalAddOrEditTokenController',
                size: "mg",
                backdrop: 'static',
                resolve: {
                    token: null
                }
            });

            addTokenModal.result.then(function (response) {
                self.reloadData();
            }, function() {});
        };

        this.editToken = function(token) {
            var editTokenModal = $uibModal.open({
                animation: true,
                templateUrl: assetsUrl + "templates/modal/add_or_edit_token.html?v=2",
                controller: 'ModalAddOrEditTokenController',
                size: "mg",
                backdrop: 'static',
                resolve: {
                    token: token
                }
            });

            editTokenModal.result.then(function (response) {
                self.reloadData();
            }, function() {});
        };

        this.delete = function(token) {
            swal({
                title: $translate.instant('TEXT.ARE_YOU_SURE'),
                text: $translate.instant('TOKEN_MANAGEMENT.TEXT.DELETE_INFORMATION'),
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: $translate.instant('WORDS.YES'),
                cancelButtonText: $translate.instant('WORDS.CLOSE')
            }).then(function () {
                TokenManager.deleteToken(token).then(function() {
                    self.reloadData();
                    swal($translate.instant('TEXT.SUCCESSFULLY_DELETED'), $translate.instant('TOKEN_MANAGEMENT.TEXT.DELETE_COMPLETE'), "success");
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

    app.controller('ModalAddOrEditTokenController', ["$scope", "$uibModalInstance", "ApiFormatManager", "TokenManager", "token", function($scope, $uibModalInstance, ApiFormatManager, TokenManager, token) {

        $scope.isLaddaWorking = false;

        $scope.formErrors = {};

        $scope.editingMode = false;

        var self = this;

        if (token !== null) {
            $scope.data = token;
            $scope.editingMode = true;

        } else {
            $scope.data = {
                description: ''
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
                    TokenManager.putToken(token.id, $scope.data).then(self.submitSuccessResponse, self.submitErrorResponse);
                } else {
                    TokenManager.postToken($scope.data).then(self.submitSuccessResponse, self.submitErrorResponse);
                }

            }

        };

        $scope.cancel = function() {
            $uibModalInstance.dismiss('cancel');
        };
    }]);
})();
