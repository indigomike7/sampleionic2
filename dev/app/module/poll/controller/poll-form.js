define([
    'global/validator',
    'global/base/BaseController'
], function(Validator, Ctrl) {
    return function($scope, $injector, gettext, gettextCatalog, $site, $modal) {
        /**
         * extends base conroller
         */
        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });

        /**
         * init scrope data
         */

        $scope.formData = {
            sQuestion: '',
            aAnswer: [],
            iPrivacy: 0,
            iPrivacyComment: 0,
            iHideVote: "0"
        };

        $scope.form = {
            aAnswer:[]
        };

        //set default permissions
        $scope.perms = {
            iMaxAnswer: 5,
            iMinAnswer: 2
        };

        // add and remove form answers
        $scope.onAddAnswer = function(){
            $scope.form.aAnswer.push({
                sAnswer: ''
            });
        };

        $scope.onRemoveAnswer = function(index){
            $scope.form.aAnswer.splice(index,1);
        };

        $scope.isValidData = function(bAlert) {

            if (Validator.isEmpty($scope.formData.sQuestion)) {
                $modal.alert(gettextCatalog.getString('Poll title is required.'));
                return false;
            }

            for (var i = 0; i < $scope.form.aAnswer.length; ++i) {
                if (Validator.isEmpty($scope.form.aAnswer[i].sAnswer)) {
                    $modal.alert(gettextCatalog.getString('Answers could not be empty!'));
                    return false;
                }
            }

            return true;
        };

        $scope.onAddPhoto = $scope._setting($scope, function() {

            return [{
                text: gettextCatalog.getString('Take Photo'),
                action: function() {
                    $scope.doAddPhoto('CAMERA');
                }
            }, {
                text: gettextCatalog.getString('Select From Gallery'),
                action: function() {
                    $scope.doAddPhoto('PHOTOLIBRARY');
                }
            }];
        });

        $scope.doAddPhoto = function(sourceType) {

            sourceType = sourceType || 'PHOTOLIBRARY';

            var getSuccess = function(fileURI) {
                $scope.doAddPhotoSuccess(fileURI);
            };

            var getFail = function(msg) {
                console.warn(msg);
                if (msg == 20) { // PERMISSION_DENIED_ERROR = 20
                    $modal.alert(gettextCatalog.getString('Illegal Access'));
                }
            };

            navigator.camera.getPicture(getSuccess, getFail, {
                quality: 50,
                destinationType: navigator.camera.DestinationType.FILE_URI,
                sourceType: Camera.PictureSourceType[sourceType],
                encodingType: Camera.EncodingType.JPEG,
                correctOrientation: true,
                targetWidth: $site.imgTargetSize,
                targetHeight: $site.imgTargetSize
            });
        };

        $scope.doAddPhotoSuccess = function(fileURI) {

            $scope.formData.photoPath = fileURI;

            $scope.$$phase || $scope.$apply();
        };

        $scope.onRemovePhoto = function() {

            $scope.formData.photoPath = null;
        };
    };
});