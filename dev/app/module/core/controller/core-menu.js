define([
    'global/base/BaseController'
], function(Ctrl) {
    return function($injector, $scope, $modal, gettext, gettextCatalog, 
        $location, $language, $coreSettings, $coreMenu, $site, $state, $viewer, 
        $ionicSideMenuDelegate) {

        // extend from base controller

        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });

        $scope.menus = $coreMenu.get();
        $scope.isShowLanguageMenu = false;
        $site.debug > 2 && console.log($scope.menus);

        // refresh menu
        $scope.$on('coreMenu:update', function(e, data) {
            $scope.menus = $coreMenu.get();
            // get language collection to decide show language menu or not
            $scope.isShowLanguageMenu = $site.lang.options.length > 1;
        });
        $scope.viewer = $viewer.get();
        $scope.$on('viewer:update', function(e, data) {
            $scope.viewer = $viewer.get();
        });

        $scope.state = $state;

        $scope.doLanguageSetting = $language.doSelectLanguage;

        $scope.doConfirmLogout = function() {
            $modal.confirm(gettextCatalog.getString('Do you want to log out of application?'), function(result) {
                if (result == 1) {
                    $location.path('app/login');
                    return $scope.$$phase || $scope.$apply();
                }
            }, gettextCatalog.getString('Confirm'), [gettextCatalog.getString('OK'), gettextCatalog.getString('Cancel')]);
            return false;
        };

        $scope.chatModule = $coreSettings.get('chat_module');

        $scope.$on('coreSettings:update', function(e, data) {
            $scope.chatModule = $coreSettings.get('chat_module');
        });

        if (ionic.Platform.isAndroid() && typeof(window.nativeControl) != 'undefined') {
            $scope.$watch(function() {
                return $ionicSideMenuDelegate.getOpenRatio();
            }, function(ratio) {
                if (ratio == -1) { // right menu open
                    window.nativeControl.lockMenu();
                } else if (ratio == 0) { // menu close
                    window.nativeControl.unlockMenu();
                }
            });
        }
    };
});