(function() {
    app.factory('ApiFormatManager', ['Restangular', function(Restangular){

        var service = {
            formatNGTableParametersToRest: formatNGTableParametersToRest,
            formatApiFormErrors: formatApiFormErrors,
            formatStringUrlToObject: formatStringUrlToObject,
            appendStringToQuery: appendStringToQuery,
            addslashes: addslashes
        };

        function formatNGTableParametersToRest(params) {

            var formatted = {};

            var sorting = params.orderBy();

            Object.keys(sorting).forEach(function(key) {
                formatted['sort'] = sorting[key];
            });

            var filters = params.filter();

            var count = 0;
            Object.keys(filters).forEach(function(key) {
                if (filters[key] != "") {
                    if (formatted.query === undefined) {
                        formatted.query = 'WHERE (';
                    }
                    if (count > 0) {
                        formatted.query = formatted.query + ' && ';
                    }
                    formatted.query = formatted.query + key + " LIKE " + "'%"+addslashes(filters[key])+"%'";
                    count++;
                }
            });

            if (count) {
                formatted.query = formatted.query + ')';
            }


            formatted.page = params.page();
            formatted.limit = params.count();

            return formatted;
        }

        function formatApiFormErrors(data) {

            var result = {};

            Object.keys(data).forEach(function(key) {
                if (data[key].errors !== undefined) {
                    result[key] = data[key].errors[0];
                } else if (data[key].children !== undefined) {
                    result[key] = formatApiFormErrors(data[key].children);
                }
            });

            return result;
        }

        function appendStringToQuery(params, string) {
            if (string === null) {
                return params;
            }

            if (params.query === undefined) {
                params.query = 'WHERE';
            } else {
                params.query =  params.query + ' &&';
            }

            params.query = params.query + ' ('+string+')';

            return params;
        }
        
        function formatStringUrlToObject(data) {
            return JSON.parse('{"' + decodeURI(data.replace(/&/g, "\",\"").replace(/=/g,"\":\"")) + '"}');
        }

        function addslashes(str) {
            return (str + '')
                .replace(/[\\"']/g, '\\$&')
                .replace(/\u0000/g, '\\0');
        }


        return service;

    }]);
})();
