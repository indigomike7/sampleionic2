define(function() {
    return function() {
        return function(input, total) {
            input = [];
            total = parseInt(total);
            for (var i = 0; i < total; i++) {
                input.push(i);
            };
            return input;
        };
    };
});
