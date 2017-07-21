define([
    'global/base/Model'
],function(Model){
    return Model.extend({
        idAttribute: 'iAlbumId',
        sModelType: 'album',
        bCanView: true, 
        bCanShare: false,
        getTotalPhoto: function(){
            return this.iTotalPhoto || 0;
        },
        getUrl: function(){
            return '#/app/album/'  + this.iAlbumId;
        },
        getPhotoDetailUrl: function(){
            if(this.aSamplePhotos.length){
                return '#/app/photos/' + this.sModelType  + '/' + this.iAlbumId +'/'+ this.aSamplePhotos[0].iPhotoId;    
            }
            return '';
        }
    });
});