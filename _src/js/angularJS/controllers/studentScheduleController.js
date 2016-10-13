app.controller('studentScheduleController', ['$scope', 'api', 'flib', 'storage', '$timeout', function($scope, api, flib, storage, $timeout){
    api.get('schedule_mod', 'list', {}).then(function(response){
        console.debug('schedule list');
        console.debug(response);
        $scope.rules = response.data;
        
        $scope.times = ['08:30-10:00', '10:15-11:45', '12:00-13:30', '14:00-15:30'];
        var weekStart = new Date();
        weekStart.setDate(weekStart.getDate() - weekStart.getDay() + 1);
        weekStart.setHours(0);
        var weekEnd = new Date(weekStart.valueOf());
        weekEnd.setDate(weekEnd.getDate() + 7);
        
        var classes = [[],[],[],[],[]];
        
        for(i=0; i< $scope.rules.length; ++i){
            var rule = $scope.rules[i];
            for(k=0; k < rule.classes.length; ++k){
                var classTime = rule.classes[k].startTimestamp = flib.timestampToDate(rule.classes[k].startTimestamp);
                if (classTime >= weekStart && classTime < weekEnd){
                    var order = classTime.getHours() / 2 - 4;
                    var wday = classTime.getDay() - 1;
                    
                    classes[wday][order] = rule.classes[k];
                }
            }
        }
        var dates = [];
        var startTimestamp = weekStart.valueOf();
        for(i=0;i<5;++i){
            dates.push(new Date(startTimestamp));
            // +1 day worth of ms
            startTimestamp += 1000 * 3600 * 24;
        }
        $scope.dates = dates;
        $scope.activeDay = (new Date()).getDay() - 1;
    }, function(response){
        console.debug(response);        
    });
    
    api.get('subject_mod', 'list',{}).then(function(response){
        console.debug('subjects');
        console.debug(response);
        
        $scope.subjects = response.data;
    }, function(response){
        console.debug(response);
    })
    
    var waitFn = function(){
        if (!$scope.$$phase && storage.currentWeek){
            $scope.week = storage.currentWeek;
            $scope.semester = storage.semester;
            return;
        }
        $timeout(waitFn, 500);
    };
    
    waitFn();
    
    $scope.selectedDate = new Date();
}]);