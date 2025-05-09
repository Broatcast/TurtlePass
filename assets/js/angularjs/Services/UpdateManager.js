(function() {

    app.factory('UpdateManager', ['Restangular', function(Restangular){

        var service = {
            getVersions: getVersions
        };

        var baseRest = Restangular.all('versions');

        function getVersions(params){
            return baseRest.customGET(params);
        }

        return service;

    }]);

})();
