define([
    'global/validator',
    'global/base/BaseController',
], function(Validator, Ctrl) {
    return function($file, $injector, $scope, $ionicPopup,$viewer,$http2, $site, $modal, gettext, gettextCatalog, $location, $window, $state) {
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
            sUrl: '',
            sPath: '',
            type: '',
            title: '',
            parent_id: $viewer.get('iUserId') ,
            parent_type: 'user',
            description: "",
            tags: "",
            category_id: 0,
            auth_view: '0',
            auth_comment: '0',
            search: 1
        };

        if(typeof $state.params.sParentType != 'undefined'){
            $scope.formData.sModule = $state.params.sParentType;
            $scope.formData.iItemId =  $state.params.iParentId;
            $scope.formData.sSubjectType = $state.params.sParentType;
            $scope.formData.iSubjectId =  $state.params.iParentId;
        }

        //$scope.form.bCanUpload =$site.getPerm('video.upload');

        $scope.loadInit = function(){

            $http2.post('video/formadd', {
                bPrivacyNoCustom: true
            })
                .success(function(data) {
                    if(data.error_code){
                        $modal.alert(data.error_message);
                        $scope.goBack();
                    }else{
                        $scope.dataReady = true;
                        $scope.form.categoryOptions  =  data.category_options;
                        $scope.form.viewOptions  = data.view_options;
                        $scope.form.commentOptions = data.comment_options;
                        $scope.form.bCanUpload = data.bCanUploadVideo;
                        $scope.formData.auth_view =  data.default_privacy_setting;

                        if($scope.form.commentOptions.length > 0){
                            $scope.formData.auth_comment  = $scope.form.commentOptions[0].sValue;
                        }
                    }

                }).error(function() {

                });
        };
        // implement do save
        $scope.doSave =  function(){

            if($scope.isProcessing)
                return true;

            if(!$scope.formData.title){
                $modal.alert(gettextCatalog.getString('Video title is required'));
                return ;
            }

            /* remap data, because server api use diffrent variable when creating and uploading video, beh
            video/upload:
             sTitle: this.$form_title.val(),
             sDescription: this.$form_description.val(),
             iCategoryId: this.$form_category.val() || 0,
             iPrivacy: this.$form_privacy.val() || 0,
             iPrivacyComment: this.$form_privacy_comment.val() || 0

            video/create:
             title: this.$form_title.val(),
             description: this.$form_description.val(),
             category_id: this.$form_category.val() || 0,
             auth_view: this.$form_privacy.val() || 0,
             auth_comment: this.$form_privacy_comment.val() || 0,
             sUrl: this.$form_url.val()
             */

            if($scope.formData.sVideoSource == 'url'){
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

            $http2.post('video/create', $scope.formData)
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
                    console.log(arguments);
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

            // create new value because video upload accept these, blah
            $scope.formData.sTitle = $scope.formData.title;
            $scope.formData.sDescription = $scope.formData.description;
            $scope.formData.iCategoryId = $scope.formData.category_id;
            $scope.formData.iPrivacy = $scope.formData.auth_view;
            $scope.formData.iPrivacyComment = $scope.formData.auth_comment;

            $http2.upload(
                'video/upload',
                $scope.formData.sPath,
                $scope.formData,
                'video')
                .then(function(data){
                    $scope.isProcessing =  false; // release blocking status
                    if(data.error_code){
                        $modal.alert(data.error_message);
                        return ;
                    }else{

                        var postData = {
                            iVideoId: data.iVideoId
                        };
                        var settings = {
                            timeout: 0
                        }
                        $http2.post('video/convert', postData, settings); // why have to call convert ?
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
            $scope.formData.videoUrl = "";

            $modal.prompt(gettextCatalog.getString('Paste your video URL'), function(result) {
                $scope.$$phase || $scope.$apply();

                if (result.buttonIndex == 2) {
                    return true;
                }
                if (!result.input1) {
                    return false;
                }
                if (!Validator.isUrl(result.input1)) {
                    return $modal.alert(gettextCatalog.getString('Invalid Video URL'));
                }

                // iVideoType: int (1: youtube, 2: vimeo, 3: url
                if (Validator.isYoutubeVideoUrl(result.input1)) {
                    $scope.formData.type = 1;
                } else if (Validator.isVimeoVideoUrl(result.input1)) {
                    $scope.formData.type = 2;
                } else {
                    $scope.formData.type = 3;
                }

                // reset spath video from device   
                $scope.formData.sPath = '';
                $scope.formData.sUrl = result.input1;
                $scope.formData.sVideoSource = 'url';
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
                $scope.formData.sVideoSource = 'device';

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

        $scope.loadInit();
    };
});
