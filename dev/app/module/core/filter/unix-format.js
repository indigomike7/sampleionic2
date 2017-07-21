define([
    'moment'
], function(Moment) {
    return function() {
        return function(input, format) {
            if (isNaN(parseInt(input))) {
                return '';
            }

            return Moment.unix(input).format(format || 'll');
        };
    };
});
