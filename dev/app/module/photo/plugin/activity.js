define([
	'activity/plugin/attachment-tpl',
	'activity/plugin/headline',
	'text!tpl/photo/photo-attachment.html'
], function(AttachmentTplPlugin, HeadlinePlugin, PhotoAttachmentTpl) {

	HeadlinePlugin.add([
		'photo',
		'advancedphoto',
	], function(feed, gettext, gettextCatalog) {

		if (feed.hasAttachment()) {
			if (!feed.hasParentUser()) {
				var aAttachments = feed.getAttachments();
				if (aAttachments.length > 1) {
					return sprintf(gettextCatalog.getString('%s shared a few photos'), feed.getPosterLink());
				} else {
					return sprintf(gettextCatalog.getString('%s shared a photo'), feed.getPosterLink());
				}
			} else {
				if (feed.getParentModuleId() == 'feed') {
					return sprintf(gettextCatalog.getString('%1$s &rarr; %2$s'), feed.getPosterLink(), feed.getParentLink(22));
				} else {
					return feed.getPosterLink();
				}
			}
		} else {
			return '';
		}
	});

	HeadlinePlugin.add([
		'album',
		'advancedphoto_album',
	], function(feed, gettext, gettextCatalog) {

		if (feed.hasAttachment()) {
			if (!feed.hasParentUser()) {
				var patt = /<a([^>]+)href\s*="([^"]*)"([^>]*)>/ig;
				var feedInfo = feed.sFeedInfo.replace(patt, '<a href="#/app/' + feed.sAlbumModelType + '/' + feed.iAlbumId + '">');
				return feed.getPosterLink() + ' ' + feedInfo;
			} else {
				if (feed.getParentModuleId() == 'feed') {
					return sprintf(gettextCatalog.getString('%1$s &rarr; %2$s'), feed.getPosterLink(), feed.getParentLink(22));
				} else {
					return feed.getPosterLink();
				}
			}
		} else {
			return '';
		}
	});

	AttachmentTplPlugin.add([
		'photo',
		'advancedphoto',
		'album_photo',
		'advalbum_photo',
		'event_photo',
		'ynevent_photo',
		'group_photo',
		'advgroup_photo'], PhotoAttachmentTpl);
});