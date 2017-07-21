define([
    'global/base/Model',
    'global/helper'
], function(Model, Helper) {
    return Model.extend({
        idAttribute: 'iPackageId',
        sModelType: 'directory_package'
    });
});
