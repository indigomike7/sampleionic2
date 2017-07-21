define(function() {
var IonicModule = angular.module('ionic'),
  extend = angular.extend,
  forEach = angular.forEach,
  isDefined = angular.isDefined,
  isNumber = angular.isNumber,
  isString = angular.isString,
  jqLite = angular.element,
  noop = angular.noop;

/*
 * Override ionicPopover
 */
IonicModule
.factory('$ionicPopover', ['$ionicModal', '$ionicPosition', '$document', '$window',
function($ionicModal, $ionicPosition, $document, $window) {

  var POPOVER_BODY_PADDING = 0;

  var POPOVER_OPTIONS = {
    viewType: 'popover',
    hideDelay: 1,
    animation: 'none',
    positionView: positionView
  };

  function positionView(target, popoverEle) {
    var targetEle = jqLite(target.target || target);
    var buttonOffset = $ionicPosition.offset(targetEle);
    var popoverWidth = popoverEle.prop('offsetWidth');
    var popoverHeight = popoverEle.prop('offsetHeight');
    // Use innerWidth and innerHeight, because clientWidth and clientHeight
    // doesn't work consistently for body on all platforms
    var bodyWidth = $window.innerWidth;
    var bodyHeight = $window.innerHeight;

    var popoverCSS = {
      left: buttonOffset.left + buttonOffset.width / 2 - popoverWidth / 2
    };
    var arrowEle = jqLite(popoverEle[0].querySelector('.popover-arrow'));

    if (popoverCSS.left < POPOVER_BODY_PADDING) {
      popoverCSS.left = POPOVER_BODY_PADDING;
    } else if (popoverCSS.left + popoverWidth + POPOVER_BODY_PADDING > bodyWidth) {
      popoverCSS.left = bodyWidth - popoverWidth - POPOVER_BODY_PADDING;
    }

    // If the popover when popped down stretches past bottom of screen,
    // make it pop up if there's room above
    if (buttonOffset.top + buttonOffset.height + popoverHeight > bodyHeight &&
        buttonOffset.top - popoverHeight > 0) {
      popoverCSS.top = buttonOffset.top - popoverHeight;
      popoverEle.addClass('popover-bottom');
    } else {
      popoverCSS.top = buttonOffset.top + buttonOffset.height;
      popoverEle.removeClass('popover-bottom');
    }

    arrowEle.css({
      left: buttonOffset.left + buttonOffset.width / 2 -
        arrowEle.prop('offsetWidth') / 2 - popoverCSS.left + 'px'
    });

    popoverEle.css({
      top: popoverCSS.top + 'px',
      left: popoverCSS.left + 'px',
      marginLeft: '0',
      opacity: '1'
    });

  }

  /**
   * @ngdoc controller
   * @name ionicPopover
   * @module ionic
   * @description
   * Instantiated by the {@link ionic.service:$ionicPopover} service.
   *
   * Be sure to call [remove()](#remove) when you are done with each popover
   * to clean it up and avoid memory leaks.
   *
   * Note: a popover will broadcast 'popover.shown', 'popover.hidden', and 'popover.removed' events from its originating
   * scope, passing in itself as an event argument. Both the popover.removed and popover.hidden events are
   * called when the popover is removed.
   */

  /**
   * @ngdoc method
   * @name ionicPopover#initialize
   * @description Creates a new popover controller instance.
   * @param {object} options An options object with the following properties:
   *  - `{object=}` `scope` The scope to be a child of.
   *    Default: creates a child of $rootScope.
   *  - `{boolean=}` `focusFirstInput` Whether to autofocus the first input of
   *    the popover when shown.  Default: false.
   *  - `{boolean=}` `backdropClickToClose` Whether to close the popover on clicking the backdrop.
   *    Default: true.
   *  - `{boolean=}` `hardwareBackButtonClose` Whether the popover can be closed using the hardware
   *    back button on Android and similar devices.  Default: true.
   */

  /**
   * @ngdoc method
   * @name ionicPopover#show
   * @description Show this popover instance.
   * @param {$event} $event The $event or target element which the popover should align
   * itself next to.
   * @returns {promise} A promise which is resolved when the popover is finished animating in.
   */

  /**
   * @ngdoc method
   * @name ionicPopover#hide
   * @description Hide this popover instance.
   * @returns {promise} A promise which is resolved when the popover is finished animating out.
   */

  /**
   * @ngdoc method
   * @name ionicPopover#remove
   * @description Remove this popover instance from the DOM and clean up.
   * @returns {promise} A promise which is resolved when the popover is finished animating out.
   */

  /**
   * @ngdoc method
   * @name ionicPopover#isShown
   * @returns boolean Whether this popover is currently shown.
   */

  return {
    /**
     * @ngdoc method
     * @name $ionicPopover#fromTemplate
     * @param {string} templateString The template string to use as the popovers's
     * content.
     * @param {object} options Options to be passed to the initialize method.
     * @returns {object} An instance of an {@link ionic.controller:ionicPopover}
     * controller (ionicPopover is built on top of $ionicPopover).
     */
    fromTemplate: function(templateString, options) {
      return $ionicModal.fromTemplate(templateString, ionic.Utils.extend(POPOVER_OPTIONS, options || {}));
    },
    /**
     * @ngdoc method
     * @name $ionicPopover#fromTemplateUrl
     * @param {string} templateUrl The url to load the template from.
     * @param {object} options Options to be passed to the initialize method.
     * @returns {promise} A promise that will be resolved with an instance of
     * an {@link ionic.controller:ionicPopover} controller (ionicPopover is built on top of $ionicPopover).
     */
    fromTemplateUrl: function(url, options) {
      return $ionicModal.fromTemplateUrl(url, ionic.Utils.extend(POPOVER_OPTIONS, options || {}));
    }
  };

}]);
});