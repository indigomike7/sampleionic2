define([
    'global/base/Model',
    'global/helper'
], function(Model, Helper) {
    return Model.extend({
        idAttribute: 'iReviewId',
        sModelType: 'directory_review'
    });
});
