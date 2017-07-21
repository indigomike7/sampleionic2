define(function() {
    return {
        escapeHTML: function(str) {
            return $('<textarea />').text(str).html();
        },
        unescapeHTML: function(str) {
            return $('<textarea />').html(str).val();
        },
        parseExternalLink: function(str) {
            var patt = /<a([^>]+)href\s*="([^"]*)"([^>]*)>/ig;
            return str.replace(patt, '<a$1href-dir external-url="$2"$3>');
        },
        prepareIframeSrc: function(str) {
            var patt = /<iframe([^src]+)src="\/\//ig;
            return str.replace(patt, '<iframe$1src="http://');
        },
        prepareHTMLText: function(str) {
            if (!str) {
                return '';
            }
            str = this.parseExternalLink(str);
            str = this.prepareIframeSrc(str);
            return str;
        },
        getObjPropByPatt: function(object, pattern) {
            for (var key in object) {
                if (pattern.test(key)) {
                    return object[key];
                }
            }
            return false;
        },
        stripTags: function(str) {
            return (str || '').replace(/(<([^>]+)>)/ig, '');
        },
        parseHashTag: function(str) {
            var patt = /#(\w+)\b(?!;)/g;
            return (str || '').replace(patt, '<a href="#app/newsfeed/hashtag/' + '$1">#$1<\/a>');
        },
        isOnScreen: function($ele) {
            var $window = $(window);
            var docViewTop = $window.scrollTop();
            var docViewBottom = docViewTop + $window.height();
            var eleTop = $ele.offset().top;
            var eleBottom = eleTop + $ele.height();
            return ((docViewBottom >= eleBottom && eleBottom >= docViewTop) || (docViewBottom >= eleTop && eleTop >= docViewTop));
        },
        removeImgs: function(content) {
            return $('<div />').html(content || '').find('img').remove().end().html();
        },
        prepareUrl: function(str) {
            str = str || '';
            if (str.indexOf('://') == -1) {
                return 'http://' + str;
            }
            return str;
        },
        parseUserTag: function(str){
            var patt = /\[user=(\d+)\]([\w,\s]+)\[\/user\]/g;
            return (str || '').replace(patt, '<a href="#/app/user/$1">$2</a>');
        }
    }
});