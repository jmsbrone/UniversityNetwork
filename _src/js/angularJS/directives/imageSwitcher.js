app.directive('imageSwitcher',['$http',function($http){
    return {
        restrict: 'A',
        link: function($scope, elem, attr){
            attr.$observe('ngSrc', function(ngSrc){
                $http.get(ngSrc).then(function(response){}, function(response){
                    
                });
            });
        }
    }
}]);