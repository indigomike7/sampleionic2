define([
    'forum/controller/forum-post-form',
    'forum/model/thread',
    'text!tpl/forum/forum-poll-add.html'
], function(PostFormCtrl, ThreadModel) {

    return function($scope, $injector, $state, $http2, $site, $modal, gettext, gettextCatalog, $viewer, $location, $ionicModal) {

        $injector.invoke(PostFormCtrl, this, {
            $scope: $scope
        });

        /**
         * Get form
         */
        $scope.form = {};
        $scope.dataReady = false;

        /**
         * Handle attach files
         */
        $scope.attachedPoll = {};

        $scope.onRemoveAttachedPoll = function() {

            if ($scope.isProcessingRemovePoll) {
                return;
            }

            $modal.confirm(gettextCatalog.getString('Are you sure?'), function(selected) {
                if (1 == selected) {
                    $scope.doRemoveAttachedPoll();
                }
            }, gettextCatalog.getString('Confirm'), [gettextCatalog.getString('OK'), gettextCatalog.getString('Cancel')]);
        };

        $scope.doRemoveAttachedPoll = function() {

            $scope.isProcessingRemovePoll = true;

            var api = 'forum/threadpolldelete';

            var sendData = {
                iPollId: $scope.attachedPoll.iPollId
            };

            if ($scope.form.aThread) {
                sendData.iThreadId = $scope.form.aThread.getId();
            }

            var successCb = function(data) {

                if (data.error_code) {
                    return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                }

                $scope.attachedPoll = {};
            };

            var errorCb = function() {

                console.warn(api, arguments);
                $modal.alert(gettextCatalog.getString('Can not load data from server'));
            };

            $http2.post(api, sendData).success(successCb).error(errorCb).
            finally(function() {
                $scope.isProcessingRemovePoll = false;
            });
        };

        $scope.onAttachPoll = function() {

            if ($scope.isProcessingAttach || $scope.attachedPoll.iPollId) {
                return;
            };

            $scope.removePollAddModal();

            $scope.pollAddModal = $ionicModal.fromTemplate(require('text!tpl/forum/forum-poll-add.html'), {
                scope: $scope
            });

            $scope.pollAddModal.show();
            $scope.isProcessingAttach = true;
        };

        $scope.hidePollAddModal = function() {

            $scope.pollAddModal && $scope.pollAddModal.hide();
        };

        $scope.removePollAddModal = function() {

            $scope.pollAddModal && $scope.pollAddModal.remove();
        };

        $scope.$on('$destroy', function() {

            $scope.removePollAddModal();
        });

        $scope.$on('modal.hidden', function() {
            
            $scope.isProcessingAttach = false;
        });

        $scope.$on('modal.removed', function() {

            $scope.isProcessingAttach = false;
        });

        $scope.onPollFormError = function() {

            $scope.removePollAddModal();
        };

        $scope.onSavePollSuccess = function(aPoll) {

            $scope.attachedPoll = aPoll;
            $scope.removePollAddModal();
        };
    };
});