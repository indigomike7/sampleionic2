define([
    'global/helper',
    'marketplace/controller/listing-form',
    'marketplace/model/listing'
], function(Helper, ListingFormCtrl, ListingModel) {

    return function($scope, $injector, $state, $http2, $site, $modal, gettext, gettextCatalog, $viewer, $location, $timeout, $ionicScrollDelegate) {

        $injector.invoke(ListingFormCtrl, this, {
            $scope: $scope
        });

        $scope.sView = $state.current.contextView || 'info';

        /**
         * Get form
         */
        $scope.getForm = function() {

            var sendData = {
                iListingId: $state.params.id
            };

            $http2.post('marketplace/formedit', sendData).success($scope.doGetFormSuccess).error($scope.doGetFormError).
            finally(function() {});
        };

        $scope.doGetFormSuccess = function(data) {

            if (data.error_code) {
                $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
                return $scope.goBack();
            }

            $scope.form = data;

            $scope.doPrepareFormData();
            $scope.doPrepareSubCategories();
            $scope.doPrepareCountryChild();

            $scope.dataReady = true;

            // bind change category event
            $(document).on('change', '.listing-form-category', $scope.onChangeCategory);
        };

        $scope.$on('$destroy', function() {

            $(document).off('change', '.listing-form-category', $scope.onChangeCategory);
        });

        $scope.doPrepareFormData = function() {

            $scope.formData = $.extend($scope.formData, {
                iListingId: $state.params.id,
                sModelType: $scope.form.sModelType || 'marketplace',
                aCategoryId: $scope.form.aCategoriesId || [],
                sTitle: Helper.unescapeHTML($scope.form.sTitle || ''),
                sMiniDescription: Helper.unescapeHTML($scope.form.sMiniDescription || ''),
                sDescription: Helper.unescapeHTML($scope.form.sDescription || ''),
                sCurrencyId: $scope.form.sCurrencyId,
                sPrice: $scope.form.sPrice,
                iIsSell: $scope.form.bIsSell ? '1' : '0',
                sCountryIso: $scope.form.sCountryIso,
                iCountryChildId: $scope.form.iCountryChildId || 0,
                sCity: $scope.form.sCity,
                sPostalCode: $scope.form.sPostalCode,
                iPrivacy: $scope.form.iPrivacy || 0,
                iPrivacyComment: $scope.form.iPrivacyComment || 0,
                iAutoSell: $scope.form.bAutoSell ? '1' : '0'
            });
        };

        $scope.doPrepareSubCategories = function() {

            var aSelected = $scope.formData.aCategoryId;

            if (aSelected.length) {
                $timeout(function() {
                    var $target = angular.element('.listing-form-category');
                    $scope.appendSubCategories($target, aSelected);
                }, 300);
            }
        };

        $scope.doPrepareCountryChild = function() {

            if ($scope.formData.sCountryIso) {
                $scope.onChangeCountry();
            }
        };

        /**
         * Handle change view
         */
        $scope.onChangeView = function(sView) {

            $scope.sView = sView || 'info';

            $ionicScrollDelegate.$getByHandle('listing-edit-form').scrollTop();
        };

        /**
         * Handle upload photos
         */
        $scope.newPhotoItems = [];

        $scope.onAddPhoto = $scope._setting($scope, function() {

            return [{
                text: gettextCatalog.getString('Take Photo'),
                action: function() {
                    $scope.doAddPhoto('CAMERA');
                }
            }, {
                text: gettextCatalog.getString('Select From Gallery'),
                action: function() {
                    $scope.doAddPhoto('PHOTOLIBRARY');
                }
            }];
        });

        $scope.doAddPhoto = function(sourceType) {

            sourceType = sourceType || 'PHOTOLIBRARY';

            var getSuccess = function(fileURI) {
                $scope.doAddPhotoSuccess(fileURI);
            };

            var getFail = function(msg) {
                console.warn(msg);
                if (msg == 20) { // PERMISSION_DENIED_ERROR = 20
                    $modal.alert(gettextCatalog.getString('Illegal Access'));
                }
            };

            navigator.camera.getPicture(getSuccess, getFail, {
                quality: 50,
                destinationType: navigator.camera.DestinationType.FILE_URI,
                sourceType: Camera.PictureSourceType[sourceType],
                encodingType: Camera.EncodingType.JPEG,
                correctOrientation: true,
                targetWidth: $site.imgTargetSize,
                targetHeight: $site.imgTargetSize
            });
        };

        $scope.doAddPhotoSuccess = function(fileURI) {

            $scope.newPhotoItems.push({
                sImagePath: fileURI
            });

            $scope.$$phrase || $scope.$apply();
        };

        $scope.onRemoveNewPhotoItem = function($index) {

            $scope.newPhotoItems.splice($index, 1);
        };

        $scope.onRemovePhotoItem = function($index) {

            var sendData = {
                iImageId: $scope.form.aImages[$index].iImageId
            };

            var successCb = function(data) {

                if (data.error_code) {
                    return $modal.alert(data.error_message || gettextCatalog.getString('Can not delete photo. Please try again later.'));
                }

                $scope.form.aImages.splice($index, 1);
            };

            var errorCb = function() {

                console.warn('marketplace/photodelete', arguments);
                $modal.alert(gettextCatalog.getString('Can not delete photo. Please try again later.'));
            };

            $modal.confirm(gettextCatalog.getString('Are you sure?'), function(selected) {
                if (1 == selected) {
                    $http2.post('marketplace/photodelete', sendData).success(successCb).error(errorCb);
                }
            }, gettextCatalog.getString('Confirm'), [gettextCatalog.getString('OK'), gettextCatalog.getString('Cancel')]);
        };

        $scope.uploadPhotos = function() {

            $scope.uploadPhotoData = {
                success_count: 0,
                sendData: {
                    iListingId: $state.params.id
                }
            };

            $scope.uploadPhoto(0);
        };
        
        $scope.uploadPhoto = function(index) {

            var successCb = function(data) {

                if (data.error_code) {
                    $scope.uploadPhotoData.error_message = data.error_message || gettextCatalog.getString('Can not upload photo(s). Please try again later.');
                } else {
                    $scope.uploadPhotoData.success_count++; // will ignore errors if have any success photos
                }

                $scope.uploadPhotoComplete(index);
            };

            var errorCb = function(error) {

                console.warn('marketplace/photoupload', arguments);

                if (error.code == FileTransferError.ABORT_ERR) {
                    $modal.toast(gettextCatalog.getString('Canceled'));
                }

                $scope.uploadPhotoComplete(index);
            };

            $http2.upload('marketplace/photoupload', $scope.newPhotoItems[index].sImagePath, $scope.uploadPhotoData.sendData).then(successCb, errorCb);
        };
        
        $scope.uploadPhotoComplete = function(index) {

            if (index >= $scope.newPhotoItems.length - 1) {
                ($scope.uploadPhotoData.success_count > 0) ? $scope.save() : $modal.alert($scope.uploadPhotoData.error_message);
            } else {
                $scope.uploadPhoto(index + 1);
            }
        };

        /**
         * Save data
         */
        $scope.onSave = function() {

            if ($scope.isProcessing) {
                return;
            }

            if (!$scope.isValidData(true)) {
                return $scope.onChangeView('info');
            }

            $scope.newPhotoItems.length ? $scope.uploadPhotos() : $scope.save();
        };

        $scope.save = function() {

            $scope.isProcessing = true;

            if (!$scope.form.country_child_options.length) {
                $scope.formData.iCountryChildId = 0;
            }

            $http2.post('marketplace/edit', $scope.formData).success($scope.doSaveSuccess).error($scope.doSaveError).
            finally(function() {
                $scope.isProcessing = false;
            });
        };

        $scope.doSaveSuccess = function(data) {

            if (data.error_code) {
                return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
            }

            $modal.toast($scope.sView == 'info' ? 'Listing successfully updated.' : 'Successfully uploaded images.');

            $location.path('app/' + $scope.formData.sModelType + '/' + data.iListingId);
        };

        $scope.getForm();
    };
});