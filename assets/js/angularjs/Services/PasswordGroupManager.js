(function() {
    app.factory('PasswordGroupManager', ['Restangular', function(Restangular){

        var service = {
            getPasswordGroup: getPasswordGroup,
            getPasswordGroups: getPasswordGroups,
            getPasswordsByPasswordGroup: getPasswordsByPasswordGroup,
            postPasswordGroup: postPasswordGroup,
            putPasswordGroup: putPasswordGroup,
            postPlainPassword: postPlainPassword,
            postBankAccountPassword: postBankAccountPassword,
            postEmailPassword: postEmailPassword,
            postServerPassword: postServerPassword,
            postCreditCardPassword: postCreditCardPassword,
            postSoftwareLicensePassword: postSoftwareLicensePassword,
            getPasswordGroupAccesses: getPasswordGroupAccesses,
            postPasswordGroupAccess: postPasswordGroupAccess,
            putPasswordGroupAccess: putPasswordGroupAccess,
            putPasswordGroupMove: putPasswordGroupMove,
            deletePasswordGroupAccess: deletePasswordGroupAccess,
            deletePasswordGroup: deletePasswordGroup,
            putPasswordGroupSorting: putPasswordGroupSorting,
            getPasswordGroupUserGroupAccesses: getPasswordGroupUserGroupAccesses,
            postPasswordGroupUserGroupAccess: postPasswordGroupUserGroupAccess,
            putPasswordGroupUserGroupAccess: putPasswordGroupUserGroupAccess,
            deletePasswordGroupUserGroupAccess: deletePasswordGroupUserGroupAccess
        };

        var baseRest = Restangular.all('passwordgroups');

        function getPasswordGroups(params){
            return baseRest.getList(params);
        }

        function getPasswordGroup(groupId) {
            return Restangular.one('passwordgroups', groupId).get();
        }

        function getPasswordsByPasswordGroup(groupId, params) {
            return Restangular.one('passwordgroups', groupId).all('passwords').getList(params);
        }

        function postPasswordGroup(name, icon, parentId){
            if (parentId == null) {

                return Restangular.one('passwordgroups', '0').customPOST({
                    name: name,
                    icon: icon
                });
            }

            return Restangular.one('passwordgroups', parentId).post({
                name: name,
                icon: icon
            });
        }

        function putPasswordGroup(passwordGroupId, name, icon){
            return Restangular.one('passwordgroups', passwordGroupId).customPUT({
                name: name,
                icon: icon
            });
        }

        function postPlainPassword(passwordGroupId, data){
            return Restangular.one('passwordgroups', passwordGroupId).all('passwords').all('plain').customPOST({
                name: data.name,
                notice: data.notice,
                log_enabled: data.log_enabled ? 1 : 0,
                url: data.url,
                username: data.username,
                password: data.password,
                custom_fields: data.custom_fields
            });
        }

        function postBankAccountPassword(passwordGroupId, data){
            return Restangular.one('passwordgroups', passwordGroupId).all('passwords').all('bank-account').customPOST({
                name: data.name,
                notice: data.notice,
                log_enabled: data.log_enabled ? 1 : 0,
                url: data.url,
                username: data.username,
                bank_name: data.bank_name,
                bank_code: data.bank_code,
                account_holder: data.account_holder,
                account_number: data.account_number,
                iban: data.iban,
                pin: data.pin,
                custom_fields: data.custom_fields
            });
        }

        function postEmailPassword(passwordGroupId, data){
            return Restangular.one('passwordgroups', passwordGroupId).all('passwords').all('email').customPOST({
                name: data.name,
                notice: data.notice,
                log_enabled: data.log_enabled ? 1 : 0,
                email_type: data.email_type,
                auth_method: data.auth_method,
                host: data.host,
                port: data.port,
                username: data.username,
                password: data.password,
                smtp_auth_method: data.smtp_auth_method,
                smtp_host: data.smtp_host,
                smtp_port: data.smtp_port,
                smtp_username: data.smtp_username,
                smtp_password: data.smtp_password,
                custom_fields: data.custom_fields
            });
        }

        function postServerPassword(passwordGroupId, data){
            return Restangular.one('passwordgroups', passwordGroupId).all('passwords').all('server').customPOST({
                name: data.name,
                notice: data.notice,
                log_enabled: data.log_enabled ? 1 : 0,
                host: data.host,
                port: data.port,
                username: data.username,
                password: data.password,
                custom_fields: data.custom_fields
            });
        }

        function postCreditCardPassword(passwordGroupId, data){
            return Restangular.one('passwordgroups', passwordGroupId).all('passwords').all('credit-card').customPOST({
                name: data.name,
                notice: data.notice,
                log_enabled: data.log_enabled ? 1 : 0,
                card_type: data.card_type,
                card_number: data.card_number,
                card_cvc: data.card_cvc,
                valid_from: data.valid_from,
                valid_to: data.valid_to,
                pin: data.pin,
                custom_fields: data.custom_fields
            });
        }

        function postSoftwareLicensePassword(passwordGroupId, data){
            return Restangular.one('passwordgroups', passwordGroupId).all('passwords').all('software-license').customPOST({
                name: data.name,
                url: data.url,
                notice: data.notice,
                log_enabled: data.log_enabled ? 1 : 0,
                version: data.version,
                license_key: data.license_key,
                custom_fields: data.custom_fields
            });
        }

        function getPasswordGroupAccesses(passwordGroupId, params){
            return Restangular.one('passwordgroups', passwordGroupId).all('accesses').getList(params);
        }

        function postPasswordGroupAccess(passwordGroupId, userId, right){
            return Restangular.one('passwordgroups', passwordGroupId).all('accesses').customPOST({
                user: userId,
                right: right
            });
        }

        function putPasswordGroupAccess(passwordGroupId, accessId, right){
            return Restangular.one('passwordgroups', passwordGroupId).one('accesses', accessId).customPUT({
                right: right
            });
        }

        function putPasswordGroupMove(passwordGroupId, parentPasswordGroupId){
            return Restangular.one('passwordgroups', passwordGroupId).all('move').customPUT({
                'parent': parentPasswordGroupId
            });
        }

        function putPasswordGroupSorting(sorting){
            return Restangular.all('passwordgroups').all('sorting').customPUT({
                'sorting': sorting
            });
        }

        function deletePasswordGroupAccess(passwordGroupId, accessId){
            return Restangular.one('passwordgroups', passwordGroupId).one('accesses', accessId).customDELETE();
        }

        function deletePasswordGroup(passwordGroupId){
            return Restangular.one('passwordgroups', passwordGroupId).customDELETE();
        }

        function getPasswordGroupUserGroupAccesses(passwordGroupId, params){
            return Restangular.one('passwordgroups', passwordGroupId).all('usergroupaccesses').getList(params);
        }

        function postPasswordGroupUserGroupAccess(passwordGroupId, userGroupId, right){
            return Restangular.one('passwordgroups', passwordGroupId).all('usergroupaccesses').customPOST({
                user_group: userGroupId,
                right: right
            });
        }

        function putPasswordGroupUserGroupAccess(passwordGroupId, accessId, right){
            return Restangular.one('passwordgroups', passwordGroupId).one('usergroupaccesses', accessId).customPUT({
                right: right
            });
        }

        function deletePasswordGroupUserGroupAccess(passwordGroupId, accessId){
            return Restangular.one('passwordgroups', passwordGroupId).one('usergroupaccesses', accessId).customDELETE();
        }

        return service;
    }]);
})();
