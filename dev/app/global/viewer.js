define(function() {

    var viewer = {
        storage: {}
    };

    viewer.set = function(data) {

        this.storage = data || {};
    };

    viewer.reset = function() {

        this.storage = {};

        localStorage.setItem('viewer', JSON.stringify(this.storage));
    };

    viewer.update = function(data) {

        $.extend(this.storage, data);
    };

    viewer.get = function(key) {

        if (this.storage.iUserId) {
            return key ? this.storage[key] : this.storage;
        }

        var sLocalStorage = localStorage.getItem('viewer');
        if (sLocalStorage) {
            return key ? JSON.parse(sLocalStorage)[key] : JSON.parse(sLocalStorage);
        }

        return key ? undefined : {};
    };

    return viewer;
});