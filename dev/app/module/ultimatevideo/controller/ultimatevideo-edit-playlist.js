define([
    'ultimatevideo/model/playlist',
    'global/validator',
    'ultimatevideo/controller/ultimatevideo-playlist-form'
], function(PlaylistModel, Validator, Ctrl) {
    return function($file, $injector, $scope, $ionicPopup,$viewer,$http2, $site, $modal, gettext, gettextCatalog, $location, $window, $state) {
        $site.debug > 2 && console.log('UltimateVideoEditPlaylistCtrl');
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

        $scope.item = $.extend({}, PlaylistModel, {
            iPlaylistId: $state.params.iPlaylistId
        });

        if (typeof($state.current.modelType) != 'undefined') {
            angular.extend($scope.item, {
                sModelType: $state.current.modelType
            });
        }

        $scope.dataReady = false;
        $scope.form =  {};
        $scope.isProcessing = false;
        $scope.saveApi = 'ultimatevideo/editPlaylist';
        $scope.searchVideos = {
            sView: 'all',
            iPage: 1,
            sSearch: '',
            iCategory: 0,
            iLimit: 5,
            sOrder: 'creation_date',
            iTotalVideo: 0,
            iPlaylistId: $scope.item.iPlaylistId
        };
        $scope.formData = {
            sTitle: '',
            sDescription: "",
            iCategoryId: 0,
            iPrivacy: 0,
            photoPath: "",
            iPlaylistId: $scope.item.iPlaylistId,
            aRemoveVideos: []
        };

        if(typeof $state.params.sParentType != 'undefined'){
            $scope.formData.sModule = $state.params.sParentType;
            $scope.formData.iItemId =  $state.params.iParentId;
            $scope.formData.sSubjectType = $state.params.sParentType;
            $scope.formData.iSubjectId =  $state.params.iParentId;
        }

        $scope.form.bCanUpload = true;//$site.getPerm('video.upload');

        $scope.loadInit = function(){
            $http2.post('ultimatevideo/formEditPlaylist', {
                bPrivacyNoCustom: true,
                iPlaylistId: $scope.item.iPlaylistId
            })
                .success(function(data) {
                    if(data.error_code){
                        $modal.alert(data.error_message);
                        $scope.goBack();
                    }else{
                        $scope.dataReady = true;
                        $scope.form.aCategoryOptions  =  data.category_options;
                        $scope.form.aViewOptions  = data.view_options;
                        $scope.formData.iPrivacy =  data.iPrivacy;
                        $scope.formData.iCategoryId = data.iCategoryId;
                        $scope.formData.sTitle = data.sTitle;
                        $scope.formData.sDescription = data.sDescription;
                        $scope.formData.photoPath = data.photoPath;
                    }

                }).error(function() {

            });
        };

        $scope.loadInit();
    };
});
