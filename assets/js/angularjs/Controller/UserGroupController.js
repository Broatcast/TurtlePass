(function() {
    app.controller('UserGroupOverviewCtrl', ["$translate", "$scope", "ngNotify", "NgTableParams", "UserGroupManager", "ApiFormatManager", "$uibModal", function($translate, $scope, ngNotify, NgTableParams, UserGroupManager, ApiFormatManager, $uibModal) {

        var self = this;

        this.loaded = false;

        this.reloadData = function() {
            $scope.tableParams = new NgTableParams({
                page: 1,
                count: 10
            }, {
                total: 0,
                getData: function (params) {
                    return UserGroupManager.getUserGroups(ApiFormatManager.formatNGTableParametersToRest(params)).then(function (data) {
                        params.total(data.total);
                        self.loaded = true;
                        return data;
                    });
                }
            });
        };

        this.reloadData();

        this.addUserGroup = function() {
            var addUserGroupModal = $uibModal.open({
                animation: true,
                templateUrl: assetsUrl + "templates/modal/add_or_edit_user_group.html?v=2",
                controller: 'ModalAddOrEditUserGroupController',
                size: "mg",
                backdrop: 'static',
                resolve: {
                    userGroup: null
                }
            });

            addUserGroupModal.result.then(function (response) {
                self.reloadData();
            }, function() {});
        };

        this.editUserGroup = function(userGroup) {
            var editUserGroupModal = $uibModal.open({
                animation: true,
                templateUrl: assetsUrl + "templates/modal/add_or_edit_user_group.html?v=2",
                controller: 'ModalAddOrEditUserGroupController',
                size: "mg",
                backdrop: 'static',
                resolve: {
                    userGroup: userGroup
                }
            });

            editUserGroupModal.result.then(function (response) {
                self.reloadData();
            }, function() {});
        };

        this.delete = function(userGroup) {
            swal({
                title: $translate.instant('TEXT.ARE_YOU_SURE'),
                text: $translate.instant('GROUP_MANAGEMENT.TEXT.DELETE_INFORMATION'),
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: $translate.instant('WORDS.YES'),
                cancelButtonText: $translate.instant('WORDS.CLOSE')
            }).then(function() {
                UserGroupManager.deleteUserGroup(userGroup.id).then(function() {
                    self.reloadData();

                    ngNotify.set($translate.instant('GROUP_MANAGEMENT.TEXT.SUCCESSFULLY_DELETED'), 'success');

                }, function(res) {
                    if (res.status == 404) {
                        swal($translate.instant('TEXT.RESOURCE_NOT_FOUND'), $translate.instant('TEXT.DELETE_NOT_FOUND'), "error");
                    } if (res.status == 409) {
                        swal($translate.instant('TEXT.DELETE_FAILED'), $translate.instant('TEXT.USER_GROUP_DELETE_LOCKED'), "error");
                    } else {
                        swal($translate.instant('TEXT.UNKNOWN_ERROR'), $translate.instant('TEXT.UNKNOWN_ERROR_INFORMATION'), "error");
                    }
                });
            }, function(dismiss) {});
        };
    }]);

    app.controller('ModalAddOrEditUserGroupController', ["$scope", "$uibModalInstance", "$translate", "ngNotify", "ApiFormatManager", "UserGroupManager", "userGroup", function($scope, $uibModalInstance, $translate, ngNotify, ApiFormatManager, UserGroupManager, userGroup) {

        $scope.isLaddaWorking = false;

        $scope.formErrors = {};

        $scope.editingMode = false;

        var self = this;

        if (userGroup !== null) {
            $scope.data = userGroup;
            $scope.editingMode = true;

        } else {
            $scope.data = {
                name: ''
            };
        }

        $scope.submit = function() {

            $scope.$broadcast('show-errors-check-validity');

            if (!$scope.isLaddaWorking && $scope.modalForm.$valid) {

                $scope.formErrors = {};

                $scope.isLaddaWorking = true;

                self.submitSuccessResponseAdd = function(response) {
                    $uibModalInstance.close(response);
                    ngNotify.set($translate.instant('GROUP_MANAGEMENT.TEXT.SUCCESSFULLY_ADDED'), 'success');
                };

                self.submitSuccessResponseEdit = function(response) {
                    $uibModalInstance.close(response);
                    ngNotify.set($translate.instant('GROUP_MANAGEMENT.TEXT.SUCCESSFULLY_UPDATED'), 'success');
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
                    UserGroupManager.putUserGroup(userGroup.id, $scope.data).then(self.submitSuccessResponseEdit, self.submitErrorResponse);
                } else {
                    UserGroupManager.postUserGroup($scope.data).then(self.submitSuccessResponseAdd, self.submitErrorResponse);
                }


            }

        };

        $scope.cancel = function() {
            $uibModalInstance.dismiss('cancel');
        };
    }]);


})();
