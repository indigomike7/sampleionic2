define([
    'ultimatevideo/model/video',
    'global/validator',
    'global/base/BaseController',
], function(VideoModel, Validator, Ctrl) {
    return function($file, $injector, $scope, $ionicPopup,$viewer,$http2, $site, $modal, gettext, gettextCatalog, $location, $window, $state) {
        $site.debug > 2 && console.log('UltimateVideoEditVideoCtrl');
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

        $scope.item = $.extend({}, VideoModel, {
            iVideoId: $state.params.iVideoId
        });

        if (typeof($state.current.modelType) != 'undefined') {
            angular.extend($scope.item, {
                sModelType: $state.current.modelType
            });
        }

        $scope.dataReady = false;
        $scope.form =  {};
        $scope.isProcessing = false;
        $scope.formData = {
            sTitle: '',
            sDescription: "",
            iCategoryId: 0,
            iPrivacy: 0,
            sTags: '',
            bAllowUploadChannel: false,
            iType: '',
            iVideoId: $scope.item.iVideoId
        };

        $scope.loadInit = function(){
            $http2.post('ultimatevideo/formEditVideo', {
                bPrivacyNoCustom: true,
                iVideoId: $scope.item.iVideoId
            })
                .success(function(data) {
                    if(data.error_code){
                        $modal.alert(data.error_message);
                        $scope.goBack();
                    }else{
                        $scope.dataReady = true;
                        $scope.form.categoryOptions  =  data.category_options;
                        $scope.form.viewOptions  = data.view_options;
                        $scope.formData.iPrivacy =  data.iPrivacy;
                        $scope.formData.iCategoryId = data.iCategoryId;
                        $scope.formData.sTitle = data.sTitle;
                        $scope.formData.sDescription = data.sDescription;
                        $scope.formData.sTags = data.sTags;
                        $scope.formData.bAllowUploadChannel = data.bAllowUploadChannel;
                        $scope.formData.iType = data.iType;
                    }

                }).error(function() {

            });
        };

        $scope.doSave = function(){
            if($scope.isProcessing)
                return true;

            if(!$scope.formData.sTitle){
                $modal.alert(gettextCatalog.getString('Video title is required'));
                return false;
            }

            if(!$scope.formData.iCategoryId){
                $modal.alert(gettextCatalog.getString('Provide a category this video will belong to'));
                return false;
            }
            $scope.isProcessing = true;
            $http2.post('ultimatevideo/editVideo', $scope.formData)
                .success(function(data){
                    if(data.error_code){
                        $modal.alert(data.error_message);
                        $scope.goBack();
                    }else{
                        $modal.toast('This video has been updated successfully.');
                        $scope.goToPage('app/ultimatevideo/video/' + $scope.item.getId());
                    }
                }).error(function(data){

                }).then(function() {
               $scope.isProcessing = false;
            });
        };


        $scope.loadInit();
    };
});
