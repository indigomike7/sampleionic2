define([
    'global/validator',
    'global/base/BaseController',
], function(Validator, Ctrl) {
    return function($file, $injector, $scope, $ionicPopup,$viewer,$http2, $site, $modal, gettext, gettextCatalog, $location, $window, $state) {
        $site.debug > 2 && console.log('UltimateVideoAddVideoCtrl');
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

        $scope.formData = {
            sVideoSource: '',
            sVideoCode: '',
            sUrl: '',
            sPath: '',
            sTitle: '',
            sDescription: "",
            iCategoryId: 0,
            iPrivacy: 0,
            sTags: '',
            bAllowUploadChannel: false
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
                        if(data.bCanAddVideo == 0) {
                            $modal.alert(data.message);
                            $scope.goBack();
                        }
                        $scope.dataReady = true;
                        $scope.form.categoryOptions  =  data.category_options;
                        $scope.form.viewOptions  = data.view_options;
                        $scope.formData.iPrivacy =  data.default_privacy_setting;
                    }

                }).error(function() {

            });
        };
        // implement do save
        $scope.doSave =  function(){

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

            if($scope.formData.sVideoSource != 'Uploaded'){
                if($scope.formData.sUrl){
                    $scope.doCreateVideoFromUrl();
                }else{
                    $modal.alert(gettextCatalog.getString('Please select video'));
                }
            }
            else{
                if($scope.formData.sPath){

                    $scope.doCreateVideoFromDevice();
                }else{
                    $modal.alert(gettextCatalog.getString('Please select video'));
                }

            }
        };

        $scope.doCreateVideoFromUrl = function(){

            if($scope.isProcessing){
                return true;
            }

            $scope.isProcessing = true;

            $http2.post('ultimatevideo/create', $scope.formData)
                .success(function(data){
                    if (data.error_code) {
                        $modal.alert(data.error_message);
                        return ;
                    }else{
                        $modal.toast(data.message);
                        $scope.isProcessing =  false; // release blocking status
                        $scope.goBack();
                    }
                })
                .error(function(){
                    $site.debug > 2 && console.log(arguments);
                })
                .finally(function(){
                    $scope.isProcessing = false;
                });
        };

        $scope.doCreateVideoFromDevice = function(){

            if($scope.isProcessing){
                return true;
            }

            // $modal.alert(JSON.stringify($scope.data));
            $scope.isProcessing = true;

            $http2.upload(
                'ultimatevideo/create',
                    $scope.formData.sPath,
                        $scope.formData,
                        'video')
                    .then(function(data){
                        $scope.isProcessing =  false; // release blocking status
                        if(data.error_code){
                            $modal.alert(data.error_message);
                            return ;
                        }else{
                            $modal.toast(data.message);
                            $scope.goBack();
                        }
                    }, function(error){
                        $scope.isProcessing = false;

                        if (error.code == FileTransferError.ABORT_ERR) {
                        return $modal.toast(gettextCatalog.getString('Canceled'));
                    }

                    $modal.alert(gettextCatalog.getString('Can not upload video'));
                });
        };

        $scope.doSelectFromUrl = function(){
            $modal.prompt(gettextCatalog.getString('Paste your video URL'), function(result) {
                $scope.$$phase || $scope.$apply();

                if (result.buttonIndex == 2) {
                    return true;
                }
                if (!result.input1) {
                    return false;
                }

                if(!$scope.extractCode(result.input1)){
                    return $modal.alert(gettextCatalog.getString('Not Supported Video URL'));
                }

                // Get video information
                $http2.post('ultimatevideo/validateUrl', {
                    sVideoCode: $scope.formData.sVideoCode,
                    sVideoSource: $scope.formData.sVideoSource
                })
                    .success(function(data){
                        if (data.error_code) {
                            $modal.alert(data.error_message);
                        }else{
                            // set title and description
                            $scope.formData.sTitle = data.title;
                            $scope.formData.sDescription = data.description;
                            $scope.isProcessing =  false; // release blocking status
                        }
                    })
                    .error(function(){
                        $site.debug > 2 && console.log(arguments);
                    })
                    .finally(function(){
                        $scope.isProcessing = false;
                    });


                // reset spath video from device
                $scope.formData.sPath = '';
                $scope.formData.sUrl = result.input1;
            }, gettextCatalog.getString('Video URL'), [gettextCatalog.getString('OK'), gettextCatalog.getString('Cancel')]);
        };

        $scope.doRemoveSelectFromUrl =  function(){
            $scope.formData.sUrl = '';
            $scope.formData.sPath = '';
            $scope.formData.sVideoSource  = '';
        };

        $scope.doRemoveSelectFromDevice =  function(){
            $scope.formData.sUrl = '';
            $scope.formData.sPath = '';
            $scope.formData.sVideoSource  = '';
        };

        $scope.doSelectFromDevice = function(){
            navigator.camera.getPicture(function(fileURI){
                // success handler
                $scope.formData.sPath = fileURI;
                $scope.formData.sUrl = '';
                $scope.formData.sVideoSource = 'Uploaded';

                $scope.$$phase || $scope.$apply();
            }, function(msg) {
                if (msg == 20) { // PERMISSION_DENIED_ERROR = 20
                    $modal.alert(gettextCatalog.getString('Illegal Access'));
                }
            }, {
                quality : 50,
                destinationType : Camera.DestinationType.FILE_URI,
                sourceType : Camera.PictureSourceType.PHOTOLIBRARY,
                mediaType : Camera.MediaType.VIDEO
            });
        };

        $scope.extractCode = function(url) {
            var videoid;
            videoid = url.match(/(?:https?:\/{2})?(?:w{3}\.)?youtu(?:be)?\.(?:com|be)(?:\/watch\?v=|\/)([^\s&]+)/);
            if (videoid) {
                $scope.formData.sVideoSource = 'Youtube';
                $scope.formData.sVideoCode = videoid[1];
                return true;
            }

            videoid = url.match(/(?:https?:\/{2})?(?:w{3}\.)?vimeo.com\/(\d+)($|\/)/);
            if (videoid) {
                $scope.formData.sVideoSource = 'Vimeo';
                $scope.formData.sVideoCode = videoid[1];
                return true;
            }

            videoid = url.match(/^.+dailymotion.com\/(video|hub)\/([^_]+)[^#]*(#video=([^_&]+))?/);
            if (videoid) {
                $scope.formData.sVideoSource = 'Dailymotion';
                $scope.formData.sVideoCode = videoid[2];
                return true;
            }

            videoid = url.match(/http(?:s?):\/\/(?:www\.|web\.|m\.)?facebook\.com\/([A-z0-9\.]+)\/videos(?:\/[0-9A-z].+)?\/(\d+)(?:.+)?$/);
            if (videoid) {
                $scope.formData.sVideoSource = 'Facebook';
                $scope.formData.sVideoCode = videoid[2];
                return true;
            }

            var ext = url.substr(url.lastIndexOf('.') + 1);
            if (ext.toUpperCase() == 'MP4') {
                $scope.formData.sVideoSource = 'VideoURL';
                $scope.formData.sVideoCode = url;
                return true;
            }
            var code = url.match(/(<iframe.*? src=(\"|\'))(.*?)((\"|\').*)/);
            if (code && code.length > 2) {
                $scope.formData.sVideoSource = 'Embed';
                $scope.formData.sVideoCode = code[3];
                return true;
            }
            return false;
        };

        $scope.loadInit();
    };
});
