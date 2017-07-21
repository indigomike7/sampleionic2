define([
    'event/controller/event-form',
    'moment'
], function(EventFormController, Moment) {

    return function($scope, $injector, $state, $http2, $site, $modal, gettext, gettextCatalog, $viewer, $location) {

        $injector.invoke(EventFormController, this, {
            $scope: $scope
        });

        $scope.form = {};
        $scope.formApi = 'event/formadd';
        $scope.saveApi = 'event/create';

        $scope.dataReady = false;

        $scope.formData = {
            auth_view: '0',
            auth_comment: '0',
            category_id: '',
            start_date: Moment(new Date().getTime()).format('YYYY-MM-DD'),
            start_time: Moment(new Date().getTime()).format('HH:mm:00'),
            end_date: Moment(new Date().getTime()).format('YYYY-MM-DD'),
            end_time: Moment(new Date().getTime() + 3600e3).format('HH:mm:00')
        };

        $scope.tmpData = {};

        // for creating events in pages
        if(typeof $state.params.sParentType != 'undefined'){
            $scope.formData.sModule = $state.params.sParentType;
            $scope.formData.iItemId =  $state.params.iParentId;
        }

        $scope.updateFormData = function(data) {
            $scope.form.categoryOptions = data.category_options;
            // event created in pages doesn't have categories
            if (!$scope.formData.sModule) {
                $scope.form.viewOptions = data.view_options;
                $scope.form.commentOptions = data.comment_options;
            }
            $scope.formData.auth_view=  data.default_privacy_setting;
            if($scope.form.commentOptions && $scope.form.commentOptions.length){
                $scope.formData.auth_comment  = $scope.form.commentOptions[0].sValue;
            }

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

            $modal.toast(data.message || gettextCatalog.getString('Event has been created successfully'));

            if (data.iEventId) {
                var modelType = typeof(data.sModelType) != 'undefined' ? data.sModelType : 'event';
                return $location.path('app/' + modelType + '/' + data.iEventId);
            }

            $location.path('app/events/my');
        };

        $scope.getForm();
    };
});
