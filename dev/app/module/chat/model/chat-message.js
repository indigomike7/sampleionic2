define([
    'global/base/Model',
    'global/helper',
    'global/viewer',
    'global/site',
    'moment'
], function(Model, Helper, Viewer, Site, Moment) {

    return Model.extend({
        idAttribute: 'time_stamp',
        sModelType: 'chat_message',
        getUsers: function() {
            return this.thread_id.split(':');
        },
        getSenderId: function() {
            return this.user.id || 0;
        },
        getReceiverId: function() {
            var aUsers = this.getUsers();
            $.each(aUsers, function(key, value) {
                if(value != Viewer.get('iUserId'))
                    return value;
            });
            return 0;
        },
        getSenderImageSrc: function() {
            var sImageSrc = this.avatar || '';
            return sImageSrc.match(/^http/ig) ? sImageSrc : (Site.siteUrl + sImageSrc);
        },
        getSenderUrl: function() {
            return '#/app/user/' + this.getSenderId();
        },
        getTimeFormatted: function() {
            return Moment(this.getTimestamp()).format('L hh:mmA');
        },
        getTimestamp: function() {
            return this.time_stamp * 1000 || this.time_stamp * 1000 || 0;
        },
        getTime: function() {
            return this.sTime || '';
        },
        getDate: function() {
            return this.sDate || '';
        },
        getMessage: function() {
            var regEx = /:([^:]+):/g;
            var aMatches = this.text.match(regEx);
            var sMessage = this.text;
            var aEmojis = [{"class":"twa twa-smile","key":":smile:"},{"class":"twa twa-simple-smile","key":":simple_smile:"},{"class":"twa twa-laughing","key":":laughing:"},{"class":"twa twa-blush","key":":blush:"},{"class":"twa twa-smiley","key":":smiley:"},{"class":"twa twa-relaxed","key":":relaxed:"},{"class":"twa twa-smirk","key":":smirk:"},{"class":"twa twa-heart-eyes","key":":heart_eyes:"},{"class":"twa twa-kissing-heart","key":":kissing_heart:"},{"class":"twa twa-kissing-closed-eyes","key":":kissing_closed_eyes:"},{"class":"twa twa-flushed","key":":flushed:"},{"class":"twa twa-relieved","key":":relieved:"},{"class":"twa twa-satisfied","key":":satisfied:"},{"class":"twa twa-grin","key":":grin:"},{"class":"twa twa-wink","key":":wink:"},{"class":"twa twa-stuck-out-tongue-winking-eye","key":":stuck_out_tongue_winking_eye:"},{"class":"twa twa-stuck-out-tongue-closed-eyes","key":":stuck_out_tongue_closed_eyes:"},{"class":"twa twa-grinning","key":":grinning:"},{"class":"twa twa-kissing","key":":kissing:"},{"class":"twa twa-kissing-smiling-eyes","key":":kissing_smiling_eyes:"},{"class":"twa twa-stuck-out-tongue","key":":stuck_out_tongue:"},{"class":"twa twa-sleeping","key":":sleeping:"},{"class":"twa twa-worried","key":":worried:"},{"class":"twa twa-frowning","key":":frowning:"},{"class":"twa twa-anguished","key":":anguished:"},{"class":"twa twa-open-mouth","key":":open_mouth:"},{"class":"twa twa-grimacing","key":":grimacing:"},{"class":"twa twa-confused","key":":confused:"},{"class":"twa twa-hushed","key":":hushed:"},{"class":"twa twa-expressionless","key":":expressionless:"},{"class":"twa twa-unamused","key":":unamused:"},{"class":"twa twa-sweat-smile","key":":sweat_smile:"},{"class":"twa twa-sweat","key":":sweat:"},{"class":"twa twa-weary","key":":weary:"},{"class":"twa twa-pensive","key":":pensive:"},{"class":"twa twa-disappointed","key":":disappointed:"},{"class":"twa twa-confounded","key":":confounded:"},{"class":"twa twa-fearful","key":":fearful:"},{"class":"twa twa-cold-sweat","key":":cold_sweat:"},{"class":"twa twa-persevere","key":":persevere:"},{"class":"twa twa-cry","key":":cry:"},{"class":"twa twa-sob","key":":sob:"},{"class":"twa twa-joy","key":":joy:"},{"class":"twa twa-astonished","key":":astonished:"},{"class":"twa twa-scream","key":":scream:"},{"class":"twa twa-tired-face","key":":tired_face:"},{"class":"twa twa-angry","key":":angry:"},{"class":"twa twa-rage","key":":rage:"},{"class":"twa twa-triumph","key":":triumph:"},{"class":"twa twa-sleepy","key":":sleepy:"},{"class":"twa twa-yum","key":":yum:"},{"class":"twa twa-mask","key":":mask:"},{"class":"twa twa-sunglasses","key":":sunglasses:"},{"class":"twa twa-dizzy-face","key":":dizzy_face:"},{"class":"twa twa-imp","key":":imp:"},{"class":"twa twa-smiling-imp","key":":smiling_imp:"},{"class":"twa twa-neutral-face","key":":neutral_face:"},{"class":"twa twa-no-mouth","key":":no_mouth:"},{"class":"twa twa-innocent","key":":innocent:"},{"class":"twa twa-alien","key":":alien:"},{"class":"twa twa-yellow-heart","key":":yellow_heart:"},{"class":"twa twa-blue-heart","key":":blue_heart:"},{"class":"twa twa-purple-heart","key":":purple_heart:"},{"class":"twa twa-heart","key":":heart:"},{"class":"twa twa-green-heart","key":":green_heart:"},{"class":"twa twa-broken-heart","key":":broken_heart:"},{"class":"twa twa-heartbeat","key":":heartbeat:"},{"class":"twa twa-heartpulse","key":":heartpulse:"},{"class":"twa twa-two-hearts","key":":two_hearts:"},{"class":"twa twa-revolving-hearts","key":":revolving_hearts:"},{"class":"twa twa-cupid","key":":cupid:"},{"class":"twa twa-sparkling-heart","key":":sparkling_heart:"},{"class":"twa twa-sparkles","key":":sparkles:"},{"class":"twa twa-star","key":":star:"},{"class":"twa twa-star2","key":":star2:"},{"class":"twa twa-dizzy","key":":dizzy:"},{"class":"twa twa-boom","key":":boom:"},{"class":"twa twa-anger","key":":anger:"},{"class":"twa twa-exclamation","key":":exclamation:"},{"class":"twa twa-question","key":":question:"},{"class":"twa twa-grey-exclamation","key":":grey_exclamation:"},{"class":"twa twa-grey-question","key":":grey_question:"},{"class":"twa twa-zzz","key":":zzz:"},{"class":"twa twa-dash","key":":dash:"},{"class":"twa twa-sweat-drops","key":":sweat_drops:"},{"class":"twa twa-notes","key":":notes:"},{"class":"twa twa-musical-note","key":":musical_note:"},{"class":"twa twa-fire","key":":fire:"},{"class":"twa twa-poop","key":":poop:"},{"class":"twa twa-thumbsup","key":":thumbsup:"},{"class":"twa twa-thumbsdown","key":":thumbsdown:"},{"class":"twa twa-ok-hand","key":":ok_hand:"},{"class":"twa twa-punch","key":":punch:"},{"class":"twa twa-fist","key":":fist:"},{"class":"twa twa-v","key":":v:"},{"class":"twa twa-wave","key":":wave:"},{"class":"twa twa-hand","key":":hand:"},{"class":"twa twa-raised-hand","key":":raised_hand:"},{"class":"twa twa-open-hands","key":":open_hands:"},{"class":"twa twa-point-up","key":":point_up:"},{"class":"twa twa-point-down","key":":point_down:"},{"class":"twa twa-point-left","key":":point_left:"},{"class":"twa twa-point-right","key":":point_right:"},{"class":"twa twa-raised-hands","key":":raised_hands:"},{"class":"twa twa-pray","key":":pray:"},{"class":"twa twa-point-up-2","key":":point_up_2:"},{"class":"twa twa-clap","key":":clap:"},{"class":"twa twa-muscle","key":":muscle:"},{"class":"twa twa-runner","key":":runner:"},{"class":"twa twa-couple","key":":couple:"},{"class":"twa twa-family","key":":family:"},{"class":"twa twa-two-men-holding-hands","key":":two_men_holding_hands:"},{"class":"twa twa-two-women-holding-hands","key":":two_women_holding_hands:"},{"class":"twa twa-dancer","key":":dancer:"},{"class":"twa twa-dancers","key":":dancers:"},{"class":"twa twa-ok-woman","key":":ok_woman:"},{"class":"twa twa-no-good","key":":no_good:"},{"class":"twa twa-information-desk-person","key":":information_desk_person:"},{"class":"twa twa-bride-with-veil","key":":bride_with_veil:"},{"class":"twa twa-person-with-pouting-face","key":":person_with_pouting_face:"},{"class":"twa twa-person-frowning","key":":person_frowning:"},{"class":"twa twa-bow","key":":bow:"},{"class":"twa twa-couplekiss","key":":couplekiss:"},{"class":"twa twa-couple-with-heart","key":":couple_with_heart:"},{"class":"twa twa-massage","key":":massage:"},{"class":"twa twa-haircut","key":":haircut:"},{"class":"twa twa-nail-care","key":":nail_care:"},{"class":"twa twa-boy","key":":boy:"},{"class":"twa twa-girl","key":":girl:"},{"class":"twa twa-woman","key":":woman:"},{"class":"twa twa-man","key":":man:"},{"class":"twa twa-baby","key":":baby:"},{"class":"twa twa-older-woman","key":":older_woman:"},{"class":"twa twa-older-man","key":":older_man:"},{"class":"twa twa-person-with-blond-hair","key":":person_with_blond_hair:"},{"class":"twa twa-man-with-gua-pi-mao","key":":man_with_gua_pi_mao:"},{"class":"twa twa-man-with-turban","key":":man_with_turban:"},{"class":"twa twa-construction-worker","key":":construction_worker:"},{"class":"twa twa-cop","key":":cop:"},{"class":"twa twa-angel","key":":angel:"},{"class":"twa twa-princess","key":":princess:"},{"class":"twa twa-smiley-cat","key":":smiley_cat:"},{"class":"twa twa-smile-cat","key":":smile_cat:"},{"class":"twa twa-heart-eyes-cat","key":":heart_eyes_cat:"},{"class":"twa twa-kissing-cat","key":":kissing_cat:"},{"class":"twa twa-smirk-cat","key":":smirk_cat:"},{"class":"twa twa-scream-cat","key":":scream_cat:"},{"class":"twa twa-crying-cat-face","key":":crying_cat_face:"},{"class":"twa twa-joy-cat","key":":joy_cat:"},{"class":"twa twa-pouting-cat","key":":pouting_cat:"},{"class":"twa twa-japanese-ogre","key":":japanese_ogre:"},{"class":"twa twa-japanese-goblin","key":":japanese_goblin:"},{"class":"twa twa-see-no-evil","key":":see_no_evil:"},{"class":"twa twa-hear-no-evil","key":":hear_no_evil:"},{"class":"twa twa-speak-no-evil","key":":speak_no_evil:"},{"class":"twa twa-guardsman","key":":guardsman:"},{"class":"twa twa-skull","key":":skull:"},{"class":"twa twa-feet","key":":feet:"},{"class":"twa twa-lips","key":":lips:"},{"class":"twa twa-kiss","key":":kiss:"},{"class":"twa twa-droplet","key":":droplet:"},{"class":"twa twa-ear","key":":ear:"},{"class":"twa twa-eyes","key":":eyes:"},{"class":"twa twa-nose","key":":nose:"},{"class":"twa twa-tongue","key":":tongue:"},{"class":"twa twa-love-letter","key":":love_letter:"},{"class":"twa twa-bust-in-silhouette","key":":bust_in_silhouette:"},{"class":"twa twa-busts-in-silhouette","key":":busts_in_silhouette:"},{"class":"twa twa-speech-balloon","key":":speech_balloon:"},{"class":"twa twa-thought-balloon","key":":thought_balloon:"}];
            aMatches && aMatches.forEach(function(key) {
                var emoji = $.grep(aEmojis, function(e){ return e.key == key; });
                emoji.length && (sMessage = sMessage.replace(key, '<i class="' + emoji[0].class +'"></i>'));
            });
            return sMessage = Helper.prepareHTMLText(sMessage || '');
        },
        getType: function() {
            return this.sType || this.type || 'text';
        },
        isViewer: function() {
            return this.getSenderId() == Viewer.get('iUserId');
        },
        getStatus: function() {
            return this.sStatus || '';
        },
        setStatus: function(status) {
            this.sStatus = status || '';
        },
        getData: function() {
            var data = this.data || '';
            return JSON.parse(data);
        },
        getFiles: function() {
            return this.files || [];
        },
        getAttachment: function() {
            return this.attachment_id || {};
        },
        hasAttachment: function() {
            if(typeof(this.attachment_id) == 'object')
                return true;
            return false;
        }
    });
});