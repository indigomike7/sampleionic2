define([
    'global/base/Model',
    'global/helper'
], function(Model, Helper) {
    return Model.extend({
        idAttribute: 'iBusinessId',
        sModelType: 'directory',
        hasContact: function() {
            if ((this.aWebAddress || []).length || this.sEmail || (this.aPhone || []).length || (this.aFax || []).length) {
                return true;
            }

            return false;
        }
    });
});
