define(function() {
    return function($timeout) {
        return {
            restrict: 'EAC',
            link: function(scope, element, attrs) {
                ionic.on('native.keyboardshow', onShow, window);
                ionic.on('native.keyboardhide', onHide, window);

                //deprecated
                ionic.on('native.showkeyboard', onShow, window);
                ionic.on('native.hidekeyboard', onHide, window);

                var scrollCtrl;
                var bottomDistance;

                $timeout(function() {
                    if (attrs.disableScroll == 'true' && typeof(cordova) != 'undefined') {
                        cordova.plugins.Keyboard.disableScroll(true);
                    }

                    bottomDistance = getBottomDistance(element);

                    element.addClass('visible');
                }, 1000);

                function onShow(e) {
                    if (ionic.Platform.isAndroid() && !ionic.Platform.isFullScreen) {
                        return;
                    }

                    var keyboardHeight = e.keyboardHeight || e.detail.keyboardHeight;
                    element.css('bottom', (keyboardHeight - bottomDistance) + "px");
                    scrollCtrl = element.controller('$ionicScroll');
                    if (scrollCtrl) {
                        scrollCtrl.scrollView.__container.style.bottom = (keyboardHeight - bottomDistance) + keyboardAttachGetClientHeight(element[0]) + "px";
                    }
                }

                function onHide() {
                    if (ionic.Platform.isAndroid() && !ionic.Platform.isFullScreen) {
                        return;
                    }

                    element.css('bottom', '');
                    if (scrollCtrl) {
                        scrollCtrl.scrollView.__container.style.bottom = '';
                    }
                }

                scope.$on('$destroy', function() {
                    ionic.off('native.keyboardshow', onShow, window);
                    ionic.off('native.keyboardhide', onHide, window);

                    //deprecated
                    ionic.off('native.showkeyboard', onShow, window);
                    ionic.off('native.hidekeyboard', onHide, window);

                    if (attrs.disableScroll == 'true' && typeof(cordova) != 'undefined') {
                        cordova.plugins.Keyboard.disableScroll(false);
                    }
                });
            }
        };
    }

    function keyboardAttachGetClientHeight(element) {
        return element.clientHeight;
    }

    function getBottomDistance(element) {
        var scrollTop = $(window).scrollTop(),
            offset = $(element).offset(),
            top = offset.top - scrollTop,
            outerHeight = $(element).outerHeight();

        return $(window).height() - top - outerHeight;
    }
});
