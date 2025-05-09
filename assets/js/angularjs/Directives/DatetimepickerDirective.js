(function () {
    app.directive('datetimepicker', function () {
        return {
            require: '?ngModel',
            restrict: 'AE',
            scope: {
                ngModel:"=",
                pick12HourFormat: '@',
                language: '@',
                useCurrent: '@',
                location: '@',
                format: '@',
                sideBySide: '@'
            },
            link: function (scope, elem, attrs) {
                elem.datetimepicker({
                    format: scope.format,
                    pick12HourFormat: scope.pick12HourFormat,
                    language: scope.language,
                    useCurrent: scope.useCurrent,
                    sideBySide: scope.sideBySide,
                    minDate: new Date(),
                });

                elem.on('blur', function() {
                    scope.ngModel = this.value;
                });
            }
        };
    });
})();
