define([
    'page/model/page',
    'global/base/BaseController'
], function(PageModel, Ctrl) {
    return function($scope, $state, $http2, $site, $injector, $location, $modal, gettext, gettextCatalog) {
        
        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });
        
        var iPageId  = $state.params.iPageId;
        
        $scope.dataReady = false;
        
        $scope.form = {};
        $scope.formData = {};
        $scope.item = $.extend({}, PageModel, {
            iPageId: iPageId
        });
        
        $scope.initForm = function(){
            var sendData =  {
                iPageId: iPageId,
                fields: 'id,title,desc,category,bCanEdit,categoryOptions,auth,viewOptions,commentOptions,imgNormal',
            };
            
            $http2.post('pages/formedit', sendData)
            .success(function(data){
                if(data.error_code){
                    $modal.alert(data.error_message);
                }else{
                    $scope.form.typeOptions =  data.category_options;
                    $scope.form.commentOptions  =  data.comment_options;
                    $scope.form.viewOptions  =  data.view_options;

                    $.extend($scope.item, data);

                    $scope.formData.iPageId = $scope.item.getId();
                    $scope.formData.sTitle  = $scope.item.getTitle();
                    $scope.formData.iTypeId = $scope.item.iTypeId || 0;
                    $scope.formData.iCategoryId = $scope.item.iCategoryId || 0;
                    $scope.formData.sInfo = $scope.item.getDescription();
                    //$scope.formData.auth_view  = data.auth.view;
                    //$scope.formData.auth_comment = data.auth.comment;
                    $scope.onTypeChanged();
                    $scope.dataReady =true;
                }
            })
            .error(function(){
                
            })
            .finally(function(){
                
            });
        };

        $scope.onTypeChanged = function() {
            // update sub categories
            var currentParentCategory = $scope.form.typeOptions.filter(function(obj){
                return obj.type_id == $scope.formData.iTypeId;
            });
            $scope.form.categoryOptions = currentParentCategory[0].sub_categories;
        };
        
        $scope.onSave = function() {

            if ($scope.isProcessing) {
                return;
            }

            if (!$scope.isValidData(true)) {
                return;
            }

            if ($scope.formData.photoPath) {
                $scope.doSaveWithPhoto();
            } else {
                $scope.save();
            }
        };

        $scope.save = function() {

            $scope.isProcessing = true;

            $http2.post('pages/edit', $scope.formData)
            .success($scope.doSaveSuccess)
            .error($scope.doSaveError)
            .finally(function() {
                $scope.isProcessing = false;
            });
        };

        $scope.doSaveSuccess = function(data) {

            if (data.error_code) {
                return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
            }

            $modal.toast(data.message || gettextCatalog.getString('Page has been updated successfully'));
            $location.path('app/pages/' + data.iPageId, true);
        };

        $scope.doSaveError = function() {

            console.error('save', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
        };

        $scope.isValidData = function(bAlert) {

            if (!$scope.formData.sTitle) {
                bAlert && $modal.alert(gettextCatalog.getString('Please enter page name.'));
                return false;
            }

            return true;
        };

        $scope.initForm();
        
        return $scope;
    };
});

