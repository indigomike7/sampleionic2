define([
	'activity/plugin/attachment-tpl',
	'text!tpl/location/location-attachment.html',
	'activity/plugin/headline'
], function(AttachmentTplPlugin, LocationAttachmentTpl, HeadlinePlugin) {

	HeadlinePlugin.add([
		'ynmobile_checkin',
	], function(feed, gettext, gettextCatalog) {

		if(feed.hasAttachment()) {

			return sprintf(gettextCatalog.getString('%1$s has checked in - at %2$s'), feed.getPosterLink(), feed.getAttachments()[0].sLocationName);
		}

		return feed.getPosterLink();
	});

	AttachmentTplPlugin.add(['ynmobile_map'], LocationAttachmentTpl);
	AttachmentTplPlugin.add(['ynfeed_map'], LocationAttachmentTpl); // support advanced feed location
});