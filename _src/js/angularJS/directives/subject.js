app.directive('subject', ['$mdDialog', 'api', function($mdDialog, api){
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
            
            $scope.assignedProf = $scope.data.profSurname + ' ' + $scope.data.profName[0] + '.' + $scope.data.profLastname[0] + '.';
            $scope.fullName = $scope.data.profSurname + ' ' + $scope.data.profName + ' ' + $scope.data.profLastname;
            $scope.subgroup = $scope.data.subgroup;
            
            $scope.showAlbums = function(){
                
            };
            $scope.asgClick = function(){
                
            };
            $scope.subgroupChanged = function(){
                if (!$scope.subgroup) return;
                api.get('subgroup_mod', 'select',{
                    index: $scope.subgroup,
                    programID: $scope.data.id
                }).then(function(response){
                    console.debug('subgroup select success');
                    console.debug(response);
                }, function(response){
                    console.debug(response);
                });
            };
        }
    };
}]);