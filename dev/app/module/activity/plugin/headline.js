define(function() {

    var Headline = function() {

        this.proxy = {};
    };

    Headline.prototype.add = function(type, proxy) {

        if (typeof type == 'string') {
            this.proxy[type] = proxy;
        } else if (typeof type == 'object') {
            for (var i = 0; i < type.length; ++i) {
                this.proxy[type[i]] = proxy;
            }
        }
    };

    Headline.prototype.get = function(feed, gettext, gettextCatalog, $state) {

        var type = feed.getActionType();

        if (this.proxy.hasOwnProperty(type)) {
            return this.proxy[type](feed, gettext, gettextCatalog, $state);
        }

        return feed.getPosterLink();
    };

    return new Headline();
});
