define([
    'quiz/model/quiz',
    'global/validator',
    'global/base/BaseController'
], function(QuizModel, Validator, Ctrl) {

    return function($scope, $injector,  $state, $location, $http2, $site, $modal, gettext, gettextCatalog) {


        /**
         * init item identity from state/url
         */
        var iQuizId = $scope.item ? $scope.item.getId() : $state.params.iQuizId;
        var iTakerId = $state.params.iUserId ? $state.params.iUserId : $scope.iTakerId;


        /**
         * extend base controller
         */
        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });

        //init quiz item from quiz model
        $scope.item = $.extend({}, QuizModel, {
            iTakerId: iTakerId
        });

        // hold on display
        $scope.dataReady  = false;
        $scope.isProcessing = false;

        // init forn element
        $scope.form = {};

        $scope.initForm = function(){

            var sendData = {
                iQuizId: iQuizId,
                iUserId: iTakerId
            };
            $http2.post('quiz/result',sendData)
                .success(function(data){
                    if(data.error_code){
                        $modal.alert(data.error_message);
                    }else{
                        // get quiz detail and permissions
                        $.extend($scope.item, data);

                        // map data to form
                        //$scope.form.aQuestions = $scope.mapQuestions(data.questions);
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

        $scope.initForm();

        return $scope;
    };
});