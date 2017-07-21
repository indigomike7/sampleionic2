define([
    'global/viewer',
    'global/base/Model',
    'global/site',
], function(Viewer, Model, Site, Moment) {
    return Model.extend({
        idAttribute: 'iAlbumId',
        sModelType: 'music_album',
        bShowRate: true,
        getTrackCount: function() {
            return this.iTotalSong || this.iTotalTrack || 0;
        },
        getPlayCount: function() {
            return this.iTotalPlay || 0;
        },
        getTitle: function() {
            return this.sName || this.sAlbumName || '';
        },
        getImageSrc: function() {
            return this.sImagePath || '';
        }
    });
});