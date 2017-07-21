define([
    'global/validator',
    'user/model/signup',
    'global/viewer',
    'global/base/BaseController',
], function(Validator, signupData, Viewer, Ctrl) {

    var viewer = require('global/viewer');

    return function($injector, $http, userLoginFacebook, userLoginTwitter, $site, $scope, $viewer, $location, gettext, $rootScope, $modal, $http2, $window, $ionicSideMenuDelegate, $language, $timeout, gettextCatalog, $coreSettings) {

        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });

        $scope.lockMenu = function() {
            $ionicSideMenuDelegate.canDragContent(false);
            if (window.nativeControl) {
                window.nativeControl.lockMenu();
            }
        };

        $scope.lockMenu();

        // User login type
        $scope.userLoginType = $coreSettings.get('user_login_type');

        $scope.$on('coreSettings:update', function() {
            $scope.userLoginType = $coreSettings.get('user_login_type');
        });

        $scope.onLanguageSetting = $language.doSelectLanguage;

        /**
         * Logout
         */
        $scope.doLogout = function() {
            var successCb = function() {
                console.log('doLogout', arguments); // ignore error
            };

            var errorCb = function() {
                console.warn('doLogout', arguments); // ignore error
            };

            $http2.post('user/logout').success(successCb).error(errorCb);

            localStorage.removeItem('token');
            $http.defaults.headers.common.token = '';

            $rootScope.$broadcast('user:logout');
        };

        $scope.doLogout();

        // initial login data
        $scope.loginData = {
            sLogin: "",
            sPassword: ""
        };

        $http.defaults.headers.common.token = "";

        // Perform the login action when the user submits the login form
        $scope.doLogin = function() {

            if ($scope.isProcessing) {
                return;
            }

            // block ui then show loading case.
            $scope.isProcessing = true;

            $http2.post('user/login', $scope.loginData)
                .success($scope.doLoginSuccess)
                .error($scope.doLoginError)
                .finally(function() {
                    $scope.isProcessing = false;
                });
        };

        $scope.doLoginSuccess = function(data) {

            if (data.error_code) {
                $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                return;
            }

            // reset login data for later loaded data.
            $scope.loginData = {};

            viewer = data;

            // save data
            localStorage.setItem('token', data.token);

            localStorage.setItem('viewer', JSON.stringify(data));

            $http.defaults.headers.common.token = data.token;

            $rootScope.$broadcast('user:login', data);

            // check subscription
            if ($viewer.get('bSubscription') === false) {
                return $location.path('app/subscriptions/browse');
            }

            window.location.href = 'index.html';
        };

        $scope.doLoginError = function() {
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
        };

        $scope.doLoginByFacebook = userLoginFacebook.login;
        $scope.doLoginByTwitter = userLoginTwitter.login;
    };
});