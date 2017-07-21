define([
    'event/model/event',
    'event/controller/event-form',
    'moment'
], function(EventModel, EventFormController, Moment) {

    return function($scope, $injector, $state, $http2, $site, $modal, gettext, gettextCatalog, $viewer, $location) {

        $injector.invoke(EventFormController, this, {
            $scope: $scope
        });

        $scope.item = $.extend({}, EventModel, {
            iEventId: $state.params.id
        });

        if (typeof($state.current.modelType) != 'undefined') {
            angular.extend($scope.item, {
                sModelType: $state.current.modelType
            });
        }

        $scope.form = {};
        $scope.formApi = 'event/formedit';
        $scope.formApiData = {
            iEventId: $scope.item.getId(),
            sModelType: $scope.item.getType()
        };
        $scope.saveApi = 'event/edit';
        $scope.formData = {
            iEventId: $scope.item.getId(),
            sModelType: $scope.item.getType()
        };
        $scope.tmpData = {};

        $scope.updateFormData = function(data) {

            $scope.form = data;
            $.extend($scope.item, data);

            $scope.formData = angular.extend($scope.formData, {
                title: $scope.item.getTitle(),
                description: $scope.item.getDescription(),
                start_date: $scope.item.getStartTimeFormatted('YYYY-MM-DD'),
                start_time: $scope.item.getStartTimeFormatted('HH:mm:00'),
                end_date: $scope.item.getEndTimeFormatted('YYYY-MM-DD'),
                end_time: $scope.item.getEndTimeFormatted('HH:mm:00'),
                host: $scope.item.getHost(),
                location: $scope.item.getLocation(),
                category_id: $scope.item.getCategoryId(),
                auth_view: $scope.item.getPrivacy(),
                auth_comment: $scope.item.getCommentPrivacy()
            });

            if (ionic.Platform.isIOS()) {
                angular.extend($scope.tmpData, {
                    start_date: new Date($scope.formData.start_date),
                    start_time: new Date(($scope.formData.start_date + ' ' + $scope.formData.start_time).replace(/-/g, '/')),
                    end_date: new Date($scope.formData.end_date),
                    end_time: new Date(($scope.formData.end_date + ' ' + $scope.formData.end_time).replace(/-/g, '/'))
                });
            }
        };

        $scope.doSaveSuccess = function(data) {

            if (data.error_code) {
                return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
            }

            $modal.toast(data.message || gettextCatalog.getString('Event has been updated successfully'));

            var modelType = typeof(data.sModelType) != 'undefined' ? data.sModelType : 'event';
            $location.path('app/' + modelType + '/' + $scope.item.getId());
        };

        $scope.getForm();
    };
});