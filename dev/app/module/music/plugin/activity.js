define([
    'activity/plugin/attachment-tpl',
    'activity/plugin/headline',
    'text!tpl/music/music-playlist-attachment.html',
    'text!tpl/music/music-song-attachment.html'
], function(AttachmentTplPlugin, HeadlinePlugin) {

    HeadlinePlugin.add([
        'musicsharing_album',
    ], function(feed, gettext, gettextCatalog) {
        return sprintf(gettextCatalog.getString('%s created a new album'), feed.getPosterLink(), feed.getItemLink());
    });


    HeadlinePlugin.add([
        'music_song',
        'music_album'
    ], function(feed, gettext, gettextCatalog) {
        return sprintf(gettextCatalog.getString('%s shared a song'), feed.getPosterLink());
    });

    AttachmentTplPlugin.add(['musicsharing_album'], require('text!tpl/music/music-playlist-attachment.html'));
    AttachmentTplPlugin.add(['musicsharing_song'], require('text!tpl/music/music-song-attachment.html'));

    AttachmentTplPlugin.add(['music', 'music_album', 'music_song'], require('text!tpl/music/music-song-attachment.html'));
});