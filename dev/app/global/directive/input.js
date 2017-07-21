define(
    function() {
        return function() {
            return {
                restrict: 'E',
                link: function(scope, element, attrs) {
                    if (attrs.type == 'text') {
                        scope.$watch(attrs.ngModel, function(newValue){
                            if (element.hasClass('ng-pristine')) {
                                element.val(($('<textarea />').html(newValue).val()));
                            }
                        });
                    }
                }
            };
        };
    });