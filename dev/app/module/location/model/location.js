define([
    'global/base/Model'
], function(Model) {

    return Model.extend({
        sModelType: 'location',
        getTitle: function() {
            return this.name || '';
        },
        getDescription: function() {
            return this.vicinity || '';
        },
        getIconSrc: function() {
            return this.icon || '';
        },
        getCoords: function() {
            return this.geometry.location || {lat: 0, lng: 0};
        },
        getImageSrc: function(z, w, h) {
            z = z || 14;
            w = w || 100;
            h = h || 100;
            return 'http://maps.googleapis.com/maps/api/staticmap?center=' + this.getCoords().lat + ',' + this.getCoords().lng + '&zoom=' + z + '&size=' + w + 'x' + h +'&maptype=roadmap&markers=color:red%7C' + this.getCoords().lat + ',' + this.getCoords().lng + '&sensor=false';
        }
    });
});