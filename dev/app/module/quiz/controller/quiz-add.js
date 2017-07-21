define([
    'global/validator',
    'quiz/controller/quiz-form'
], function(Validator, QuizFormController) {
    return function($scope, $injector, gettext, gettextCatalog, $http2, $site, $modal, $location) {

        /**
         * check permission
         */
        //$site.requirePerm('quiz.create');

        /**
         * extends base conroller
         */
        $injector.invoke(QuizFormController, this, {
            $scope: $scope
        });

        /**
         * init scrope data
         */
        $scope.isProcessing = false;

        $scope.dataReady = false;

        $scope.loadInit = function(){
            $http2.post('quiz/formadd')
                .success(function(data){
                    if(data.error_code){
                        $modal.alert(data.error_message);
                        $scope.goBack();
                    }else{

                        // map form and permission
                        $scope.form.viewOptions = data.view_options;
                        $scope.form.commentOptions = data.comment_options;
                        $scope.perms.iMaxAnswer = data.perms.iMaxAnswer;
                        $scope.perms.iMinAnswer = data.perms.iMinAnswer;
                        $scope.perms.iMaxQuestion = data.perms.iMaxQuestion;
                        $scope.perms.iMinQuestion = data.perms.iMinQuestion;
                        $scope.formData.sAuthView =  data.default_privacy_setting;
                        if($scope.form.commentOptions.length){
                            $scope.formData.sAuthComment = $scope.form.commentOptions[0].sValue;
                        }

                        for(var i = 0; i<$scope.perms.iMinQuestion; ++i){
                            $scope.onAddQuestion(true);
                        }

                        $scope.dataReady = true;
                    }

                })
                .error(function(){
                    $modal.alert(gettextCatalog.getString('can not load data from server'));
                });
        };

        $scope.onSave = function() {

            if ($scope.isProcessing || !$scope.isValidData()) {
                return;
            }

            // get answers
            $scope.formData.aQuestions = JSON.stringify($scope.form.aQuestions);

            $scope.isProcessing = true;

            if ($scope.formData.photoPath) {
                $scope.doSaveWithPhoto();
            } else {
                $scope.doSave();
            }
        }

        $scope.doSave = function(){

            $http2.post('quiz/add', $scope.formData)
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

            $http2.upload('quiz/add', $scope.formData.photoPath, $scope.formData)
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
                $location.path(data.iQuizId ? ('app/quiz/' + data.iQuizId) : 'app/quizzes/my');
            }
        };

        $scope.loadInit();

        return $scope;
    };
});