(function() {

    app.factory('UserManager', ['Restangular', function(Restangular){

        var service = {
            getUsers: getUsers,
            postUser: postUser,
            putOwnUser: putOwnUser,
            putUser: putUser,
            deleteUser: deleteUser,
            activateUser: activateUser,
            disableSecret: disableSecret,
            deactivateUser: deactivateUser
        };

        var baseRest = Restangular.all('users');

        function getUsers(params){
            return baseRest.getList(params);
        }

        function postUser(data){
            return baseRest.post(data);
        }

        function putOwnUser(data){
            return Restangular.one('user').customPUT(data);
        }

        function deleteUser(userId){
            return Restangular.one('users', userId).customDELETE();
        }

        function activateUser(userId){
            return Restangular.one('users', userId).customPOST({}, "activate");
        }

        function deactivateUser(userId){
            return Restangular.one('users', userId).customPOST({}, "deactivate");
        }

        function disableSecret(userId){
            return Restangular.one('users', userId).all('secret').customDELETE();
        }

        function putUser(userId, data){
            return Restangular.one('users', userId).customPUT(data);
        }

        return service;

    }]);

})();
