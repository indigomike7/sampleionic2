define([
    'group/model/group',
    'global/base/BaseController'
], function(Model, Ctrl) {
    return function($scope, $state, $http2, $site, $injector, $location, $modal, gettext, gettextCatalog) {
        
        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });
        
        $scope.dataReady = false;
        
        
        $scope.item = $.extend({
        }, Model);
        $scope.form ={};
        $scope.formData = {
            sTitle: '',
            sDescription: '',
            iCategoryId: 0,
            auth_view: 'everyone',
            auth_comment: 'registered',
            iSearch: 1
        };
        
        $scope.initForm = function(){
            $http2.post('groups/formadd', {bPrivacyNoCustom: true},{cache: true})
            .success(function(data){
                if(data.error_code){
                    $modal.alert(data.error_message);
                    $scope.goBack();
                }else{
                    $scope.form.typeOptions =  data.category_options;
                    $scope.form.commentOptions  =  data.comment_options;
                    $scope.form.viewOptions  =  data.view_options;

                    if($scope.form.typeOptions.length){
                        $scope.formData.iTypeId =  $scope.form.typeOptions[0].type_id;
                    }
                    //retrieve sub-category from parent category list
                    $scope.onTypeChanged();

                    if($scope.form.commentOptions.length){
                        $scope.formData.auth_comment =  $scope.form.commentOptions[0].id;
                    }
                    if($scope.form.commentOptions.length){
                        $scope.formData.auth_view =  $scope.form.viewOptions[0].id;
                    }
                    
                    $scope.dataReady =true;
                }
            })
            .error(function(){
                
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

            $http2.post('groups/create', $scope.formData)
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