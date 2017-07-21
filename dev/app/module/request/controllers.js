define([
	'request/controller/request-item',
	'request/controller/request-list'
], function(RequestItemCtrl, RequestListCtrl) {

	angular.module('myapp.controllers')
		.controller('RequestItemCtrl', RequestItemCtrl)
		.controller('RequestListCtrl', RequestListCtrl);
});