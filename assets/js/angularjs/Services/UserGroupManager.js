(function() {

    app.factory('UserGroupManager', ['Restangular', function(Restangular){

        var service = {
            getUserGroups: getUserGroups,
            postUserGroup: postUserGroup,
            putUserGroup: putUserGroup,
            deleteUserGroup: deleteUserGroup
        };

        var baseRest = Restangular.all('usergroups');

        function getUserGroups(params){
            return baseRest.getList(params);
        }

        function postUserGroup(data){
            return baseRest.post(data);
        }

        function putUserGroup(userId, data){
            return Restangular.one('usergroups', userId).customPUT(data);
        }

        function deleteUserGroup(userId){
            return Restangular.one('usergroups', userId).customDELETE();
        }

        return service;

    }]);

})();
