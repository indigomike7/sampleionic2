define([
    'global/base/BaseController',
    'moment'
], function(Ctrl, Moment) {

    return function($scope, $rootScope, $http2, $site, $q, $state,  $injector, $modal, gettext, gettextCatalog, $viewer, $coreSettings) {

        var iUserId  = $state.params.iUserId;
        var currentYear = new Date().getFullYear();
        $scope.minYear = $coreSettings.get('user_date_of_birth_start') || currentYear - 99;
        $scope.maxYear = $coreSettings.get('user_date_of_birth_end') || currentYear - 16;

        $scope.isProcessing = false;

        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });

        $scope.iUserId = iUserId;
        $scope.dataReady = false;

        $scope.form = {
            genderOptions: [],
            aChildLocations: [],
            perms: {}
        };

        $scope.formData = {
            iUserId: iUserId,
            sCountryIso: 0,
            iCountryChildId: 0
            // should init from current profile by id.
        };

        $scope.tmpData = {};

        $scope.getForm = function() {
            var sendData =  {
                iUserId: iUserId
                //fields: 'profile,genderOptions'
            };

            $http2.post('user/formeditprofile',sendData)
                .success(function(data){
                    //$.extend($scope.formData,data.info);
                    $scope.form.aLocations = data.aLocations;
                    $scope.form.genderOptions = data.gender_options;
                    $scope.form.perms = data.perms;

                    $scope.formData.iGender = data.info.sGender ? (data.info.sGender =='Male' ? 1 : 2) : '';
                    $scope.formData.sAbout = data.info.sAboutMe;
                    $scope.formData.iCountryChildId = data.info.iCountryChildId;
                    $scope.formData.sCity = data.info.sCity;
                    $scope.formData.sCountryIso = data.info.sCountryISO;
                    $scope.getChildLocations();
                    $scope.formData.sZipCode = data.info.sZipCode;

                    if (ionic.Platform.isIOS() && data.info.sDateOfBirthYMD) {
                        $scope.tmpData.sBirthday = new Date(data.info.sDateOfBirthYMD);
                    } else {
                        $scope.formData.sBirthday = data.info.sDateOfBirthYMD;
                    }

                    $scope.dataReady = true;
                })
                .error(function(){
                    $modal.toast(gettextCatalog.getString('Can not get data from server'));
                });
        };
        $scope.onSave = function(){

            if ($scope.isProcessing || !$scope.isValidData(true)) {
                return;
            }

            $scope.isProcessing = true;

            if (!$scope.form.aChildLocations.length) {
                $scope.formData.iCountryChildId = 0;
            }

            $scope.formData.sCountryIso = $scope.formData.sCountryIso || 0;

            if (ionic.Platform.isIOS() && $scope.tmpData.sBirthday) {
                $scope.formData.sBirthday = Moment($scope.tmpData.sBirthday.getTime()).format('YYYY-MM-DD');
            }

            if (!$scope.form.perms['user.can_edit_dob']) {
                delete($scope.formData.sBirthday);
            }

            $http2.post('user/edit_profile',$scope.formData)
                .success($scope.onSaveSuccess)
                .error(function(){
                    $modal.toast(gettextCatalog.getString('Can not process your request'));
                })
                .finally(function(){
                    $scope.isProcessing = false;
                });
        };

        $scope.getChildLocations = function() {

            $scope.form.aChildLocations = [];

            sendData = {
                sCountryIso: $scope.formData.sCountryIso
            };

            $http2.get('core/getchilds',sendData)
                .success(function(data){
                    $scope.form.aChildLocations = data;
                })
                .error(function(){
                });
        };

        $scope.onSaveSuccess = function(data) {

            if (data.error_code) {
                return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
            }

            $modal.toast(data.message);
            $viewer.update({
                profile: $scope.formData
            });
            //$rootScope.$broadcast('user:updateInfo', data);
            $scope.goBack();
        };

        $scope.isValidData = function(bAlert) {

            // only validate user's birthday for now, many rules may included in the future
            var isValidBirthday = true;
            if ($scope.formData.sBirthday || (ionic.Platform.isIOS() && $scope.tmpData.sBirthday)) {
                var newDate;
                
                if (ionic.Platform.isIOS()) {
                    newDate = $scope.tmpData.sBirthday;
                } else {
                    newDate = Date.parse($scope.formData.sBirthday);
                    if (typeof newDate === "number") {
                        newDate = new Date(newDate);
                    }
                }

                var minDate = Date.parse($scope.minYear + '/01/01 00:00:00');
                var maxDate = Date.parse($scope.maxYear + '/12/31 23:59:59');

                if (newDate.getTime() > maxDate) {
                    isValidBirthday = false;
                } else if (newDate.getTime() < minDate) {
                    isValidBirthday = false;
                }
            }

            if (!isValidBirthday) {
                bAlert && $modal.alert(sprintf(gettextCatalog.getString('Birthday can not be larger than %1$s or lower than %2$s'), $scope.maxYear, $scope.minYear));
                return false;
            }

            return true;
        };

        $scope.getForm();
    };
});