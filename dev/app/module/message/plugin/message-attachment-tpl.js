define(function() {

    var MessageAttachmentTpl = function() {

        this.proxy = {};
    }

    MessageAttachmentTpl.prototype.add = function(type, proxy) {

        if (typeof type == 'string') {
            this.proxy[type] = proxy;
        } else if (typeof type == 'object') {
            for (var i = 0; i < type.length; ++i) {
                this.proxy[type[i]] = proxy;
            }
        }
    }

    MessageAttachmentTpl.prototype.get = function(type) {

        if (this.proxy.hasOwnProperty(type)) {
            return this.proxy[type];
        }

        return '';
    }

    return new MessageAttachmentTpl();
});