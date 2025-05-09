'use strict';

app.controller('LanguageDropdownCtrl', ["MY_GLOBAL_SETTINGS", "$translate", "Restangular", "LanguageManager", "tmhDynamicLocale", function(MY_GLOBAL_SETTINGS, $translate, Restangular, LanguageManager, tmhDynamicLocale) {
    var self = this;
    this.currentUser = MY_GLOBAL_SETTINGS.user;

    this.src = "/img/icons/country/"+MY_GLOBAL_SETTINGS.user.language.id+".png";

    this.status = {
        isopen: false
    };

    this.toggleDropdown = function($event) {
        $event.preventDefault();
        $event.stopPropagation();
        self.status.isopen = !self.status.isopen;
    };

    if (MY_GLOBAL_SETTINGS.user.language.id != 'en') {
        tmhDynamicLocale.set(MY_GLOBAL_SETTINGS.user.language.id);
    }

    this.changeLanguage = function(newLanguage) {
        self.src = "/img/icons/country/"+newLanguage+".png";
        $translate.use(newLanguage);
        tmhDynamicLocale.set(newLanguage);

        LanguageManager.putLanguage(newLanguage);

        if (newLanguage == "en") {
            Restangular.configuration.defaultHeaders["Accept-Language"] = newLanguage;
        } else {
            Restangular.configuration.defaultHeaders["Accept-Language"] = newLanguage+",en;q=0.5";
        }
    };
}]);

