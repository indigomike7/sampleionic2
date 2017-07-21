define([
    'global/validator',
    'poll/controller/poll-form'
], function(Validator, PollFormController) {
    return function($scope, $injector, gettext, gettextCatalog, $http2, $site, $modal, $location) {

        /**
         * check permission
         */
        //$site.requirePerm('poll.create');

        /**
         * extends base conroller
         */
        $injector.invoke(PollFormController, this, {
            $scope: $scope
        });

        /**
         * init scrope data
         */
        $scope.isProcessing = false;

        for(var i = 0; i<$scope.perms.iMinAnswer; ++i){
            $scope.form.aAnswer.push({sAnswer: ''});
        }

        $scope.dataReady = false;

        $scope.loadInit = function(){
            $http2.post('poll/formadd')
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
                        $scope.formData.iPrivacy =  data.default_privacy_setting;

                        if($scope.form.commentOptions.length){
                            $scope.formData.iPrivacyComment = $scope.form.commentOptions[0].sValue;
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
            $scope.formData.aAnswer = JSON.stringify($scope.getAnswers());

            $scope.isProcessing = true;

            if ($scope.formData.photoPath) {
                $scope.doSaveWithPhoto();
            } else {
                $scope.doSave();
            }
        }

        $scope.doSave = function(){

            $http2.post('poll/create', $scope.formData)
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

            $http2.upload('poll/create', $scope.formData.photoPath, $scope.formData)
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
                $location.path(data.iPollId ? ('app/poll/' + data.iPollId) : 'app/polls/my');
            }
        };

        $scope.getAnswers = function() {

            var aAnswers = [];
            for(var i =0; i<$scope.form.aAnswer.length; ++i){
                aAnswers.push($scope.form.aAnswer[i].sAnswer);
            }
            return aAnswers;
        };

        $scope.loadInit();

        return $scope;
    };
});