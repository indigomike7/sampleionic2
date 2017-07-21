define([
    'quiz/controllers',
    'quiz/directives',
    'quiz/plugin/activity',
    'text!tpl/quiz/quiz-add.html',
    'text!tpl/quiz/quiz-browse-quiz.html',
    'text!tpl/quiz/quiz-detail.html',
    'text!tpl/quiz/quiz-edit.html',
    'text!tpl/quiz/quiz-start.html',
    'text!tpl/quiz/quiz-result.html',
    'text!tpl/quiz/quiz-home.html',
    'text!tpl/quiz/quiz-list.html',
    'text!tpl/quiz/quiz-my-quiz.html',
    'text!tpl/quiz/quiz-search.html',
], function() {
    
    angular.module('myapp')
    .config(function($stateProvider, $urlRouterProvider, gettext) {
        $stateProvider
        .state('app.QuizDetail', {
            module: 'quiz',
            url: '/quiz/:iQuizId',
            cache: false,
            history: ionic.Platform.isIPad() ? {
                title: gettext('Quizzes'),
                isRoot: true
            } : {},
            views: {
                'menuContent': ionic.Platform.isIPad() ? {
                    template: require('text!tpl/quiz/quiz-home.html'),
                    controller: 'QuizHomeCtrl'
                } : {
                    template: require('text!tpl/quiz/quiz-detail.html'),
                    controller: 'QuizDetailCtrl'
                }
            }
        })
        .state('app.QuizEdit', {
            module: 'quiz',
            url: '/quizzes/edit/:iQuizId',
            cache: false,
            history: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/quiz/quiz-edit.html'),
                    controller: 'QuizEditCtrl'
                }
            }
        })
        .state('app.QuizStart', {
            module: 'quiz',
            url: '/quiz/:iQuizId/start',
            cache: false,
            history: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/quiz/quiz-start.html'),
                    controller: 'QuizStartCtrl'
                }
            }
        })
        .state('app.QuizResult', {
            module: 'quiz',
            url: '/quiz/:iQuizId/result/:iUserId',
            cache: false,
            history: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/quiz/quiz-result.html'),
                    controller: 'QuizResultCtrl'
                }
            }
        })
        .state('app.QuizAdd', {
            module: 'quiz',
            url: '/quizzes/add',
            cache: false,
            history: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/quiz/quiz-add.html'),
                    controller: 'QuizAddCtrl'
                }
            }
        })
        .state('app.QuizBrowse', {
            module: 'quiz',
            url: '/quizzes',
            cache: false,
            history: {
                title: gettext('Quizzes'),
                isRoot: true
            },
            views: {
                'menuContent': {
                    template: require('text!tpl/quiz/quiz-home.html'),
                    controller: 'QuizHomeCtrl'
                },
                'tabContent@app.QuizBrowse': {
                    template: require('text!tpl/quiz/quiz-browse-quiz.html'),
                    controller: 'QuizBrowseQuizCtrl'
                }
            }
        })
         .state('app.QuizMy', {
            module: 'quiz',
            url: '/quizzes/my',
            cache: false,
            history: {
                title: gettext('Quizzes'),
                isRoot: true
            },
            views: {
                'menuContent': {
                    template: require('text!tpl/quiz/quiz-home.html'),
                    controller: 'QuizHomeCtrl'
                },
                'tabContent@app.QuizMy': {
                    template: require('text!tpl/quiz/quiz-my-quiz.html'),
                    controller: 'QuizMyQuizCtrl'
                }
            }
        });
    });
});