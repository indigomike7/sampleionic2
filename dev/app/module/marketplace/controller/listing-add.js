define([
    'marketplace/controller/listing-form'
], function(ListingFormCtrl) {

    return function($scope, $injector, $state, $http2, $site, $modal, gettext, gettextCatalog, $viewer, $location, $timeout) {

        $injector.invoke(ListingFormCtrl, this, {
            $scope: $scope
        });

        /**
         * Get form
         */
        $scope.formData = {
            iAutoSell: 1,
            sEmails: '',
            sPersonalMessage: ''
        };

        $scope.getForm = function() {

            $http2.post('marketplace/formadd').success($scope.doGetFormSuccess).error($scope.doGetFormError).
            finally(function() {});
        };

        $scope.doGetFormSuccess = function(data) {

            if (data.error_code) {
                $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                return $scope.goBack();
            }

            $scope.form = data;

            $scope.doPrepareFormData(data);

            $scope.dataReady = true;

            // bind change category event
            $(document).on('change', '.listing-form-category', $scope.onChangeCategory);
        };

        $scope.$on('$destroy', function() {

            $(document).off('change', '.listing-form-category', $scope.onChangeCategory);
        });

        $scope.doPrepareFormData = function(data) {

            $scope.formData = $.extend($scope.formData, {
                aCategoryId: [],
                iIsSell: '0',
                sCurrencyId: $scope.form.currency_options.length ? $scope.form.currency_options[0].currency_id : 0,
                iPrivacy: data.default_privacy_setting,
                iPrivacyComment: $scope.form.comment_options.length ? $scope.form.comment_options[0].sValue : 0
            });
        };

        /**
         * Save data
         */
        $scope.onSave = function() {

            if ($scope.isProcessing || !$scope.isValidData(true)) {
                return;
            }

            $scope.save();
        };

        $scope.save = function() {

            $scope.isProcessing = true;

            if (!$scope.form.country_child_options.length) {
                $scope.formData.iCountryChildId = 0;
            }

            $http2.post('marketplace/create', $scope.formData).success($scope.doSaveSuccess).error($scope.doSaveError).
            finally(function() {
                $scope.isProcessing = false;
            });
        };

        $scope.doSaveSuccess = function(data) {

            if (data.error_code) {
                return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
            }

            $modal.toast(data.message || gettextCatalog.getString('Listing successfully added.'));

            $location.path('app/listing/' + data.iListingId + '/edit/photo');
        };

        $scope.getForm();
    };
});