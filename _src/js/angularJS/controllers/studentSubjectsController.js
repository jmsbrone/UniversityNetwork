app.controller('studentSubjectsController', ['$scope', 'api', function($scope, api){
    api.get('group_req','subject_list', {
        semesterID:1
    }).then(function(response){
        console.debug(response);
        $scope.list = response.data;
    }, function(response){
        console.debug(response);
    });
}]);