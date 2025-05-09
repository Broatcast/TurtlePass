(function() {
    app.factory('LoginAccessManager', ['Restangular', function(Restangular){

        var service = {
            getLoginAccesses: getLoginAccesses,
            getLoginAccess: getLoginAccess,
            postLoginAccess: postLoginAccess,
            putLoginAccess: putLoginAccess,
            deleteLoginAccess: deleteLoginAccess
        };

        var baseRest = Restangular.all('loginaccesses');

        function getLoginAccesses(params){
            return baseRest.getList(params);
        }

        function getLoginAccess(id) {
            return Restangular.one('loginaccesses', id).get();
        }

        function postLoginAccess(data){
            return baseRest.post(data);
        }

        function putLoginAccess(id, data){
            return Restangular.one('loginaccesses', id).customPUT(data);
        }

        function deleteLoginAccess(loginAccess) {
            return loginAccess.remove();
        }
        
        return service;

    }]);
})();
