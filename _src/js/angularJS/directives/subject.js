app.directive('subject', function(){
    return {
        restrict: 'E',
        scope: {
            data: '='
        },
        templateUrl: '/_src/js/angularJS/directives/subject.html',
        link: function($scope, elem, attr){
            $scope.subjectName = $scope.data.subjectName;
            $scope.countAlbums = function(){
                return '0 альбомов';
            }
            
            $scope.assignedProf = '';
        }
    };
});