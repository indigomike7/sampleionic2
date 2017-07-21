define([
    'angular',
    'myapp',
    'directory/controllers',
    'directory/directives',
    'directory/plugin/activity',
    'text!tpl/directory/directory-activity.html',
    'text!tpl/directory/directory-all.html',
    'text!tpl/directory/directory-claim.html',
    'text!tpl/directory/directory-detail.html',
    'text!tpl/directory/directory-faqs.html',
    'text!tpl/directory/directory-favourite.html',
    'text!tpl/directory/directory-follow.html',
    'text!tpl/directory/directory-followers.html',
    'text!tpl/directory/directory-home.html',
    'text!tpl/directory/directory-my.html',
    'text!tpl/directory/directory-add-step1.html',
    'text!tpl/directory/directory-add-step2.html',
    'text!tpl/directory/directory-edit.html',
    'text!tpl/directory/directory-edit-cover.html',
    'text!tpl/directory/directory-events.html',
    'text!tpl/directory/directory-detail-photo.html',
    'text!tpl/directory/directory-upload-photo.html',
    'text!tpl/directory/directory-members.html',
    'text!tpl/directory/directory-music.html',
    'text!tpl/directory/directory-overview.html',
    'text!tpl/directory/directory-reviews.html',
    'text!tpl/directory/directory-reviews-add.html',
    'text!tpl/directory/directory-review-edit.html',
    'text!tpl/directory/directory-detail-video.html',
    'text!tpl/directory/directory-detail-videochannel.html',
    'text!tpl/directory/directory-discussion.html',
    'text!tpl/directory/directory-post-list.html',
    'text!tpl/directory/directory-topic-add.html',
    'text!tpl/directory/directory-topic-detail.html',
    'text!tpl/directory/directory-topic-edit-post.html',
    'text!tpl/directory/directory-topic-list.html',
    'text!tpl/directory/directory-topic-quote-post.html',
    'text!tpl/directory/directory-topic-reply.html',
], function(angular, myapp) {
    angular.module('myapp').config(function($stateProvider, $urlRouterProvider, gettext) {
        $stateProvider.state('app.directory', {
            module: 'directory',
            url: '/directory',
            cache: false,
            history: {
                title: gettext('Businesses'),
                isRoot: true
            },
            views: {
                'menuContent': {
                    template: require('text!tpl/directory/directory-home.html'),
                    controller: 'DirectoryHomeCtrl'
                },
                'tabContent@app.directory': {
                    template: require('text!tpl/directory/directory-all.html'),
                    controller: 'DirectoryAllCtrl'
                }
            }
        }).state('app.directoryMy', {
            module: 'directory',
            url: '/directory/my',
            cache: false,
            history: {
                title: gettext('Businesses'),
                isRoot: true
            },
            views: {
                'menuContent': {
                    template: require('text!tpl/directory/directory-home.html'),
                    controller: 'DirectoryHomeCtrl'
                },
                'tabContent@app.directoryMy': {
                    template: require('text!tpl/directory/directory-my.html'),
                    controller: 'DirectoryMyCtrl'
                }
            }
        }).state('app.directoryClaim', {
            module: 'directory',
            url: '/directory/claim',
            cache: false,
            history: {
                title: gettext('Businesses'),
                isRoot: true
            },
            views: {
                'menuContent': {
                    template: require('text!tpl/directory/directory-home.html'),
                    controller: 'DirectoryHomeCtrl'
                },
                'tabContent@app.directoryClaim': {
                    template: require('text!tpl/directory/directory-claim.html'),
                    controller: 'DirectoryClaimCtrl'
                }
            }
        }).state('app.directoryFavourite', {
            module: 'directory',
            url: '/directory/favourite',
            cache: false,
            history: {
                title: gettext('Businesses'),
                isRoot: true
            },
            views: {
                'menuContent': {
                    template: require('text!tpl/directory/directory-home.html'),
                    controller: 'DirectoryHomeCtrl'
                },
                'tabContent@app.directoryFavourite': {
                    template: require('text!tpl/directory/directory-favourite.html'),
                    controller: 'DirectoryFavouriteCtrl'
                }
            }
        }).state('app.directoryFollow', {
            module: 'directory',
            url: '/directory/follow',
            cache: false,
            history: {
                title: gettext('Businesses'),
                isRoot: true
            },
            views: {
                'menuContent': {
                    template: require('text!tpl/directory/directory-home.html'),
                    controller: 'DirectoryHomeCtrl'
                },
                'tabContent@app.directoryFollow': {
                    template: require('text!tpl/directory/directory-follow.html'),
                    controller: 'DirectoryFollowCtrl'
                }
            }
        }).state('app.directoryFaqs', {
            module: 'directory',
            url: '/directory/faqs',
            cache: false,
            history: {
                title: gettext('Businesses'),
                isRoot: true
            },
            views: {
                'menuContent': {
                    template: require('text!tpl/directory/directory-home.html'),
                    controller: 'DirectoryHomeCtrl'
                },
                'tabContent@app.directoryFaqs': {
                    template: require('text!tpl/directory/directory-faqs.html'),
                    controller: 'DirectoryFaqsCtrl'
                }
            }
        }).state('app.directoryAddStep1', {
            module: 'directory',
            url: '/directory/add',
            cache: false,
            history: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/directory/directory-add-step1.html'),
                    controller: 'DirectoryAddStep1Ctrl'
                }
            }
        }).state('app.directoryAddStep2', {
            module: 'directory',
            url: '/directory/add/:sPurpose/:iPackageId',
            cache: false,
            history: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/directory/directory-add-step2.html'),
                    controller: 'DirectoryAddStep2Ctrl'
                }
            }
        }).state('app.directoryEdit', {
            module: 'directory',
            url: '/directory/edit/:iBusinessId',
            cache: false,
            history: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/directory/directory-edit.html'),
                    controller: 'DirectoryEditCtrl'
                }
            }
        }).state('app.directoryEditCover', {
            module: 'directory',
            url: '/directory/edit/:iBusinessId/cover',
            cache: false,
            history: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/directory/directory-edit-cover.html'),
                    controller: 'DirectoryEditCoverCtrl'
                }
            }
        }).state('app.directoryDetailPhoto', {
            module: 'directory',
            url: '/directory/business/:id/photos',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/directory/directory-detail.html'),
                    controller: 'DirectoryDetailCtrl'
                },
                'tabContent@app.directoryDetailPhoto': {
                    template: require('text!tpl/directory/directory-detail-photo.html'),
                    controller: 'DirectoryDetailPhotoCtrl'
                }
            }
        }).state('app.directoryUploadPhoto', {
            module: 'directory',
            url: '/directory/business/:id/upload_photo',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/directory/directory-upload-photo.html'),
                    controller: 'DirectoryUploadPhotoCtrl'
                }
            }
        }).state('app.directoryDetailVideo', {
            module: 'directory',
            url: '/directory/business/:id/videos',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/directory/directory-detail.html'),
                    controller: 'DirectoryDetailCtrl'
                },
                'tabContent@app.directoryDetailVideo': {
                    template: require('text!tpl/directory/directory-detail-video.html'),
                    controller: 'DirectoryDetailVideoCtrl'
                }
            }
        }).state('app.directoryDetailVideochannel', {
            module: 'directory',
            url: '/directory/business/:id/videochannel',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/directory/directory-detail.html'),
                    controller: 'DirectoryDetailCtrl'
                },
                'tabContent@app.directoryDetailVideochannel': {
                    template: require('text!tpl/directory/directory-detail-videochannel.html'),
                    controller: 'DirectoryDetailVideochannelCtrl'
                }
            }
        }).state('app.directoryBusinessId', {
            module: 'directory',
            url: '/directory/business/:id',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/directory/directory-detail.html'),
                    controller: 'DirectoryDetailCtrl'
                },
                'tabContent@app.directoryBusinessId': {
                    template: require('text!tpl/directory/directory-overview.html'),
                    controller: 'DirectoryOverviewCtrl'
                }
            }
        }).state('app.directoryBusinessDetail', {
            module: 'directory',
            url: '/directory/:id',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/directory/directory-detail.html'),
                    controller: 'DirectoryDetailCtrl'
                },
                'tabContent@app.directoryBusinessDetail': {
                    template: require('text!tpl/directory/directory-overview.html'),
                    controller: 'DirectoryOverviewCtrl'
                }
            }
        }).state('app.directoryBusinessIdActivity', {
            module: 'directory',
            url: '/directory/business/:id/activity',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/directory/directory-detail.html'),
                    controller: 'DirectoryDetailCtrl'
                },
                'tabContent@app.directoryBusinessIdActivity': {
                    template: require('text!tpl/directory/directory-activity.html'),
                    controller: 'DirectoryActivityCtrl'
                }
            }
        }).state('app.directoryBusinessIdReviews', {
            module: 'directory',
            url: '/directory/business/:id/reviews',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/directory/directory-detail.html'),
                    controller: 'DirectoryDetailCtrl'
                },
                'tabContent@app.directoryBusinessIdReviews': {
                    template: require('text!tpl/directory/directory-reviews.html'),
                    controller: 'DirectoryReviewsCtrl'
                }
            }
        }).state('app.directoryBusinessIdMembers', {
            module: 'directory',
            url: '/directory/business/:id/members',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/directory/directory-detail.html'),
                    controller: 'DirectoryDetailCtrl'
                },
                'tabContent@app.directoryBusinessIdMembers': {
                    template: require('text!tpl/directory/directory-members.html'),
                    controller: 'DirectoryMembersCtrl'
                }
            }
        }).state('app.directoryBusinessIdFollowers', {
            module: 'directory',
            url: '/directory/business/:id/followers',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/directory/directory-detail.html'),
                    controller: 'DirectoryDetailCtrl'
                },
                'tabContent@app.directoryBusinessIdFollowers': {
                    template: require('text!tpl/directory/directory-followers.html'),
                    controller: 'DirectoryFollowersCtrl'
                }
            }
        }).state('app.directoryBusinessIdReviewsAdd', {
            module: 'directory',
            url: '/directory/business/:id/reviews/add',
            cache: false,
            history: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/directory/directory-reviews-add.html'),
                    controller: 'DirectoryReviewsAddCtrl'
                }
            }
        }).state('app.directoryReviewReviewIdEdit', {
            module: 'directory',
            url: '/directory/review/:reviewId/edit',
            cache: false,
            history: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/directory/directory-review-edit.html'),
                    controller: 'DirectoryReviewEditCtrl'
                }
            }
        }).state('app.directoryDetailDiscussion', {
            module: 'directory',
            url: '/directory/business/:id/discussions',
            cache: false,
            views: {
                'menuContent':{
                    template: require('text!tpl/directory/directory-detail.html'),
                    controller: 'DirectoryDetailCtrl'
                },
                'tabContent@app.directoryDetailDiscussion': {
                    template: require('text!tpl/directory/directory-discussion.html'),
                    controller: 'DirectoryDiscussionCtrl'
                }
            }
        }).state('app.directoryTopicDetail', {
            module: 'directory',
            url: '/directory_topic/:iTopicId',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/directory/directory-topic-detail.html'),
                    controller: 'DirectoryTopicDetailCtrl'
                }
            }
        }).state('app.directoryEditPost', {
            module: 'directory',
            url: '/directory_topic_editpost/:iPostId',
            cache: false,
            history: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/directory/directory-topic-edit-post.html'),
                    controller: 'DirectoryTopicEditPostCtrl'
                }
            }
        }).state('app.directoryQuotePost', {
            module: 'directory',
            url: '/directory_topic_quotepost/:iTopicId/:iPostId',
            cache: false,
            history: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/directory/directory-topic-quote-post.html'),
                    controller: 'DirectoryTopicQuotePostCtrl'
                }
            }
        }).state('app.directoryReplyPost', {
            module: 'directory',
            url: '/directory_topic_reply/:iTopicId',
            cache: false,
            history: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/directory/directory-topic-reply.html'),
                    controller: 'DirectoryTopicReplyCtrl'
                }
            }
        }).state('app.directoryDetailAddTopic', {
            module: 'directory',
            url: '/directory_topic_add/:id',
            cache: false,
            history: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/directory/directory-topic-add.html'),
                    controller: 'DirectoryTopicAddCtrl'
                }
            }
        }).state('app.directoryBusinessIdEvents', {
            module: 'directory',
            url: '/directory/business/:id/events',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/directory/directory-detail.html'),
                    controller: 'DirectoryDetailCtrl'
                },
                'tabContent@app.directoryBusinessIdEvents': {
                    template: require('text!tpl/directory/directory-events.html'),
                    controller: 'DirectoryEventsCtrl'
                }
            }
        }).state('app.directoryBusinessIdMusic', {
            module: 'directory',
            url: '/directory/business/:id/music',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/directory/directory-detail.html'),
                    controller: 'DirectoryDetailCtrl'
                },
                'tabContent@app.directoryBusinessIdMusic': {
                    template: require('text!tpl/directory/directory-music.html'),
                    controller: 'DirectoryMusicCtrl'
                }
            }
        });
    });
});