define([
    'global/viewer'
], function(Viewer) {

    return function($rootScope) {

        var $viewer = {
            storage: {}
        };

        $viewer.set = function(data) {

            $viewer.storage = data || {};

            localStorage.setItem('viewer', JSON.stringify($viewer.storage));

            $rootScope.$broadcast('viewer:update', $viewer.storage);
        };

        $viewer.reset = function() {

            $viewer.storage = {};

            localStorage.setItem('viewer', JSON.stringify($viewer.storage));

            $rootScope.$broadcast('viewer:update', $viewer.storage);
        };

        $viewer.update = function(data) {

            $.extend($viewer.storage, data);

            localStorage.setItem('viewer', JSON.stringify($viewer.storage));

            $rootScope.$broadcast('viewer:update', $viewer.storage);
        };

        $viewer.get = function(key) {

            if (!$viewer.storage.iUserId) {
                var sLocalStorage = localStorage.getItem('viewer');
                if (sLocalStorage) {
                    $viewer.storage = JSON.parse(sLocalStorage);
                }
            }
            
            return key ? $viewer.storage[key] : $viewer.storage;
        };

        $rootScope.$on('user:login', function(e, data) {

            $viewer.set(data);
            Viewer.set(data);
        });

        $rootScope.$on('user:logout', function(e, data) {

            $viewer.reset();
            Viewer.reset();
        });

        $rootScope.$on('user:updateAvatar', function(e, data) {

            var newData = {
                sPhotoUrl: data.user_image,
                sFullPhotoUrl: data.user_image,
                PhotoImg_Url: data.user_image,
                BigUserProfileImg_Url: data.user_image
            };

            $viewer.update(newData);
            Viewer.update(newData);
        });

        $rootScope.$on('user:updateInfo', function(e, data) {

            var newData = {
                sFullName: data.full_name,
                fullname: data.full_name,
                full_name: data.full_name,
                sTitle: data.full_name
            };

            $viewer.update(newData);
            Viewer.update(newData);
        });

        return $viewer;
    };
});