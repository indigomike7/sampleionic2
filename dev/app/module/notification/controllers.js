define([
	'notification/controller/notification-item',
	'notification/controller/notification-list'
], function(NotificationItemCtrl, NotificationListCtrl) {

	angular.module('myapp.controllers')
		.controller('NotificationItemCtrl', NotificationItemCtrl)
		.controller('NotificationListCtrl', NotificationListCtrl);
});