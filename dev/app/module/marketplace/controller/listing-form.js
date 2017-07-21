define([
    'global/base/BaseController',
], function(BaseController) {

    return function($scope, $injector, $state, $http2, $site, $modal, gettext, gettextCatalog, $viewer, $location, $timeout) {

        $injector.invoke(BaseController, this, {
            $scope: $scope
        });

        /**
         * Get form
         */
        $scope.form = {};
        $scope.form.country_child_options = [];
        $scope.formData = {};

        $scope.doGetFormError = function() {

            console.warn('getForm', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
            $scope.goBack();
        };

        /**
         * Handle select categories
         */
        $scope.onChangeCategory = function(evt) {

            var $target = $(evt.currentTarget);

            var $subs_holder = $target.parent().parent().children('.listing-form-category-subs-holder');

            $subs_holder.empty();

            $scope.updateCategories();

            if ($target.val()) {
                $scope.appendSubCategories($target);
            }
        };

        $scope.updateCategories = function() {

            $scope.formData.aCategoryId = [];

            var $form_category = $('.listing-form-category');

            $form_category.each(function(index) {
                if ($(this).val()) {
                    $scope.formData.aCategoryId.push($(this).val());
                }
            });
        };

        $scope.appendSubCategories = function($target, aSelected) {

            var subCategoriesHtml = $scope.getSubCategoriesHtml($scope.form.category_options, $target.val(), aSelected);

            var $subs_holder = $target.parent().parent().children('.listing-form-category-subs-holder');

            $subs_holder.html(subCategoriesHtml);
        };

        $scope.getSubCategories = function(aCategory, iParentId) {

            var aSub = [];

            for (var i in aCategory) {
                if (aCategory[i].category_id == iParentId) {
                    return aCategory[i].sub;
                } else {
                    aSub = $scope.getSubCategories(aCategory[i].sub, iParentId);
                    if (aSub.length > 0) {
                        return aSub;
                    }
                }
            }

            return aSub;
        };

        $scope.getSubCategoriesHtml = function(aCategory, iParentId, aSelected) {

            aSelected = aSelected || [];

            var aSub = $scope.getSubCategories(aCategory, iParentId);

            if (aSub.length == 0 || !iParentId) {
                return '';
            }

            var selected = 0;

            var html = '<div class="input-select border-top"><select class="listing-form-category">';

            html += '<option value="">' + gettextCatalog.getString('Select Sub-Category') + '</option>';

            for (var i = 0; i < aSub.length; i++) {
                html += '<option value="' + aSub[i].category_id + '"';

                if (aSelected.indexOf(aSub[i].category_id) > -1) {
                    html += ' selected="selected"';
                    selected = aSub[i].category_id;
                }

                html += '>' + aSub[i].name + '</option>';
            }

            html += '</select></div>';

            html += '<div class="listing-form-category-subs-holder">' + $scope.getSubCategoriesHtml(aCategory, selected, aSelected) + '</div>';

            return html;
        };

        /**
         * Handle select country
         */
        $scope.onChangeCountry = function() {

            $scope.form.country_child_options = [];

            $scope.isLoadingCountryChilds = true;

            var sendData = {
                sCountryIso: $scope.formData.sCountryIso
            };

            var successCb = function(data) {

                if (data.error_code) {
                    return $modal.alert(data.error_message || gettextCatalog.getString('Can not load country childs'));
                }

                $scope.form.country_child_options = data || [];
            };

            var errorCb = function(arguments) {

                console.warn('core/getchilds', arguments);
                $modal.alert(gettextCatalog.getString('Can not load country childs'));
            };

            $http2.get('core/getchilds', sendData).success(function(data) {
                if ($scope.formData.sCountryIso == sendData.sCountryIso) {
                    successCb(data);
                }
            }).error(function() {
                if ($scope.formData.sCountryIso == sendData.sCountryIso) {
                    errorCb(arguments);
                }
            }).
            finally(function() {
                if ($scope.formData.sCountryIso == sendData.sCountryIso) {
                    $scope.isLoadingCountryChilds = false;
                }
            });
        };

        /**
         * Save data
         */
        $scope.doSaveError = function() {

            console.warn('save', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
        };

        $scope.isValidData = function(bAlert) {

            if (!$scope.formData.aCategoryId.length) {
                bAlert && $modal.alert(gettextCatalog.getString('Provide a category this listing will belong to.'));
                return false;
            }

            if (!$scope.formData.sTitle) {
                bAlert && $modal.alert(gettextCatalog.getString('Provide a name for this listing.'));
                return false;
            }

            if (!$scope.formData.sPrice) {
                bAlert && $modal.alert(gettextCatalog.getString('Provide a valid price.'));
                return false;
            }

            if (!$scope.formData.sCountryIso) {
                bAlert && $modal.alert(gettextCatalog.getString('Provide a location for this listing.'));
                return false;
            }

            return true;
        };
    };
});