define(function() {

    return function($rootScope, $http2, $site, $timeout) {

        var $coreSettings = {
            storage: {
                chat_module: '',
                user_login_type: 'both'
            }
        };

        $coreSettings.set = function(object) {

            $.extend($coreSettings.storage, object);

            $rootScope.$broadcast('coreSettings:update', $coreSettings.storage);
        };

        $coreSettings.get = function(key) {

            return $coreSettings.storage[key];
        };

        $coreSettings.update = function() {

            var successCb = function(data) {

                if (data.error_code) {
                    console.warn('core/settings', data);
                    return $rootScope.$broadcast('coreSettings:updateError');
                }

                $coreSettings.set(data);
            };

            var errorCb = function() {

                console.warn('core/settings', arguments);
                $rootScope.$broadcast('coreSettings:updateError');
            };

            $http2.post('core/settings').success(successCb).error(errorCb);
        };

        return $coreSettings;
    };
});