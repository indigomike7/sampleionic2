define([
    'quiz/model/quiz',
    'global/validator',
    'quiz/controller/quiz-form'
], function(QuizModel, Validator, QuizFormController) {

    return function($scope, $injector,  $state, $location, $http2, $site, $modal, gettext, gettextCatalog) {

        /**
         * check permission
         */
        // $site.requirePerm('quiz.edit');

        /**
         * init item identity from state/url
         */
        var iQuizId = $state.params.iQuizId;

        /**
         * extend base controller
         */
        $injector.invoke(QuizFormController, this, {
            $scope: $scope
        });

        //init quiz item from quiz model
        $scope.item = $.extend({}, QuizModel, {
            iQuizId: iQuizId
        });

        // hold on display
        $scope.dataReady  = false;
        $scope.isProcessing = false;

        // prepare formData to be sent with edit api
        $scope.formData = {
            iQuizId: iQuizId
        };

        // init forn element
        $scope.form = {};
        var optionIndex = 0;

        $scope.initForm = function(){

            var sendData = {
                iQuizId: iQuizId
            };
            $http2.post('quiz/formedit',sendData)
                .success(function(data){
                    if(data.error_code){
                        $modal.alert(data.error_message);
                    }else{
                        // get quiz detail and permissions
                        $.extend($scope.item, data.detail);
                        $.extend($scope.perms, data.perms);

                        // map data to form
                        $scope.form.viewOptions  =  data.view_options;
                        $scope.form.commentOptions  =  data.comment_options;
                        $scope.form.aQuestions = $scope.mapQuestions(data.questions);
                        $scope.formData.sTitle = $scope.item.sTitle;
                        $scope.formData.sDescription = $scope.item.sDescription;
                        $scope.formData.sAuthView = $scope.item.iPrivacy;
                        $scope.formData.sAuthComment = $scope.item.iCommentPrivacy;
                        $scope.dataReady  = true;
                    }
                })
                .error(function(){
                    $modal.toast(gettextCatalog.getString('Can not get data from server'));
                });
        };

        $scope.mapQuestions = function(questions){

            //question that will be returned
            var finalQuestions = [];

            questions.forEach(function(question){

                // temporary question to push to returned question
                var tempQuestion = {
                    aAnswers: []
                };
                question.answers.forEach(function(answer){

                    tempQuestion.aAnswers.push(
                        {
                            sAnswer: answer.answer,
                            iAnswerId: answer.answer_id,
                            bIsCorrect: parseInt(answer.is_correct)
                        }
                    );
                });

                tempQuestion.sQuestion = question.question;
                tempQuestion.iQuestionId = question.question_id;

                // push to final question
                finalQuestions.push(tempQuestion);
            });

            console.log(finalQuestions);
            return finalQuestions;
        };

        $scope.onSave = function(){

            if ($scope.isProcessing || !$scope.isValidData()) {
                return ;
            }

            // get answers
            $scope.formData.aQuestions = JSON.stringify($scope.form.aQuestions);

            $scope.isProcessing = true;

            if ($scope.formData.photoPath) {
                $scope.doSaveWithPhoto();
            } else {
                $scope.doSave();
            }
        };

        $scope.doSave = function(){

            $http2.post('quiz/edit', $scope.formData)
                .success(function(data){
                    $scope.doSaveSuccess(data);
                })
                .error(function(){
                    $modal.alert(gettextCatalog.getString('Can not load data from server'));
                })
                .finally(function(){
                    $scope.isProcessing = false;
                });

        };

        $scope.doSaveWithPhoto = function(){

            var success = function(data){
                $scope.doSaveSuccess(data);
            }

            var error = function(error){
                if (error.code == FileTransferError.ABORT_ERR) {
                    return $modal.toast(gettextCatalog.getString('Canceled'));
                }

                $modal.alert(gettextCatalog.getString('Can not load data from server'));
            }

            $http2.upload('quiz/edit', $scope.formData.photoPath, $scope.formData)
                .then(success, error)
                .finally(function(){
                    $scope.isProcessing = false;
                });

        };

        $scope.doSaveSuccess = function(data){

            if(data.error_code){
                $modal.alert(data.error_message);
            }else{

                $modal.toast(data.message);
                $location.path('app/quiz/' + iQuizId);
            }
        };

        $scope.initForm();

        return $scope;
    };
});