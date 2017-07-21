define([
	'quiz/directive/quiz-detail',
    'quiz/directive/quiz-list',
    'quiz/directive/quiz-search',
],function(){
    angular.module('myapp.directives')
    .directive('quizDetailDir', require('quiz/directive/quiz-detail'))
    .directive('quizSearchDir', require('quiz/directive/quiz-search'))
    .directive('quizListDir', require('quiz/directive/quiz-list'))
    ;
});
