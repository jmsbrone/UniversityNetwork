// Подключение AngularJS с библиотеками
var app = angular.module("websiteApp", ['ui.router', 'ngMaterial', 'ngMessages', 'angular-loading-bar', 'ngAnimate']);

// Путь к моделям маршрутизатора.
var viewFolder = 'ui.router/views/';
app.config(['cfpLoadingBarProvider', function(cfpLoadingBarProvider) {
    cfpLoadingBarProvider.latencyThreshold = 25;
}]);
// Настройка маршрутизатора
app.config(['$stateProvider', '$urlRouterProvider', function($stateProvider, $urlRouterProvider) {
    $urlRouterProvider.otherwise('/main');

    $stateProvider
    // Корень
    .state('app',{
        abstract: true,
        url: '/',
        views: {
            'header' : {
                templateUrl: viewFolder + 'header.html',
                controller: 'headerController'
            },
            'body' : {},
            'footer' : {
                templateUrl: viewFolder + 'footer.html'
            }
        }
    })
    .state('app.main', {
        url: 'main',
        views: {
            'main' : {
                templateUrl: viewFolder + 'main.html'
            }
        }
    })
    // Состояние отображения страницы входа. Внутри ui-view="body".
    .state('app.login',{
        url: 'login',
        views: {
            'login' : {
                templateUrl: viewFolder + 'login.html',
                controller: 'loginController'
            }
        }
    })
    // Состояние отображения страницы регистрации. Внутри ui-view="body".
    .state('app.register',{
        url: 'register',
        views: {
            'register' : {
                templateUrl: viewFolder + 'register.html',
                controller: 'registerController'
            }
        }
    })
    // Кабинеты
    .state('app.admin',{
        url: 'admin',
        views: {
            'adminPC' : {
                templateUrl: viewFolder + 'admin.html',
                controller: 'adminController'
            }
        }
    })
    .state('app.manager',{
        url: 'manager',
        views: {
            'managerPC' : {
                templateUrl: viewFolder + 'manager.html',
                controller: 'managerController'
            }
        }
    })
    .state('app.student',{
        url: 'student',
        views: {
            'studentPC' : {
                templateUrl: viewFolder + 'student.html'
            }
        }
    })
    .state('app.student.profile',{
        url: '/profile',
        views: {
            'profile' : {
                templateUrl: viewFolder + 'student_profile.html',
                controller: 'studentProfileController'
            }
        }
    })
    .state('app.student.news',{
        url: '/news',
        views: {
            'news' : {
                templateUrl: viewFolder + 'student_news.html'
            }
        }
    })
    .state('app.student.group',{
        url: '/group',
        views: {
            'group' : {
                templateUrl: viewFolder + 'student_group.html'
            }
        }
    })
    .state('app.student.schedule', {
        url: '/schedule',
        views: {
            'schedule' : {
                templateUrl: viewFolder + 'student_schedule.html',
                controller: 'studentScheduleController'
            }
        }
    })
    .state('app.student.subjects', {
        url: '/subjects',
        views: {
            'subjects' : {
                templareUrl: viewFolder + 'student_subjects.html'
            }
        }
    })
}]);