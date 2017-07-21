define([
    'ImgCache',
],function(){
    return function() {

        return {
            restrict: 'A',
            link: function (scope, el, attrs) {

                var setImg = function (type, el, src) {
                    ImgCache.getCachedFileURL(src, function (src, dest) {
                        var img_src = dest.fullPath.replace('/'.ImgCache.options.localCacheFolder, ImgCache.getCacheFolderURI());
                        if (type === 'bg') {
                            el.css({'background-image': 'url(' + img_src + ')'});
                        } else {
                            el.attr('src', img_src);
                        }
                    });
                };

                var loadImg = function (type, el, src) {
                    ImgCache.$promise.then(function () {
                        ImgCache.isCached(src, function (path, success) {
                            if (success) {
                                setImg(type, el, src);
                            } else {
                                ImgCache.cacheFile(src, function () {
                                    setImg(type, el, src);
                                });
                            }
                        });
                    });
                };

                attrs.$observe('icSrc', function (src) {
                    loadImg('src', el, src);
                });

                attrs.$observe('icBg', function (src) {
                    loadImg('bg', el, src);
                });
            }
        }
    };
});
