define([
    'global/validator',
    'ultimatevideo/controller/ultimatevideo-playlist-form'
], function(Validator, Ctrl) {
    return function($file, $injector, $scope, $ionicPopup,$viewer,$http2, $site, $modal, gettext, gettextCatalog, $location, $window, $state) {
        $site.debug > 2 && console.log('UltimateVideoAddPlaylistCtrl');
        /**
         * check require permission
         */
        //$site.requirePerm('video.create');

        /**
         * extend base controllers
         */
        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });

        $scope.dataReady = false;
        $scope.form =  {};
        $scope.isProcessing = false;
        $scope.saveApi = 'ultimatevideo/createPlaylist';

        $scope.formData = {
            sTitle: '',
            sDescription: "",
            iCategoryId: 0,
            iPrivacy: 0,
            photoPath: ""
        };

        if(typeof $state.params.sParentType != 'undefined'){
            $scope.formData.sModule = $state.params.sParentType;
            $scope.formData.iItemId =  $state.params.iParentId;
            $scope.formData.sSubjectType = $state.params.sParentType;
            $scope.formData.iSubjectId =  $state.params.iParentId;
        }

        $scope.form.bCanUpload = true;//$site.getPerm('video.upload');

        $scope.loadInit = function(){
            $http2.post('ultimatevideo/formadd', {
                bPrivacyNoCustom: true
            })
                .success(function(data) {
                    if(data.error_code){
                        $modal.alert(data.error_message);
                        $scope.goBack();
                    }else{
                        $scope.dataReady = true;
                        $scope.form.aCategoryOptions  =  data.category_options;
                        $scope.form.aViewOptions  = data.view_options;
                        $scope.formData.iPrivacy =  data.default_privacy_setting;
                    }

                }).error(function() {

            });
        };

        $scope.loadInit();
    };
});
