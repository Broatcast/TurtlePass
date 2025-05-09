(function() {
    app.controller('CheckUpdateCtrl', ["UpdateManager", function(UpdateManager) {
        var self = this;

        this.versionData = null;
        this.loaded = false;

        UpdateManager.getVersions().then(function (data) {
            self.versionData = data;
            self.loaded = true;
        });
    }]);
})();
