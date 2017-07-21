define([
    'user/model/user'
], function(UserModel) {

    return function($viewer, gettext, gettextCatalog) {

        var $dislike = {};

        $dislike.getLastDisliked = function(obj) {

            if (!obj.getTotalDislike()) {
                return null;
            }

            var dislikedList = obj.getDislikedList().filter(function(user) {
                return user.iUserId != $viewer.get('iUserId');
            });

            var lastDisliked = dislikedList.length ? $.extend({}, UserModel, dislikedList[0]) : UserModel;

            return lastDisliked;
        };

        $dislike.getDislikeStat = function(obj) {

            if (!obj.getTotalDislike()) {
                return '';
            }

            var dislikeStat = '<span>' + (obj.isDisliked() ? gettextCatalog.getString('You') : $dislike.getLastDisliked(obj).getTitle()) + '</span>';
            if (obj.getTotalDislike() > 1) {
                dislikeStat += '<span class="dark">&nbsp;' + gettextCatalog.getString('and') + '</span>';
                dislikeStat += '<span>&nbsp;' + (obj.getTotalDislike() - 1) + '&nbsp;' + (obj.getTotalDislike() - 1 == 1 ? gettextCatalog.getString('other') : gettextCatalog.getString('others')) + '</span>';
            }
            dislikeStat += '<span class="dark">&nbsp;' + gettextCatalog.getString('disliked this') + '</span>';

            return dislikeStat;
        }

        return $dislike;
    };
});