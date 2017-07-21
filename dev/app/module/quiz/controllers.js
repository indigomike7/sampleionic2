define([
    'quiz/controller/quiz-add',
    'quiz/controller/quiz-browse-quiz',
    'quiz/controller/quiz-detail',
    'quiz/controller/quiz-edit',
    'quiz/controller/quiz-start',
    'quiz/controller/quiz-result',
    'quiz/controller/quiz-home',
    'quiz/controller/quiz-item',
    'quiz/controller/quiz-list',
    'quiz/controller/quiz-my-quiz',
], function() {
    angular.module('myapp.controllers')
        .controller('QuizItemCtrl',require('quiz/controller/quiz-item'))
        .controller('QuizDetailCtrl', require('quiz/controller/quiz-detail'))
        .controller('QuizEditCtrl', require('quiz/controller/quiz-edit'))
        .controller('QuizStartCtrl', require('quiz/controller/quiz-start'))
        .controller('QuizResultCtrl', require('quiz/controller/quiz-result'))
        .controller('QuizAddCtrl', require('quiz/controller/quiz-add'))
        .controller('QuizHomeCtrl', require('quiz/controller/quiz-home'))
        .controller('QuizMyQuizCtrl', require('quiz/controller/quiz-my-quiz'))
        .controller('QuizBrowseQuizCtrl', require('quiz/controller/quiz-browse-quiz'))
        ;
});