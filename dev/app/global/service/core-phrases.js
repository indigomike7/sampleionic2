define(function() {

    return function($rootScope, $http, $http2, gettextCatalog, $site) {

        var $corePhrases = {
            storage: {
            }
        };

        var userLanguage  = localStorage.getItem('lang');

        if(typeof userLanguage == 'undefined' || userLanguage == null){
            userLanguage =  $site.lang.def;
        }

        $http.defaults.headers.common.foxlang = userLanguage || '';

        gettextCatalog.setCurrentLanguage(userLanguage);
        moment.locale(userLanguage);

        $http2.get('core/languages',{})
            .success(function(data){
                $site.lang.options = data;
                $rootScope.$broadcast('coreMenu:update');
            });

        $corePhrases.set = function(object) {

            $.extend($corePhrases.storage, object);

            gettextCatalog.setStrings(userLanguage, $corePhrases.storage);
            $rootScope.$broadcast('coreMenu:update');
        };

        $corePhrases.update = function() {

            var successCb = function(data) {

                if (data.error_code) {
                    return console.warn('core/phrases', data);
                }

                $corePhrases.set(data);
            };

            var errorCb = function() {

                console.warn('core/phrases', arguments);
            };

            $http2.get('core/phrases').success(successCb).error(errorCb);
        };

        $rootScope.$on('user:login', $corePhrases.update);

        return $corePhrases;
    };
});