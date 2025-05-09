(function() {

    app.factory('LanguageManager', ['Restangular', function(Restangular){

        var service = {
            getLanguages: getLanguages,
            putLanguage: putLanguage
        };

        var baseRest = Restangular.all('languages');

        function getLanguages(params){
            return baseRest.getList(params);
        }

        function putLanguage(language) {
            return Restangular.all('language').customPUT({
                language: language
            });
        }

        return service;

    }]);

})();
