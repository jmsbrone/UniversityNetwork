app.controller('studentScheduleController', ['$scope', 'api', 'flib', 'storage', '$timeout', function($scope, api, flib, storage, $timeout){
    
    $scope.selectedDate = new Date();
    
    function updateFn(){
        if (!storage.program){
            $timeout(updateFn, 500);
            return;
        }
        api.get('schedule_mod', 'list', {}).then(function(response){
            console.debug('schedule list');
            console.debug(response);
            $scope.rules = response.data;

            $scope.times = ['08:30-10:00', '10:15-11:45', '12:00-13:30', '14:00-15:30'];
            var weekStart = new Date($scope.selectedDate.valueOf());
            switch(weekStart.getDay()){
                case 6:
                    weekStart.setDate(weekStart.getDate() + 2);
                    break;
                case 0:
                    weekStart.setDate(weekStart.getDate() + 1);
                    break;
            }
            weekStart.setDate(weekStart.getDate() - weekStart.getDay() + 1);
            weekStart.setHours(0);
            var weekEnd = new Date(weekStart.valueOf());
            weekEnd.setDate(weekEnd.getDate() + 7);

            var classes = [[],[],[],[],[]];

            for(i=0; i< $scope.rules.length; ++i){
                var rule = $scope.rules[i];
                var validRule = true;
                for(k=0;k<storage.program.length;++k){
                    if (storage.program[k].subjectID == rule.subjectID){
                        // Subgroup not set for user
                        if (!storage.program[k].subgroup || !rule.subgroup) break;
                        // Subgroup differs for user
                        if (storage.program[k].subgroup != rule.subgroup) {
                            validRule = false;
                        }
                        break;
                    }
                }
                if (!validRule) continue;
                for(k=0; k < rule.classes.length; ++k){
                    var classTime = rule.classes[k].startTimestamp = flib.timestampToDate(rule.classes[k].startTimestamp);
                    if (classTime >= weekStart && classTime < weekEnd){
                        var order = classTime.getHours() / 2 - 4;
                        var wday = classTime.getDay() - 1;
                        
                        classes[wday][order] = rule;
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
            $scope.activeDay = $scope.selectedDate.getDay() - 1;
            $scope.classes = classes;
            waitFn();
        }, function(response){
            console.debug(response);        
        });
    };
    $scope.getProfName = function(day, order){
        if (!$scope.classes) return;
        if (!$scope.classes[day][order] || $scope.classes[day][order].profs.length == 0) return;
        var profs = $scope.classes[day][order].profs;
        return profs[0].surname + ' ' + profs[0].name[0] + '.' + profs[0].lastname[0] + '.';
    }
    
    api.get('subject_mod', 'list',{}).then(function(response){
        console.debug('subjects');
        console.debug(response);
        
        $scope.subjects = response.data;
    }, function(response){
        console.debug(response);
    })
    
    $scope.getClassType = function(rule){
        if (!rule) return;
        switch(rule.classType){
            case 'lab': return 'лб';
            case 'lection': return 'лк';
            case 'activity': return 'у';
        }
    }
    
    var waitFn = function(){
        if (!$scope.$$phase){
            $scope.week = storage.getWeek($scope.selectedDate);
            $scope.semester = storage.semester;
            return;
        }
        $timeout(waitFn, 500);
    };
    
    $scope.$watch('selectedDate', function(oldValue, newValue){
        if (!newValue || newValue == oldValue) return;
        updateFn();
    });
    
    $timeout(updateFn, 500);
    
    $scope.disabledWeekendsPredicate = function(date){
        var day = date.getDay();
        return day != 0 && day != 6;
    }
    
    $scope.checkSubgroup = function(rule){
        if (!rule) return false;
        if (!rule.subgroup) return true;
        if (!storage.program) return true;
        for(i=0;i<storage.program.length;++i){
            if (storage.program[i].subjectID == rule.subjectID){
                if (storage.program[i].subgroup == rule.subgroup) return true;
                return false;
            }
        }
        return false;
    }
}]);