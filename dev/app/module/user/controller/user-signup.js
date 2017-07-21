define([
    'jcrop',
    'moment',
    'global/data/timezone',
    'text!tpl/user/user-term.html',
    'text!tpl/user/packages.html',
], function(Jcrop, Moment, timezones) {

    /**
     * ionic modal animation type.
     */
    var animation = 'slide-in-up';

    function SignupController($scope, $http, $http2, userSignupData, $ionicModal, $site, $modal, $rootScope, $ionicSideMenuDelegate, $location, $coreSettings, $timeout, gettextCatalog) {

        $scope.formReady = false;
        $scope.packagesData = [];

        $scope.lockMenu = function() {
            $ionicSideMenuDelegate.canDragContent(false);
            if(window.nativeControl){
                window.nativeControl.lockMenu();
            }
        };

        $scope.lockMenu();

        // sign up data
        $scope.data = userSignupData.data;
        $scope.timezones = timezones;
        $scope.tmpData = {};

        if ($scope.data.sBirthday && ionic.Platform.isIOS()) {
            $scope.tmpData.sBirthday = new Date($scope.data.sBirthday);
        }

        $scope.isProcessing = false;

        // reset token
        $http.defaults.headers.common.token = "";

        // go back
        $scope.cancel = function() {

            var cf = {
                title: gettextCatalog.getString('Confirm'),
                content: gettextCatalog.getString('No account will be created if you cancel this screen'),
                buttons: [gettextCatalog.getString('OK'), gettextCatalog.getString('Cancel')]
            };

            $modal.confirm(cf.content, function(selected) {
                if (selected == 1) {
                    $location.path('app/login');
                    $scope.$$phase || $scope.$apply();
                }
            }, cf.title, cf.buttons);
        };

        // form


        $scope.fetchData = function() {

            var success = function(data) {
                if (data.error_code) {
                    $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                    return $scope.goBack();
                }

                $scope.packagesData = data.aPackage;

                if ($scope.packagesData.length) {
                    if (!data.aPerm.bSubscribeIsRequiredOnSignUp) {
                        $scope.packagesData.unshift({
                            iPackageId: 0,
                            sTitle: gettextCatalog.getString('Free')
                        });
                    }

                    $scope.data.iPackageId = $scope.packagesData[0].iPackageId;
                }

                $scope.formReady = true;
            };

            var error = function() {
                $modal.alert(gettextCatalog.getString('Can not load data from server'));
                $scope.goBack();
            };

            $http2.get('user/getsubscriptionpackages')
                .success(success)
                .error(error);
        };

        $scope.fetchData();

        // membership
        $scope.subscriptionListData = {
            sView: 'signup'
        };

        $scope.showPackages = function() {

            $scope.modalPackages = $ionicModal.fromTemplate(require('text!tpl/user/packages.html'), {
                scope: $scope,
                animation: animation,
            });

            $scope.modalPackages.show();
        };

        $scope.hidePackages = function() {

            $scope.modalPackages.remove();
        };

        $scope.isValidData = function(bAlert) {

            $scope.invalidData = {
                sFullName: !$scope.data.sFullName || !$scope.data.sFullName.trim(),
                sEmail: !$scope.data.sEmail || !$scope.data.sEmail.trim(),
                sPassword: !$scope.data.sPassword || $scope.data.sPassword.length < 6 || $scope.data.sPassword.indexOf(' ') > -1,
                bIsAgreeTerms: !$scope.data.bIsAgreeTerms
            };

            if ($scope.invalidData.sFullName) {
                bAlert && $modal.alert(gettextCatalog.getString('Fullname can not be empty'));
                return false;
            }

            if ($scope.invalidData.sEmail) {
                bAlert && $modal.alert(gettextCatalog.getString('Email is not valid'));
                return false;
            }

            if (!$scope.data.sPassword) {
                bAlert && $modal.alert(gettextCatalog.getString('Password can not be empty'));
                return false;
            }

            if ($scope.data.sPassword.length < 6) {
                bAlert && $modal.alert(gettextCatalog.getString('Password must be longer than 6'));
                return false;
            }

            if ($scope.invalidData.bIsAgreeTerms) {
                bAlert && $modal.alert(gettextCatalog.getString('You must agree to the Terms of Service to continue'));
                return false;
            }

            return true;
        };

        $scope.onSave = function() {
            // check email if is exist
            if ($scope.isProcessing || !$scope.isValidData(true)) {
                return;
            }

            $scope.isProcessing = true;
            $scope.error = {};

            var success = function(data) {
                if (data.error_code) {
                    $scope.error.sEmail = true;
                    return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                    $scope.isProcessing = false;
                }

                $scope.doSignup();
            };

            var error = function() {
                $modal.alert(gettextCatalog.getString('Can not load data from server'));
                $scope.isProcessing = false;
            };

            var final = function() {

            };

            $http2.post('user/signup_check_email', {
                sEmail: $scope.data.sEmail
            }).success(success).error(error).
                finally(final);
        };

        $scope.doSignup = function() {

            $scope.isProcessing = true;

            var success = function(data) {

                if (data.error_code) {
                    if (data.error_code == 4 || (data.error_code == 5 && !data.iPurchaseId)) { // need to verify email or admin approve
                        $modal.alert(data.error_message);
                        $location.path('app/login');
                    } else if (data.iPurchaseId) {
                        $scope.signupSuccess(data);
                    } else {
                        $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server')); //defensive programming
                    }
                } else {
                    $scope.signupSuccess(data);
                }
            };

            var error = function() {
                $modal.alert(gettextCatalog.getString('Can not load data from server'));
            };

            $http2.post('user/signup_account', $scope.data)
                .success(success)
                .error(error)
                .
                finally(function() {
                    $scope.isProcessing = false;
                });

        };

        $scope.showTerm = function() {

            $scope.modalTerm = $ionicModal.fromTemplate(require('text!tpl/user/user-term.html'), {
                scope: $scope,
                animation: animation,
            });

            $scope.modalTerm.show();
        };

        $scope.hideTerm = function() {

            $scope.modalTerm.remove();
        };

        $scope.signupSuccess = function(data) {

            window.localStorage.setItem('token', data.token);
            window.localStorage.setItem('viewer', JSON.stringify(data));

            $http.defaults.headers.common.token = data.token;

            if (data.iPurchaseId && $scope.data.iPackageId) { // go to purchase if a non-free package was selected
                return $location.path('app/subscription/' + $scope.data.iPackageId + '/' + data.iPurchaseId);
            }

            $rootScope.$broadcast('user:login', data);

            $modal.toast(gettextCatalog.getString('Signup sucessfully'));
            window.location.href = 'index.html';
        };
    }

    return SignupController;
});