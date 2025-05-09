(function() {
    app.factory('TokenManager', ['Restangular', function(Restangular){

        var service = {
            getTokens: getTokens,
            getToken: getToken,
            postToken: postToken,
            putToken: putToken,
            deleteToken: deleteToken
        };

        var baseRest = Restangular.all('tokens');

        function getTokens(params){
            return baseRest.getList(params);
        }

        function getToken(tokenId) {
            return Restangular.one('tokens', tokenId).get();
        }

        function postToken(data){
            return baseRest.post(data);
        }

        function putToken(tokenId, data){
            return Restangular.one('tokens', tokenId).customPUT(data);
        }

        function deleteToken(token) {
            return token.remove();
        }
        
        return service;

    }]);
})();
