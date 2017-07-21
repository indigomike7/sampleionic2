define(function() {

    return function(gettext, gettextCatalog) {
        var Modal = function() {
            this.toast = function() {
                console.info.apply(console, arguments);
            };

            this.alert = function() {
                window.alert.apply(window, arguments);
            };

            this.confirm = function(str, cb, title, labels) {
                cb(window.confirm(str) ? 1 : 2);
                //cordova 3.3 change index from 1 NOT 0
            };

            this.prompt = function() {
                return window.prompt.apply(window, prompt);
            };

            this.beep = function() {
                console.info.apply(console, arguments);
            };

            this.vibrate = function() {
                console.info.apply(console, arguments);
            };
        };

        var modal = new Modal;

        document.addEventListener('deviceready', function() {
            modal.alert = function(message, completeCallback, title, buttonLabel) {
                var _title = (title || gettextCatalog.getString('Alert'));
                var _buttonLabel = (buttonLabel || gettextCatalog.getString('OK'));
                var _message = (message || '').toString();
                return navigator.notification.alert(_message, completeCallback, _title, _buttonLabel);
            };
            
            modal.confirm = function() {
                return navigator.notification.confirm.apply(navigator.notitication, arguments);
            };

            modal.prompt = function() {
                return navigator.notification.prompt.apply(navigator.notification, arguments);
            };

            modal.toast = function() {
                return window.plugins.toast.showShortBottom.apply(window.plugins.toast, arguments);
            };

            modal.beep = function() {
                return navigator.notification.beep.apply(navigator.notitication, arguments);
            };

            modal.vibrate = function() {
                return navigator.notification.vibrate.apply(navigator.notification, arguments);
            };
        });

        return modal;
    };
});
