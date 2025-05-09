(function() {
    app.factory('SecretManager', ['Restangular', function(Restangular){

        var service = {
            getSecret: getSecret,
            postSecret: postSecret,
            putSecret: putSecret,
            deleteSecret: deleteSecret
        };

        var baseRest = Restangular.all('secrets');

        function getSecret(){
            return baseRest.customGET();
        }

        function postSecret(data){
            return baseRest.post(data);
        }

        function putSecret(secret, code){
            return Restangular.one('secrets', secret).all(code).customPUT();
        }

        function deleteSecret(){
            return baseRest.customDELETE();
        }
        
        return service;

    }]);
})();
