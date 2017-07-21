define(function() {
    return function($timeout) {
        return {
            restrict: 'C',
            link: function postLink(scope, element, attrs, controller, transcludeFn) {
                scope.scrollToActiveTab = function() {
                    var ngItemActive = element.find('.tab-item-active');
                    if (ngItemActive.length) {
                        var offsetLeft = ngItemActive.offset().left - element.offset().left + element.scrollLeft();
                        var scrollLeft = offsetLeft - ((element.width() - ngItemActive.width()) / 2);
                        if (scrollLeft > 0) {
                            element.scrollLeft(scrollLeft);
                        }
                    }
                }

                var itemActiveVisible = function() {
                    var ngItemActive = element.find('.tab-item-active');
                    return ngItemActive.length ? ngItemActive.is(':visible') : false;
                };

                scope.$watch(itemActiveVisible, function(newVal, oldVal) {
                    if (newVal) {
                        scope.scrollToActiveTab();
                    }
                });
            }
        }
    }
});