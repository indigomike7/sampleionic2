define([
	'activity/plugin/attachment-tpl',
	'activity/plugin/headline',
	'text!tpl/marketplace/listing-attachment.html'
], function(AttachmentTplPlugin, HeadlinePlugin, ListingAttachmentTpl) {

	HeadlinePlugin.add([
        'advancedmarketplace',
        'marketplace'
    ], function(feed, gettext, gettextCatalog) {
        return sprintf(gettextCatalog.getString('%s created a listing.'), feed.getPosterLink());
    });

	AttachmentTplPlugin.add(['advancedmarketplace', 'marketplace'], ListingAttachmentTpl);
});