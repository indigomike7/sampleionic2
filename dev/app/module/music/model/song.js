define([
    'global/viewer',
    'global/base/Model',
    'global/site',
], function(Viewer, Model, Site, Moment) {
    return Model.extend({
        idAttribute: 'iSongId',
        sModelType: 'music_song',
        bShowRate: false,
        getUrl: function() {
            var url = '#/app/' + this.getType() + '/' + this.getId();
            if (this.sParentType) {
                url += '/' + this.sParentType + '/' + this.iParentId;
            }
            return url;
        },
        getSongPath: function() {
            var sSongPath = this.sSongPath || '';
            sSongPath = sSongPath.replace("index.php", "PF.Base");
            return sSongPath;
        },
        getOrderNumber: function() {
            return parseInt(this.iOrdering || 0, 10) + 1;
        },
        getAlbumId: function() {
            return parseInt(this.iAlbumId) || 0;
        },
        getAlbumTitle: function() {
            return this.sAlbumName || '';
        },
        getAlbumType: function() {
            return this.sAlbumModelType || 'music_album';
        },
        getAlbumUrl: function() {
            var url = '#/app/' + this.getAlbumType() + '/' + this.getAlbumId();
            if (this.sParentType) {
                url += '/' + this.sParentType + '/' + this.iParentId;
            }
            return url;
        },
        getAlbumImageSrc: function() {
            return this.sAlbumImage || '';
        },
        getPlayCount: function() {
            return this.iTotalPlay || 0;
        },
        getGenre: function() {
            return this.sGenre || '';
        }
    });
});