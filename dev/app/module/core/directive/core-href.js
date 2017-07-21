define([
    'global/helper'
], function(Helper) {

    return function($location, $site) {

        var link = function(scope, element, attrs) {

            scope.url = attrs.url;
            scope.externalUrl = attrs.externalUrl;

            element.bind('click', function(e) {
				
				var href,ngclick;
				
				if(href = e.currentTarget.getAttribute('ng-url')){
					$site.debug > 2 && console.log('ng-url',href);
					window.location.href = href;
					return;
				}else if(ngclick =e.currentTarget.getAttribute('ng-click')){
					scope.$apply(ngclick);
					return ;
				}
				
                if ((!scope.url && !scope.externalUrl) 
                || $(e.currentTarget).has($(e.target).closest('a')).length
                || $(e.currentTarget).has($(e.target).closest('[href-dir]')).length 
                || $(e.currentTarget).has($(e.target).closest('[ng-click]')).length) {
                    return;
                }

                if (scope.externalUrl) {
                    var externalUrl = Helper.prepareUrl(scope.externalUrl);
                    return window.open(externalUrl, '_system', 'location=yes');
                }

                window.location.href = scope.url;
            });
        };

        return {
            restrict: 'A',
            link: link
        };
    };
});