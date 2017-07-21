define([], function() {

    return function($scope, $http2, $modal, gettextCatalog) {

        $scope.pollFormReady = false;

        $scope.pollForm = {};

        $scope.pollFormData = {
        	iHideVote: 0
        };

        $scope.pollGetForm = function() {

        	var successCb = function(data) {
        		
                if (data.error_code) {
                    $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                    $scope.onPollFormError && $scope.onPollFormError(); // callback to parent scope
                    return;
                }

                $scope.pollForm = $.extend({
                    answers: ['', '']
                }, data);

                $scope.pollFormReady = true;
        	};

            var errorCb = function() {

                console.warn('poll/formadd', arguments);
                $modal.alert(gettextCatalog.getString('Can not load data from server'));
                $scope.onPollFormError && $scope.onPollFormError(); // callback to parent scope
            };

            $http2.post('poll/formadd').success(successCb).error(errorCb);
        };

        $scope.pollAddAnswer = function() {

            if ($scope.pollForm.perms.iMaxAnswer && $scope.pollForm.answers.length >= $scope.pollForm.perms.iMaxAnswer) {
                return $modal.alert(gettextCatalog.getString('You have reached your limit.'));
            }

            $scope.pollForm.answers.push('');
        };

        $scope.pollRemoveAnswer = function(index) {

            if ($scope.pollForm.answers.length <= 2) {
                return $modal.alert(gettextCatalog.getString('You must have a minimum of 2 answers.'));
            }

            $scope.pollForm.answers.splice(index, 1);
        };

        $scope.pollGetAnswers = function() {

            var answers = [];

            for (var i = 0; i < $scope.pollForm.answers.length; i++) {
                if ($scope.pollForm.answers[i]) {
                    answers.push($scope.pollForm.answers[i]);
                }
            }

            return answers;
        };

        $scope.onSavePoll = function() {

        	if ($scope.isProcessingSavePoll || !$scope.isValidPollData(true)) {
        		return;
        	}

        	$scope.isProcessingSavePoll = true;

        	var successCb = function(data) {

        		if (data.error_code) {
        			return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
        		}

        		// callback to parent scope
        		$scope.onSavePollSuccess && $scope.onSavePollSuccess(data.aPoll);
        	};

        	var errorCb = function() {

        		console.warn('forum/threadpolladd', arguments);
        		$modal.alert(gettextCatalog.getString('Can not load data from server'));
        	};

        	$scope.pollFormData.aAnswer = JSON.stringify($scope.pollGetAnswers());

        	$http2.post('forum/threadpolladd', $scope.pollFormData).success(successCb).error(errorCb).finally(function() {
        		$scope.isProcessingSavePoll = false;
        	});
        };

        $scope.isValidPollData = function(bAlert) {

            if (!$scope.pollFormData.sQuestion) {
                bAlert && $modal.alert(gettextCatalog.getString('Provide a question for your poll.'));
                return false;
            }

            if ($scope.pollGetAnswers().length < 2) {
                bAlert && $modal.alert(gettextCatalog.getString('You need to write at least 2 answers.'));
                return false;
            }

            return true;
        };

        $scope.pollGetForm();
    };
});