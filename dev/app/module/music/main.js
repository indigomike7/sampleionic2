define([
    'music/controllers',
    'music/directives',
    'music/plugin/activity',
    'music/plugin/message',
    'text!tpl/music/music-browse-playlist.html',
    'text!tpl/music/music-browse-song.html',
    'text!tpl/music/music-home.html',
    'text!tpl/music/music-my-playlist.html',
    'text!tpl/music/music-my-song.html',
    'text!tpl/music/music-playlist-detail.html',
    'text!tpl/music/music-playlist-detail-content.html',
    'text!tpl/music/music-song-detail.html',
    'text!tpl/music/music-song-detail-content.html'
], function() {
    angular.module('myapp').config(function($stateProvider, $urlRouterProvider, gettext) {
        var site = require('settings/site');
        if (site.template == 'ipad') {
            return $stateProvider.state('app.MusicBrowsePlaylist', {
                module: 'music',
                itemType: 'playlist',
                itemView: 'all',
                url: '/music_playlists',
                cache: false,
                history: {
                    title: gettext('Music'),
                    isRoot: true
                },
                views: {
                    'menuContent': {
                        template: require('text!tpl/music/music-home.html'),
                        controller: 'MusicHomeIpadCtrl',
                    },
                    'leftContent@app.MusicBrowsePlaylist': {
                        template: require('text!tpl/music/music-browse-playlist.html')
                    },
                    'rightContent@app.MusicBrowsePlaylist': {
                        template: require('text!tpl/music/music-playlist-detail-content.html'),
                        controller: 'MusicPlaylistDetailCtrl',
                    }
                }
            }).state('app.MusicMyPlaylist', {
                module: 'music',
                itemType: 'playlist',
                itemView: 'my',
                url: '/music_my_playlists',
                cache: false,
                history: {
                    title: gettext('Music'),
                    isRoot: true
                },
                views: {
                    'menuContent': {
                        template: require('text!tpl/music/music-home.html'),
                        controller: 'MusicHomeIpadCtrl',
                    },
                    'leftContent@app.MusicMyPlaylist': {
                        template: require('text!tpl/music/music-my-playlist.html')
                    },
                    'rightContent@app.MusicMyPlaylist': {
                        template: require('text!tpl/music/music-playlist-detail-content.html'),
                        controller: 'MusicPlaylistDetailCtrl',
                    }
                }
            }).state('app.MusicBrowseSong', {
                module: 'music',
                itemType: 'song',
                itemView: 'all',
                url: '/music_songs',
                cache: false,
                history: {
                    title: gettext('Music'),
                    isRoot: true
                },
                views: {
                    'menuContent': {
                        template: require('text!tpl/music/music-home.html'),
                        controller: 'MusicHomeIpadCtrl',
                    },
                    'leftContent@app.MusicBrowseSong': {
                        template: require('text!tpl/music/music-browse-song.html')
                    },
                    'rightContent@app.MusicBrowseSong': {
                        template: require('text!tpl/music/music-song-detail-content.html'),
                        controller: 'MusicSongDetailCtrl',
                    }
                }
            }).state('app.MusicMySong', {
                module: 'music',
                itemType: 'song',
                itemView: 'my',
                url: '/music_my_songs',
                cache: false,
                history: {
                    title: gettext('Music'),
                    isRoot: true
                },
                views: {
                    'menuContent': {
                        template: require('text!tpl/music/music-home.html'),
                        controller: 'MusicHomeIpadCtrl',
                    },
                    'leftContent@app.MusicMySong': {
                        template: require('text!tpl/music/music-my-song.html')
                    },
                    'rightContent@app.MusicMySong': {
                        template: require('text!tpl/music/music-song-detail-content.html'),
                        controller: 'MusicSongDetailCtrl',
                    }
                }
            }).state('app.MusicPlaylistDetail', {
                module: 'music',
                itemType: 'playlist',
                itemView: 'all',
                url: '/music_playlist/:iItemId',
                cache: false,
                history: {
                    title: gettext('Music'),
                    isRoot: true
                },
                sModelType: 'music_playlist',
                views: {
                    'menuContent': {
                        template: require('text!tpl/music/music-home.html'),
                        controller: 'MusicHomeIpadCtrl',
                    },
                    'leftContent@app.MusicPlaylistDetail': {
                        template: require('text!tpl/music/music-browse-playlist.html')
                    },
                    'rightContent@app.MusicPlaylistDetail': {
                        template: require('text!tpl/music/music-playlist-detail-content.html'),
                        controller: 'MusicPlaylistDetailCtrl',
                    }
                }

            }).state('app.MusicAlbumDetail3', {
                module: 'music',
                itemType: 'playlist',
                itemView: 'all',
                url: '/musicsharing_album/:iItemId',
                cache: false,
                history: {
                    title: gettext('Music'),
                    isRoot: true
                },
                sModelType: 'musicsharing_album',
                views: {
                    'menuContent': {
                        template: require('text!tpl/music/music-home.html'),
                        controller: 'MusicHomeIpadCtrl',
                    },
                    'leftContent@app.MusicAlbumDetail3': {
                        template: require('text!tpl/music/music-browse-playlist.html')
                    },
                    'rightContent@app.MusicAlbumDetail3': {
                        template: require('text!tpl/music/music-playlist-detail-content.html'),
                        controller: 'MusicPlaylistDetailCtrl',
                    }
                }
            }).state('app.MusicAlbumDetail', {
                module: 'music',
                itemType: 'playlist',
                itemView: 'all',
                url: '/music_album/:iItemId',
                cache: false,
                history: {
                    title: gettext('Music'),
                    isRoot: true
                },
                sModelType: 'music_album',
                views: {
                    'menuContent': {
                        template: require('text!tpl/music/music-home.html'),
                        controller: 'MusicHomeIpadCtrl',
                    },
                    'leftContent@app.MusicAlbumDetail': {
                        template: require('text!tpl/music/music-browse-playlist.html')
                    },
                    'rightContent@app.MusicAlbumDetail': {
                        template: require('text!tpl/music/music-playlist-detail-content.html'),
                        controller: 'MusicPlaylistDetailCtrl',
                    }
                }
            }).state('app.MusicSongDetail2', {
                module: 'music',
                itemType: 'song',
                itemView: 'all',
                url: '/musicsharing_song/:iItemId',
                cache: false,
                history: {
                    title: gettext('Music'),
                    isRoot: true
                },
                sModelType: 'musicsharing_song',
                views: {
                    'menuContent': {
                        template: require('text!tpl/music/music-home.html'),
                        controller: 'MusicHomeIpadCtrl',
                    },
                    'leftContent@app.MusicSongDetail2': {
                        template: require('text!tpl/music/music-browse-song.html')
                    },
                    'rightContent@app.MusicSongDetail2': {
                        template: require('text!tpl/music/music-song-detail-content.html'),
                        controller: 'MusicSongDetailCtrl',
                    }
                }
            }).state('app.MusicSongDetail', {
                module: 'music',
                itemType: 'song',
                itemView: 'all',
                url: '/music_song/:iItemId',
                cache: false,
                history: {
                    title: gettext('Music'),
                    isRoot: true
                },
                sModelType: 'music_song',
                views: {
                    'menuContent': {
                        template: require('text!tpl/music/music-home.html'),
                        controller: 'MusicHomeIpadCtrl',
                    },
                    'leftContent@app.MusicSongDetail': {
                        template: require('text!tpl/music/music-browse-song.html')
                    },
                    'rightContent@app.MusicSongDetail': {
                        template: require('text!tpl/music/music-song-detail-content.html'),
                        controller: 'MusicSongDetailCtrl',
                    }
                }
            }).state('app.MusicDetail', {
                module: 'music',
                itemType: 'song',
                itemView: 'all',
                url: '/music/:iItemId',
                cache: false,
                history: {
                    title: gettext('Music'),
                    isRoot: true
                },
                views: {
                    'menuContent': {
                        template: require('text!tpl/music/music-home.html'),
                        controller: 'MusicHomeIpadCtrl',
                    },
                    'leftContent@app.MusicDetail': {
                        template: require('text!tpl/music/music-browse-song.html')
                    },
                    'rightContent@app.MusicDetail': {
                        template: require('text!tpl/music/music-song-detail-content.html'),
                        controller: 'MusicSongDetailCtrl',
                    }
                }
            }).state('app.MusicPlaylistDetail4', {
                module: 'music',
                url: '/music_album/:iAlbumId/:sParentType/:iParentId',
                cache: false,
                sModelType: 'music_album',
                views: {
                    'menuContent': {
                        template: require('text!tpl/music/music-playlist-detail.html'),
                        controller: 'MusicPlaylistDetailCtrl',
                    }
                }
            }).state('app.MusicPlaylistDetail5', {
                module: 'music',
                url: '/musicsharing_album/:iAlbumId/:sParentType/:iParentId',
                cache: false,
                sModelType: 'musicsharing_album',
                views: {
                    'menuContent': {
                        template: require('text!tpl/music/music-playlist-detail.html'),
                        controller: 'MusicPlaylistDetailCtrl',
                    }
                }
            }).state('app.MusicSongDetail4', {
                module: 'music',
                url: '/music_song/:iSongId/:sParentType/:iParentId',
                cache: false,
                sModelType: 'music_song',
                views: {
                    'menuContent': {
                        template: require('text!tpl/music/music-song-detail.html'),
                        controller: 'MusicSongDetailCtrl',
                    }
                }
            }).state('app.MusicSongDetail5', {
                module: 'music',
                url: '/musicsharing_song/:iSongId/:sParentType/:iParentId',
                cache: false,
                sModelType: 'musicsharing_song',
                views: {
                    'menuContent': {
                        template: require('text!tpl/music/music-song-detail.html'),
                        controller: 'MusicSongDetailCtrl',
                    }
                }
            });
        }

        $stateProvider.state('app.MusicPlaylistDetail', {
            module: 'music',
            url: '/music_playlist/:iAlbumId',
            cache: false,
            sModelType: 'music_playlist',
            views: {
                'menuContent': {
                    template: require('text!tpl/music/music-playlist-detail.html'),
                    controller: 'MusicPlaylistDetailCtrl',
                }
            }
        }).state('app.MusicAlbumDetail3', {
            module: 'music',
            url: '/musicsharing_album/:iAlbumId',
            cache: false,
            sModelType: 'musicsharing_album',
            views: {
                'menuContent': {
                    template: require('text!tpl/music/music-playlist-detail.html'),
                    controller: 'MusicPlaylistDetailCtrl',
                }
            }
        }).state('app.MusicAlbumDetail', {
            module: 'music',
            url: '/music_album/:iAlbumId',
            cache: false,
            sModelType: 'music_album',
            views: {
                'menuContent': {
                    template: require('text!tpl/music/music-playlist-detail.html'),
                    controller: 'MusicPlaylistDetailCtrl',
                }
            }
        }).state('app.MusicSongDetail3', {
            module: 'music',
            url: '/musicsharing_song/:iSongId',
            cache: false,
            sModelType: 'musicsharing_song',
            views: {
                'menuContent': {
                    template: require('text!tpl/music/music-song-detail.html'),
                    controller: 'MusicSongDetailCtrl',
                }
            }
        }).state('app.MusicSongDetail', {
            module: 'music',
            url: '/music_song/:iSongId',
            cache: false,
            sModelType: 'music_song',
            views: {
                'menuContent': {
                    template: require('text!tpl/music/music-song-detail.html'),
                    controller: 'MusicSongDetailCtrl',
                }
            }
        }).state('app.MusicDetail', {
            module: 'music',
            url: '/music/:iSongId',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/music/music-song-detail.html'),
                    controller: 'MusicSongDetailCtrl',
                }
            }
        }).state('app.MusicBrowsePlaylist', {
            module: 'music',
            url: '/music_playlists',
            cache: false,
            history: {
                title: gettext('Music'),
                isRoot: true
            },
            views: {
                'menuContent': {
                    template: require('text!tpl/music/music-home.html'),
                    controller: 'MusicHomeCtrl',
                },
                'tabContent@app.MusicBrowsePlaylist': {
                    template: require('text!tpl/music/music-browse-playlist.html'),
                    controller: 'MusicBrowsePlaylistCtrl',
                }
            }
        }).state('app.MusicMyPlaylist', {
            module: 'music',
            url: '/music_my_playlists',
            cache: false,
            history: {
                title: gettext('Music'),
                isRoot: true
            },
            views: {
                'menuContent': {
                    template: require('text!tpl/music/music-home.html'),
                    controller: 'MusicHomeCtrl',
                },
                'tabContent@app.MusicMyPlaylist': {
                    template: require('text!tpl/music/music-my-playlist.html'),
                    controller: 'MusicMyPlaylistCtrl',
                }
            }
        }).state('app.MusicBrowseSong', {
            module: 'music',
            url: '/music_songs',
            cache: false,
            history: {
                title: gettext('Music'),
                isRoot: true
            },
            views: {
                'menuContent': {
                    template: require('text!tpl/music/music-home.html'),
                    controller: 'MusicHomeCtrl',
                },
                'tabContent@app.MusicBrowseSong': {
                    template: require('text!tpl/music/music-browse-song.html'),
                    controller: 'MusicBrowseSongCtrl',
                }
            }
        }).state('app.MusicMySong', {
            module: 'music',
            url: '/music_my_songs',
            cache: false,
            history: {
                title: gettext('Music'),
                isRoot: true
            },
            views: {
                'menuContent': {
                    template: require('text!tpl/music/music-home.html'),
                    controller: 'MusicHomeCtrl',
                },
                'tabContent@app.MusicMySong': {
                    template: require('text!tpl/music/music-my-song.html'),
                    controller: 'MusicMySongCtrl',
                }
            }
        }).state('app.MusicPlaylistDetail4', {
            module: 'music',
            url: '/music_album/:iAlbumId/:sParentType/:iParentId',
            cache: false,
            sModelType: 'music_album',
            views: {
                'menuContent': {
                    template: require('text!tpl/music/music-playlist-detail.html'),
                    controller: 'MusicPlaylistDetailCtrl',
                }
            }
        }).state('app.MusicPlaylistDetail5', {
            module: 'music',
            url: '/musicsharing_album/:iAlbumId/:sParentType/:iParentId',
            cache: false,
            sModelType: 'musicsharing_album',
            views: {
                'menuContent': {
                    template: require('text!tpl/music/music-playlist-detail.html'),
                    controller: 'MusicPlaylistDetailCtrl',
                }
            }
        }).state('app.MusicSongDetail4', {
            module: 'music',
            url: '/music_song/:iSongId/:sParentType/:iParentId',
            cache: false,
            sModelType: 'music_song',
            views: {
                'menuContent': {
                    template: require('text!tpl/music/music-song-detail.html'),
                    controller: 'MusicSongDetailCtrl',
                }
            }
        }).state('app.MusicSongDetail5', {
            module: 'music',
            url: '/musicsharing_song/:iSongId/:sParentType/:iParentId',
            cache: false,
            sModelType: 'musicsharing_song',
            views: {
                'menuContent': {
                    template: require('text!tpl/music/music-song-detail.html'),
                    controller: 'MusicSongDetailCtrl',
                }
            }
        });
    });
});