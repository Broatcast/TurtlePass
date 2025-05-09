(function() {

    app.factory('SettingManager', ['Restangular', function(Restangular){

        var service = {
            getSettings: getSettings,
            postSettings: postSettings
        };

        var baseRest = Restangular.all('settings');

        function getSettings(params){
            return baseRest.getList(params);
        }

        function postSettings(data){
            return baseRest.post(data);
        }

        return service;

    }]);

})();
