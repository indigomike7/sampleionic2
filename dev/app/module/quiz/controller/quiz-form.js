define([
    'global/validator',
    'global/base/BaseController'
], function(Validator, Ctrl) {
    return function($scope, $injector, gettext, gettextCatalog, $site, $modal, $ionicScrollDelegate) {
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
            sQuestions: '',
            aAnswer: [],
            sAuthView: 0,
            sAuthComment: 0,
            iHideVote: 0
        };

        $scope.form = {
            aQuestions: []
        };

        //set default permissions
        $scope.perms = {
            iMinQuestion: 1,
            iMaxQuestion: 10,
            iMinAnswer: 2,
            iMaxAnswer: 5
        };

        // add and remove form answers
        $scope.onAddQuestion = function(noscroll){
            var aDefaultAnswers = [];
            for (i=0; i<$scope.perms.iMinAnswer; ++i){
                aDefaultAnswers.push({
                    "sAnswer": '',
                    "iAnswerId": '',
                    "bIsCorrect": 0
                });
            }

            // set the first answer to be true
            aDefaultAnswers[0].bIsCorrect = 1;

            $scope.form.aQuestions.push({
                iQuestionId: '',
                sQuestion: '',
                aAnswers: aDefaultAnswers
            });

            if (!noscroll) {
                $ionicScrollDelegate.$getByHandle('quiz_question_list').scrollBottom(true);
            }
        };

        $scope.onRemoveQuestion = function(questionIndex) {
            $scope.form.aQuestions.splice(questionIndex, 1);
        };

        $scope.onAddAnswer = function (questionIndex){
            $scope.form.aQuestions[questionIndex].aAnswers.push({
                "sAnswer": '',
                "iAnswerId": '',
                "bIsCorrect": 0
            });
        };

        $scope.onRemoveAnswer = function(questionIndex, index){
            var aAnswers = $scope.form.aQuestions[questionIndex].aAnswers;

            if (aAnswers[index].bIsCorrect) {
                if (aAnswers[index + 1]){
                    aAnswers[index + 1].bIsCorrect = true;
                } else {
                    aAnswers[index - 1].bIsCorrect = true;
                }
            }
            aAnswers.splice(index, 1);
        };

        $scope.setCorrectAnswer = function(questionIndex, answerIndex){
            $scope.form.aQuestions[questionIndex].aAnswers.forEach(function(item, index){
                item.bIsCorrect = index == answerIndex;
            });
        };

        $scope.isValidData = function(bAlert) {

            if (Validator.isEmpty($scope.formData.sTitle)) {
                $modal.alert(gettextCatalog.getString('Quiz title is required.'));
                return false;
            }

            if (Validator.isEmpty($scope.formData.sDescription)) {
                $modal.alert(gettextCatalog.getString('Quiz description is required.'));
                return false;
            }

            for (var i = 0; i < $scope.form.aQuestions.length; ++i) {

                if (Validator.isEmpty($scope.form.aQuestions[i].sQuestion)) {
                    $modal.alert(gettextCatalog.getString('Questions and Answers could not be empty!'));
                    return false;
                }

                for (var j = 0; j < $scope.form.aQuestions[i].aAnswers.length; ++j) {
                    if (Validator.isEmpty($scope.form.aQuestions[i].aAnswers[j].sAnswer)) {
                        $modal.alert(gettextCatalog.getString('Questions and Answers could not be empty!'));
                        return false;
                    }
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