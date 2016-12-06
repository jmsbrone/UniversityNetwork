app.controller('studentScheduleController', ['$scope', '$mdPanel', '$mdDialog', 'api', 'flib', 'storage', '$timeout', function($scope, $mdPanel, $mdDialog, api, flib, storage, $timeout){
    $scope.selectedDate = new Date();
    
    function updateFn(){
        if (!storage.program || !storage.rules){
            $timeout(updateFn, 50);
            return;
        }
        $scope.rules = storage.rules;
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
                var cl = rule.classes[k];
                if (typeof(cl.startTimestamp) == 'string') {
                    cl.startTimestamp = flib.timestampToDate(cl.startTimestamp);
                }
                var classTime = cl.startTimestamp;
                if (classTime >= weekStart && classTime < weekEnd){
                    var order = classTime.getHours() / 2 - 4;
                    var wday = classTime.getDay() - 1;

                    classes[wday][order] = rule;
                    classes[wday][order].id = cl.id;
                }
            }
            //rule.classes = null;
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
    };
    $scope.getProfName = function(day, order){
        if (!$scope.classes) return;
        if (!$scope.classes[day][order] || $scope.classes[day][order].profs.length == 0) return;
        var profs = $scope.classes[day][order].profs;
        return profs[0].surname + ' ' + profs[0].name[0] + '.' + profs[0].lastname[0] + '.';
    };
    
    $scope.getClassType = function(rule){
        if (!rule) return;
        switch(rule.classType){
            case 'lab': return 'лб';
            case 'lection': return 'лк';
            case 'activity': return 'у';
        }
    };
    
    var waitFn = function(){
        if (!$scope.$$phase){
            $scope.week = storage.getWeek($scope.selectedDate);
            $scope.semester = storage.semester;
            return;
        }
        $timeout(waitFn, 50);
    };
    
    $scope.$watch('selectedDate', function(oldValue, newValue){
        if (!newValue || newValue == oldValue) return;
        updateFn();
    });
    
    $timeout(updateFn, 50);
    
    $scope.disabledWeekendsPredicate = function(date){
        var day = date.getDay();
        return day != 0 && day != 6;
    };
    
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
    
    $scope.openSelection = function($event, cl){
        var position = $mdPanel.newPanelPosition()
            .relativeTo($event.target.closest('button'))
            .addPanelPosition($mdPanel.xPosition.ALIGN_START, $mdPanel.yPosition.BELOW);
        
        var mdPanelRef = $mdPanel.create({
            attachTo: angular.element(document.body),
            controller: 'asgSelectionController',
            controllerAs: 'ctrl',
            template: 
                '<md-list md-colors="{background: \'background\'}">' +
                '   <md-list-item layout-align="center center" ng-repeat="option in ctrl.options" ng-click="close(option)">' +
                '       <p>{{ option }}</p>' + 
                '   </md-list-item>' + 
                '</md-list>'
            ,
            clickOutsideToClose: true,
            escapeToClose: true,
            locals: {
                'options' : function(c){
                    switch(c.classType){
                        case 'lection': return false;
                        case 'lab': return ['Лабораторная'];
                        case 'activity': return ['Контрольная', 'Тест', 'Семинар'];
                    }
                }(cl)
            },
            position: position
        });
        //$scope.selected = mdPanelRef.selected;
        mdPanelRef.open();
        $scope.panel = mdPanelRef;
        $scope.class = cl;
    };
    $scope.$watch('panel.selected', function(){
        if (!$scope.panel) return;
        //alert($scope.panel.selected);
    });
    
    $scope.canHaveAsg = function(cl){
        if (!cl) return false;
        return cl.classType != 'lection';
    };
    
    // Class menu
    $scope.onClassClick = function(cl, event){
        $mdDialog.show({
            controller: 'classDialogController',
            templateUrl: 'ui.router/templates/class_dialog.html',
            parent: angular.element(document.body),
            clickOutsideToClose: false,
            locals: {
                classObj: cl
            }
        }).then();
    };
}]);
app.controller('classDialogController', ['$scope', '$mdDialog', 'flib', 'classObj', 'api', '$timeout', function($scope, $mdDialog, flib, cl, api, $timeout){
    // Setup and global functions.
    $scope.classObj = cl;
    
    $scope.close = function(){
        $mdDialog.hide();
    };
    
    $scope.fixTabs = function(){
        if ($scope.$$phase){
            $timeout($scope.fixTabs, 100);
            return;
        }
        var width = $('md-dialog md-pagination-wrapper').width();
        $('md-dialog md-pagination-wrapper').width(width + 1);
    };
    
    $scope.getContentHeight = function(){
        try{
            var newHeight = $('md-dialog').height() + $('md-dialog').offset().top - $('#tabs-content').offset().top - 5;
            return newHeight;
        } catch(err){
            console.debug('Error during setting height: ' + err);
        }
    }
    
    //------------------------------------------------------------------
    // Assignments.
    
    $scope.labs = [];
    $scope.newAsg = {};
    
    api.post('lab_mod', 'class_list', {
        classID: $scope.classObj.id
    },function(response){
        console.debug(response);
        $scope.labs = response.data;
        for(i=0;i<$scope.labs.length;++i){
            $scope.labs[i].completed = $scope.labs[i].completed == '1';
        }
        $timeout($scope.fixTabs, 250);
    }, function(response){
        console.debug(response);
    });
    
    api.post('cg_mod', 'class_list', {
        classID: $scope.classObj.id
    },function(response){
        console.debug(response);
        $scope.cgs = response.data;
        for(i=0;i<$scope.cgs.length;++i){
            $scope.cgs[i].completed = $scope.cgs[i].completed == '1';
        }
    }, function(response){
        console.debug(response);
    });
    
    api.post('kr_mod', 'class_list', {
        classID: $scope.classObj.id
    },function(response){
        console.debug(response);
        $scope.krs = response.data;
        for(i=0;i<$scope.krs.length;++i){
            $scope.krs[i].completed = $scope.krs[i].completed == '1';
        }
    }, function(response){
        console.debug(response);
    });
    
    api.post('tests_mod', 'class_list', {
        classID: $scope.classObj.id
    },function(response){
        console.debug(response);
        $scope.tests = response.data;
        for(i=0;i<$scope.tests.length;++i){
            $scope.tests[i].completed = $scope.tests[i].completed == '1';
        }
    }, function(response){
        console.debug(response);
    });
    
    $scope.confirmAsg = function(){
        if (!$scope.newAsg.desc){
            $scope.newAsg.desc = null;
        }
        $scope.newAsg.classID = $scope.classObj.id;
        var returnArray = null;
        switch($scope.asgType){
            case 'lab':
                var request = 'lab_mod';
                returnArray = 'labs';
                break;
            case 'kr':
                var request = 'kr_mod';
                returnArray = 'krs';
                break;
            case 'cg':
                var request = 'cg_mod';
                returnArray = 'cgs';
                break;
            case 'test':
                var request = 'tests_mod';
                returnArray = 'tests';
                break;
        }
        api.post(request, $scope.modifyAsg ? 'modify' : 'add', $scope.newAsg)
            .then(function(response){
                if (!$scope.modifyAsg){
                    $scope.newAsg.id = response.data.id;
                    $scope[returnArray].push($scope.newAsg);
                }
                $scope.addAsgForm = false;
                $scope.modifyAsg = false;
                $scope.newAsg = {};
            }, function(response){
                console.debug(response);
            });
    };
    $scope.updateStatus = function(type, asg){
        if (!asg._count) {
            asg._count = 0;
        }
        asg._count++;
        $timeout(function(){
            if (--asg._count != 0) return;
            api.post(type+'_mod', asg.completed ? 'set' : 'unset',{
                asgID: asg.id
            },function(response){
                
            }, function(response){
                console.debug(response);
            });
        }, 500);
    };
    
    $scope.deleteAsgFn = function(type, asg){
        var returnArray = null;
        var requestGroup = '';
        
        switch(type){
            case 'lab':
                requestGroup = 'lab';
                returnArray = 'labs';
                break;
            case 'cg':
                requestGroup = 'cg';
                returnArray = 'cgs';
                break;
            case 'kr':
                requestGroup = 'kr';
                returnArray = 'krs';
                break;
            case 'test':
                requestGroup = 'tests';
                returnArray = 'tests';
                break;
        }
        api.post(requestGroup + '_mod', 'delete', {
            id: asg.id
        },function(response){
            $scope[returnArray] = flib.eject($scope[returnArray], asg);
        }, function(response){
            console.debug(response);
        });
    };
    
    $scope.modifyAsgFn = function(type, asg){
        $scope.addAsgForm = true;
        $scope.newAsg = asg;
        if ($scope.newAsg.order){
            try{
                $scope.newAsg.order = parseInt($scope.newAsg.order);
            }catch(err){
                console.debug(err);
            }
        }
        $scope.asgModify = true;
    };
    
    //------------------------------------------------------------------
    // Files.
    
    // Attaching reaction to file selection change.
    $timeout(function(){
        $('#upload-file').on('change', function(){
            var files = $('#upload-file')[0].files;
            $scope.uploadFiles = [];
            for(i=0;i<files.length;++i){
                $scope.uploadFiles.push({
                    originalName: files[i].name,
                    name: files[i].name
                });
            }
            if (!$scope.$$phase){
                $scope.$apply();
            }
        });
    }, 1500);
    
    api.post('upload_req', 'class_files', {
        classID: $scope.classObj.id
    },function(response){
        console.debug(response);
        $scope.files = response.data;
    }, function(response){
        console.debug(response);
    });
    
    $scope.collectImagePath = function(file, cropSize){
        return location.origin + '/' + file.folder + '/' + file.name + '_' + cropSize + '.' + file.ext;
    };
    
    $scope.getFilePath = function(file){
        return location.origin + '/' + file.folder + '/' + file.name + '.' + file.ext;
    }
    
    $scope.confirmUpload = function(){
        api.upload('upload_mod','add',{
            classID: $scope.classObj.id,
            filenames: $scope.uploadFiles,
            files: $('#upload-file')[0].files
        },function(response){
            console.debug(response);
            $scope.addFileForm = false;
            $scope.uploadFiles = [];
            if (!$scope.files){
                $scope.files = [];
            }
            $scope.files = $scope.files.concat(response.data.files);
        }, function(response){
            console.debug(response);
            //flib.alert('Ошибка', response.data);
        });
    };
    
    $scope.deleteFile = function(file){
        api.post('upload_mod', 'delete', {
            uploadID: file.id
        },function(response){
            $scope.files = flib.eject($scope.files, file);
        }, function(response){
            console.debug(response);
            //flib.alert('Ошибка', response.data);
        });
    };
    
    $scope.clear = function(){
        $scope.uploadFiles = [];
        $('#upload-file')[0].files = null;
    };
}]);