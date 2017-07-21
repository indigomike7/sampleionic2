define([
    'poll/model/poll',
    'global/validator',
    'poll/controller/poll-form'
], function(PollModel, Validator, PollFormController) {

    return function($scope, $injector,  $state, $location, $http2, $site, $modal, gettext, gettextCatalog) {

        /**
         * check permission
         */
        // $site.requirePerm('poll.edit');

        /**
         * init item identity from state/url
         */
        var iPollId = $state.params.iPollId;

        /**
         * extend base controller
         */
        $injector.invoke(PollFormController, this, {
            $scope: $scope
        });

        //init poll item from poll model
        $scope.item = $.extend({}, PollModel, {
            iPollId: iPollId
        });

        // hold on display
        $scope.dataReady  = false;
        $scope.isProcessing = false;

        // prepare formData to be sent with edit api
        $scope.formData = {
            iPollId: iPollId
        };

        // init forn element
        $scope.form = {};
        var optionIndex = 0;

        $scope.initForm = function(){

            var sendData = {
                iPollId: iPollId
            };
            $http2.post('poll/formedit',sendData)
                .success(function(data){
                    if(data.error_code){
                        $modal.alert(data.error_message);
                    }else{
                        // get poll detail and permissions
                        $.extend($scope.item, data.detail);
                        $.extend($scope.perms, data.perms);

                        // map data to form
                        $scope.form.viewOptions  =  data.view_options;
                        $scope.form.commentOptions  =  data.comment_options;
                        $scope.form.aAnswer = $scope.item.aAnswer;

                        $scope.formData.sQuestion = $scope.item.sQuestion;
                        $scope.formData.iPrivacy = $scope.item.iPrivacy;
                        $scope.formData.iPrivacyComment = $scope.item.iPrivacyComment;
                        $scope.formData.iHideVote = String($scope.item.bIsHideVote);
                        $scope.dataReady  = true;
                    }
                })
                .error(function(){
                    $modal.toast(gettextCatalog.getString('Can not get data from server'));
                });
        };

        $scope.onSave = function(){

            if ($scope.isProcessing || !$scope.isValidData()) {
                return ;
            }

            // get answers
            $scope.formData.aAnswer = JSON.stringify($scope.getAnswers());

            $scope.isProcessing = true;

            if ($scope.formData.photoPath) {
                $scope.doSaveWithPhoto();
            } else {
                $scope.doSave();
            }
        };

        $scope.doSave = function(){

            $http2.post('poll/edit', $scope.formData)
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

            $http2.upload('poll/edit', $scope.formData.photoPath, $scope.formData)
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
                $location.path('app/poll/' + iPollId);
            }
        };

        $scope.getAnswers = function() {

            var aAnswer = [];

            // convert the answer to correct format
            if ($scope.form.aAnswer.length) {
                $scope.form.aAnswer.forEach(function(item){
                    aAnswer.push({
                        answer_id: item.iAnswerId || 0,
                        answer: item.sAnswer
                    });
                });
            }

            return aAnswer;
        };

        $scope.initForm();

        return $scope;
    };
});