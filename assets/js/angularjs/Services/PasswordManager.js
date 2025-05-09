(function() {
    app.factory('PasswordManager', ['Restangular', function(Restangular){

        var service = {
            searchPassword: searchPassword,
            getPassword: getPassword,
            getPasswordGroup: getPasswordGroup,
            getPasswordAccesses: getPasswordAccesses,
            changePassword: changePassword,
            deletePassword: deletePassword,
            postPasswordAccess: postPasswordAccess,
            putPassword: putPassword,
            putPlainPassword: putPlainPassword,
            putBankAccountPassword: putBankAccountPassword,
            putEmailPassword: putEmailPassword,
            putServerPassword: putServerPassword,
            putCreditCardPassword: putCreditCardPassword,
            putSoftwareLicensePassword: putSoftwareLicensePassword,
            putPasswordAccess: putPasswordAccess,
            postPasswordConfirm: postPasswordConfirm,
            deletePasswordAccess: deletePasswordAccess,
            getPasswordLogs: getPasswordLogs,
            getPasswordShares: getPasswordShares,
            postPasswordShares: postPasswordShares,
            deletePasswordShare: deletePasswordShare,
            getPasswordByShareLinkId: getPasswordByShareLinkId,
            putPlainPasswordByShareLinkId: putPlainPasswordByShareLinkId,
            putBankAccountPasswordByShareLinkId: putBankAccountPasswordByShareLinkId,
            putEmailPasswordByShareLinkId: putEmailPasswordByShareLinkId,
            putServerPasswordByShareLinkId: putServerPasswordByShareLinkId,
            putCreditCardPasswordByShareLinkId: putCreditCardPasswordByShareLinkId,
            putSoftwareLicensePasswordByShareLinkId: putSoftwareLicensePasswordByShareLinkId,
            getPasswordUserGroupAccesses: getPasswordUserGroupAccesses,
            postPasswordUserGroupAccess: postPasswordUserGroupAccess,
            putPasswordUserGroupAccess: putPasswordUserGroupAccess,
            deletePasswordUserGroupAccess: deletePasswordUserGroupAccess,
            putPasswordMove: putPasswordMove
        };

        function searchPassword(query){
            return Restangular.one('passwords', query).all('search').getList();
        }

        function getPassword(passwordId){
            return Restangular.one('passwords', passwordId).get();
        }

        function getPasswordGroup(passwordId){
            return Restangular.one('passwords', passwordId).all('passwordgroup').customGET();
        }

        function putPassword(passwordId, data){
            return Restangular.one('passwords', passwordId).customPUT(data);
        }

        function putPlainPassword(passwordId, data){
            return Restangular.one('passwords', passwordId).all('plain').customPUT({
                name: data.name,
                notice: data.notice,
                log_enabled: data.log_enabled ? 1 : 0,
                url: data.url,
                username: data.username,
                password: data.password,
                custom_fields: data.custom_fields
            });
        }

        function putBankAccountPassword(passwordId, data){
            return Restangular.one('passwords', passwordId).all('bank-account').customPUT({
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

        function putEmailPassword(passwordId, data){
            return Restangular.one('passwords', passwordId).all('email').customPUT({
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

        function putServerPassword(passwordId, data){
            return Restangular.one('passwords', passwordId).all('server').customPUT({
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

        function putCreditCardPassword(passwordId, data){
            return Restangular.one('passwords', passwordId).all('credit-card').customPUT({
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

        function putSoftwareLicensePassword(passwordId, data){
            return Restangular.one('passwords', passwordId).all('software-license').customPUT({
                name: data.name,
                url: data.url,
                notice: data.notice,
                log_enabled: data.log_enabled ? 1 : 0,
                version: data.version,
                license_key: data.license_key,
                custom_fields: data.custom_fields
            });
        }
        
        function changePassword(currentPassword, newPassword) {
            return Restangular.one('userpassword', 'change').customPOST({
                current_password: currentPassword,
                new_password: newPassword
            });
        }

        function deletePassword(passwordId){
            return Restangular.one('passwords', passwordId).customDELETE();
        }

        function postPasswordConfirm(passwordId){
            return Restangular.one('passwords', passwordId).all('confirm').customPOST();
        }

        function getPasswordAccesses(passwordId, params){
            return Restangular.one('passwords', passwordId).all('accesses').getList(params);
        }

        function postPasswordAccess(passwordId, userId, right){
            return Restangular.one('passwords', passwordId).all('accesses').customPOST({
                user: userId,
                right: right
            });
        }

        function putPasswordAccess(passwordId, accessId, right){
            return Restangular.one('passwords', passwordId).one('accesses', accessId).customPUT({
                right: right
            });
        }

        function deletePasswordAccess(passwordId, accessId){
            return Restangular.one('passwords', passwordId).one('accesses', accessId).customDELETE();
        }

        function getPasswordLogs(passwordId, params){
            return Restangular.one('passwords', passwordId).all('logs').getList(params);
        }

        function getPasswordShares(passwordId, params){
            return Restangular.one('passwords', passwordId).all('shares').getList(params);
        }

        function postPasswordShares(passwordId, data){
            return Restangular.one('passwords', passwordId).all('shares').customPOST({
                mode: data.mode,
                valid_to: data.valid_to,
                recipient: data.recipient,
                view_limit: data.viewLimit
            });
        }

        function deletePasswordShare(passwordId, passwordShareId){
            return Restangular.one('passwords', passwordId).one('shares', passwordShareId).customDELETE();
        }

        function getPasswordByShareLinkId(passwordShareLinkId, token){
            return Restangular.one('password-shares', passwordShareLinkId).get({token: token});
        }

        function putPlainPasswordByShareLinkId(passwordShareLinkId, token, data){
            return Restangular.one('password-shares', passwordShareLinkId).all('plain').customPUT({
                name: data.name,
                notice: data.notice,
                log_enabled: data.log_enabled ? 1 : 0,
                url: data.url,
                username: data.username,
                password: data.password,
                custom_fields: data.custom_fields
            }, null, {token: token});
        }

        function putBankAccountPasswordByShareLinkId(passwordShareLinkId, token, data){
            return Restangular.one('password-shares', passwordShareLinkId).all('bank-account').customPUT({
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
            }, null, {token: token});
        }

        function putEmailPasswordByShareLinkId(passwordShareLinkId, token, data){
            return Restangular.one('password-shares', passwordShareLinkId).all('email').customPUT({
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
            }, null, {token: token});
        }

        function putServerPasswordByShareLinkId(passwordShareLinkId, token, data){
            return Restangular.one('password-shares', passwordShareLinkId).all('server').customPUT({
                name: data.name,
                notice: data.notice,
                log_enabled: data.log_enabled ? 1 : 0,
                host: data.host,
                port: data.port,
                username: data.username,
                password: data.password,
                custom_fields: data.custom_fields
            }, null, {token: token});
        }

        function putCreditCardPasswordByShareLinkId(passwordShareLinkId, token, data){
            return Restangular.one('password-shares', passwordShareLinkId).all('credit-card').customPUT({
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
            }, null, {token: token});
        }

        function putSoftwareLicensePasswordByShareLinkId(passwordShareLinkId, token, data){
            return Restangular.one('password-shares', passwordShareLinkId).all('software-license').customPUT({
                name: data.name,
                url: data.url,
                notice: data.notice,
                log_enabled: data.log_enabled ? 1 : 0,
                version: data.version,
                license_key: data.license_key,
                custom_fields: data.custom_fields
            }, null, {token: token});
        }

        function getPasswordUserGroupAccesses(passwordId, params){
            return Restangular.one('passwords', passwordId).all('usergroupaccesses').getList(params);
        }

        function postPasswordUserGroupAccess(passwordId, userGroupId, right){
            return Restangular.one('passwords', passwordId).all('usergroupaccesses').customPOST({
                user_group: userGroupId,
                right: right
            });
        }

        function putPasswordUserGroupAccess(passwordId, accessId, right){
            return Restangular.one('passwords', passwordId).one('usergroupaccesses', accessId).customPUT({
                right: right
            });
        }

        function deletePasswordUserGroupAccess(passwordId, accessId){
            return Restangular.one('passwords', passwordId).one('usergroupaccesses', accessId).customDELETE();
        }

        function putPasswordMove(passwordId, passwordGroupId){
            return Restangular.one('passwords', passwordId).all('move').customPUT({
                'password_group': passwordGroupId
            });
        }

        return service;

    }]);
})();
