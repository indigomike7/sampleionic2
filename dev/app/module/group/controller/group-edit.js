define([
    'group/model/group',
    'global/base/BaseController'
], function(GroupModel, Ctrl) {
    return function($scope, $state, $http2, $site, $injector, $location, $modal, gettext, gettextCatalog) {
        
        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });
        
        var iGroupId  = $state.params.iGroupId;
        
        $scope.dataReady = false;
        
        $scope.form = {};
        $scope.formData = {};
        $scope.item = $.extend({}, GroupModel, {
            iGroupId: iGroupId
        });
        
        $scope.initForm = function(){
            var sendData =  {
                iGroupId: iGroupId,
                fields: 'id,title,desc,category,bCanEdit,categoryOptions,auth,viewOptions,commentOptions,imgNormal',
            };
            
            $http2.post('groups/formedit', sendData)
            .success(function(data){
                if(data.error_code){
                    $modal.alert(data.error_message);
                }else{
                    $scope.form.typeOptions =  data.category_options;
                    $scope.form.commentOptions  =  data.comment_options;
                    $scope.form.viewOptions  =  data.view_options;

                    $.extend($scope.item, data);

                    $scope.formData.iGroupId = $scope.item.getId();
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

            $http2.post('groups/edit', $scope.formData)
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

            $modal.toast(data.message || gettextCatalog.getString('Group has been updated successfully'));
            $location.path('app/groups/' + data.iGroupId, true);
        };

        $scope.doSaveError = function() {

            console.error('save', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
        };

        $scope.isValidData = function(bAlert) {

            if (!$scope.formData.sTitle) {
                bAlert && $modal.alert(gettextCatalog.getString('Please enter group name.'));
                return false;
            }

            return true;
        };

        $scope.initForm();
        
        return $scope;
    };
});

