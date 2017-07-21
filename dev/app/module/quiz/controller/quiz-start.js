define([
    'quiz/model/quiz',
    'global/validator',
    'global/base/BaseController'
], function(QuizModel, Validator, Ctrl) {

    return function($scope, $injector, $viewer, $state, $location, $http2, $site, $modal, gettext, gettextCatalog) {

        /**
         * init item identity from state/url
         */
        var iQuizId = $state.params.iQuizId,
            iTakerId = $viewer.get('iUserId')

        /**
         * extend base controller
         */
        $injector.invoke(Ctrl, this, {
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

        $scope.initForm = function(){

            var sendData = {
                iQuizId: iQuizId
            };
            $http2.post('quiz/questions',sendData)
                .success(function(data){
                    if(data.error_code){
                        $modal.alert(data.error_message);
                    }else{
                        // get quiz detail and permissions
                        $.extend($scope.item, data);

                        // map data to form
                        $scope.form.aQuestions = $scope.mapQuestions(data.questions);
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
                    aAnswers: [],
                    sQuestion: question.question,
                    iQuestionId: question.question_id,
                    iSelectedAnswer: ''
                };
                question.answers.forEach(function(answer){

                    tempQuestion.aAnswers.push(
                        {
                            sAnswer: answer.answer,
                            iAnswerId: answer.answer_id,
                            bIsCorrect: parseInt(answer.is_correct),
                        }
                    );
                });

                // push to final question
                finalQuestions.push(tempQuestion);
            });

            console.log(finalQuestions);
            return finalQuestions;
        };

        $scope.isValidAnswer = function() {

            for (i = 0; i < $scope.form.aQuestions.length; i++) {
                if ($scope.form.aQuestions[i].iSelectedAnswer == ''){
                    return false;
                }
            }
            return true;
        }

        $scope.onSubmit = function(){

            if ($scope.isProcessing) {
                return ;
            }

            $scope.isProcessing = true;

            if (!$scope.isValidAnswer()) {

                $scope.isProcessing = false;
                return $modal.alert(gettextCatalog.getString('Please answer all questions.'));
            }

            // get answers
            $scope.formData.aAnswers = $scope.getAnswers();

            $http2.post('quiz/answer', $scope.formData)
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

        $scope.getAnswers = function() {

            var finalAnswer = [];

            $scope.form.aQuestions.forEach(function(question){

                finalAnswer.push({
                    iQuestionId: question.iQuestionId,
                    iAnswerId: question.iSelectedAnswer
                });
            });

            return finalAnswer;
        };

        $scope.doSaveSuccess = function(data){

            if(parseInt(data.error_code)){
                $scope.goBack();
                //$modal.alert(data.error_message);
            }else{

                $modal.toast(data.message);
                $location.path('app/quiz/' + iQuizId + '/result/' + iTakerId);
            }
        };

        $scope.initForm();

        return $scope;
    };
});