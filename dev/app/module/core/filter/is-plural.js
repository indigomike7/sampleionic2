define(function() {
    return function(gettextCatalog, gettextPlurals) {
        return function(input) {
            var currentLanguage = gettextCatalog.getCurrentLanguage();
            return gettextPlurals(currentLanguage, input);
        };
    };
});
