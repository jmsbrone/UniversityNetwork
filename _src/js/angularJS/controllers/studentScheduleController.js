app.controller('studentScheduleController', ['$scope', 'api', 'flib', function($scope, api, flib){
    console.debug('schedule controller is initialized');

    api.get('schedule_mod', 'list', {}).then(function(response){
        console.debug('schedule list');
        console.debug(response);
        
        $scope.rules = response.data;
    }, function(response){
        console.debug(response);        
    });
}]);