app.controller('managerController', ['$scope','api', 'flib', function($scope,api, flib){
    // Setup
    $scope.newDep = {};
    $scope.newProfile = {};
    $scope.newGroup = {};
    $scope.scheduleFilter = {};
    
    // Form containers.
    $scope.orderTimes = [{
        order: 1,
        time: '8:30 - 10:00'
    }, {
        order: 2,
        time: '10:15 - 11:45'
    }, {
        order: 3,
        time: '12:00 - 13:30'
    }, {
        order: 4,
        time: '14:00 - 15:30'
    }];

    // Getting departments.
    api.get('dep_mod', 'list', {})
    .then(function(response){
        console.debug('departments');
        console.debug(response);
        $scope.departments = response.data;
    }, function(response){
        console.debug(response);
    });
    
    // Getting subjects.
    api.get('subject_mod', 'list', {})
    .then(function(response){
        console.debug('subjects');
        console.debug(response);
        $scope.subjects = response.data;
    }, function(response){
        console.debug(response);
    });
    
    // Getting semesters.
    api.get('semester_mod', 'list', {}).then(function(response){
        console.debug('semesters');
        console.debug(response);
        $scope.semesters = response.data;
        for(var i=0;i<$scope.semesters.length;++i){
            $scope.semesters[i].startTimestamp = new Date($scope.semesters[i].startTimestamp * 1000);
            $scope.semesters[i].endTimestamp = new Date($scope.semesters[i].endTimestamp * 1000);
        }
    }, function(response){
        console.debug(response);
    });
    
    // Getting rooms.
    api.get('room_mod', 'list', {}).then(function(response){
        console.debug('rooms');
        console.debug(response);
        $scope.rooms = response.data;
    }, function(response){
        console.debug(response);
    });
    
    // Watchers.
    $scope.$watch('activeDep', function(oldValue, newValue){
        $scope.activeProfile = null;
        $scope.activeGroup = null;
        
        if ($scope.activeDep== null) return;
        api.get('prof_mod', 'list', {
            depID: $scope.activeDep.id
        }).then(function(response){
            console.debug(response);
            $scope.activeDep.profs = response.data;
        }, function(response){
            console.debug(response);
        });
        
        api.get('profile_mod', 'list', {
            depID: $scope.activeDep.id
        }).then(function(response){
            console.debug(response);
            $scope.activeDep.profiles = response.data;
        }, function(response){
            console.debug(response);
        });
    });
    $scope.$watch('activeProfile', function(){
        $scope.activeGroup = null;
        
        if ($scope.activeProfile == null) return;
        api.get('group_mod', 'list', {
            profileID: $scope.activeProfile.id
        }).then(function(response){
            console.debug(response);
            $scope.activeProfile.groups = response.data;
        }, function(response){
            console.debug(response);
        });
    });
    $scope.$watch('activeGroup', function(){
        if ($scope.activeGroup== null) return;
        api.get('grouplist_mod', 'list', {
            groupID: $scope.activeGroup.id
        }).then(function(response){
            console.debug(response);
            $scope.activeGroup.students = response.data;
        }, function(response){
            console.debug(response);
        });
    });

    // Groups.
    
    $scope.showGroupForm = function(){
        $scope.groupForm = true;
    };
    
    $scope.addProfile = function(){
        var dep = $scope.activeDep;
        if ($scope.newProfile.name.length < 10) return;
        api.get('profile_mod', 'add', {
            name: $scope.newProfile.name,
            depID: dep.id,
            shortName: $scope.newProfile.short
        })
        .then(function(response){
            console.debug(response);
            if (typeof($scope.activeDep.profiles) == 'undefined') $scope.activeDep.profiles = [];
            $scope.activeDep.profiles.push(response.data);
            $scope.profileForm = false;
            $scope.newProfile = {};
        }, function(response){
            console.debug(response);
        });
    };
  
    $scope.expandProfile = function(profile){
        $scope.activeProfile = profile;
        if (typeof(profile.groups) == 'undefined'){
            api.get('group_mod', 'list', {
                profileID : profile.id
            })
            .then(function(response){
                console.debug('gettin group list for '+profile.id);
                console.debug(response);
                profile.groups = response.data;
            }, function(response){
                console.debug(response);
            });
        }
    };
    
    $scope.deleteProfile = function(){
        api.get('profile_mod', 'delete',{
            profileID: $scope.activeProfile.id
        })
        .then(function(response){
            console.debug(response);
            $scope.activeDep.profiles = flib.eject($scope.activeDep.profiles, $scope.activeProfile);
            $scope.activeProfile = null;
        }, function(response){
            console.debug(response);
        });
    };
    
    // Groups.
    
    $scope.manageGroup = function(group){
        $scope.activeGroup = group;
        
        // Grouplist
        api.get('grouplist_mod','list',{
            groupID : group.id
        })
        .then(function(response){
            console.debug('grouplist for ' + group.id);
            console.debug(response);
            $scope.activeGroup.students = response.data;
        }, function(response){
            console.debug(response);
        });
    };
    
    $scope.addGroup = function(){
        if (typeof($scope.newGroup.year) == 'undefined') return;
      	var year = $scope.newGroup.year.toString();
        api.get('group_mod', 'add', {
            profileID : $scope.activeProfile.id,
            name: $scope.activeProfile.short + '-' + year[2] + year[3],
            year: year
        })
        .then(function(response){
            console.debug('adding group');
            console.debug(response);
            $scope.groupForm = false;
            $scope.newGroup = {};
            if (typeof($scope.activeProfile.groups) == 'undefined'){
                $scope.activeProfile.groups = [];
            }
            $scope.activeProfile.groups.push(response.data);
        }, function(response){
            console.debug(response);
        });
    };
    
    $scope.deleteGroup = function(group){
        api.get('group_mod', 'delete', {
            groupID: group.id
        }).then(function(response){
            console.debug(response);
            $scope.activeProfile.groups = flib.eject($scope.activeProfile.groups, group);
            $scope.activeGroup = null;
        }, function(response){
            console.debug(response);
        });
    }
    
    // Grouplist.
    
    $scope.addStudent = function(){
       api.get('grouplist_mod', 'add', {
           groupID: $scope.activeGroup.id,
           surname: $scope.newStudent.surname,
           name: $scope.newStudent.name,
           lastname: $scope.newStudent.lastname
       }).then(function(response){
           console.debug(response);
           $scope.activeGroup.students.push(response.data);
           $scope.newStudent = {};
           $scope.studentForm = false;
       }, function(response){
           console.debug(response);
       });
    }
    
    $scope.setPresident = function(student){
        api.get('grouplist_mod', 'pr_change',{
            groupID: $scope.activeGroup.id,
            studentID: student.id
        }).then(function(response){
            console.debug(response);
            if (response.data.status){
                $scope.activeGroup.presID = student.id;
            }
        }, function(response){
            console.debug(response);
        });
    }
    
    $scope.deleteStudent = function(student){
        api.get('grouplist_mod', 'delete',{
            studentID: student.id
        }).then(function(response){
            console.debug(response);
            var students = [];
            for(var i=0;i<$scope.activeGroup.students.length;++i){
                if ($scope.activeGroup.students[i].id == student.id) continue;
                students.push($scope.activeGroup.students[i]);
            }
            $scope.activeGroup.students = students;
        }, function(response){
            console.debug(response);
        });
    }
    
    // Program.
    
    $scope.addProgram = function(){
        api.get('program_mod', 'add', {
            groupID: $scope.activeGroup.id,
            semesterID: $scope.chosenSemester.id,
            subjectID: $scope.newProgramNote.subjectID,            
            examType: $scope.newProgramNote.examType,
            profID: $scope.newProgramNote.prof.id
        }).then(function(response){
            console.debug('program_mod_add: ');
            console.debug(response);
            $scope.activeGroup.program.push(response.data);
            $scope.newProgramNote = {};
        }, function(response){
            console.debug(response);
        });
    };
    
    $scope.deleteProgram = function(program){
        api.get('program_mod', 'delete', {
            programID: program.id
        }).then(function(response){
            console.debug(response);
            $scope.activeGroup.program = flib.eject($scope.activeGroup.program, program);
        }, function(response){
            console.debug(response);
        });
    };
    
    $scope.onSemesterSelect = function(){
        // Program
        api.get('program_mod', 'list', {
            groupID: $scope.activeGroup.id,
            semesterID: $scope.chosenSemester.id
        }).then(function(response){
            console.debug('program_mod_list: ');
            console.debug(response);
            $scope.activeGroup.program = response.data;
        }, function(response){
            console.debug(response);
        });
        api.get('prof_mod', 'list', {}).then(function(response){
            console.debug(response);
            $scope.profs = response.data;
        }, function(response){
            console.debug(response);
        });
    };
    
    // Subjects.
    
    $scope.addSubject = function(){
        api.get('subject_mod', 'add',{
            name: $scope.newSubject.name
        }).then(function(response){
            console.debug(response);
            $scope.subjects.push(response.data);
            $scope.subjectForm = false;
            $scope.newSubject = {};
        }, function(response){
            console.debug(response);
        });
    };
    
    $scope.deleteSubject = function(subject){
        api.get('subject_mod', 'delete',{
            subjectID: subject.id
        }).then(function(response){
            console.debug(response);
            $scope.subjects = flib.eject($scope.subjects, subject);
        }, function(response){
            console.debug(response);
        });
    };
    
    // Departments.
    
    $scope.addDep = function(){
        api.get('dep_mod', 'add', {
            name: $scope.newDep.name
        }).then(function(response){
            console.debug(response);
            if (typeof($scope.departments) == 'undefined') $scope.departments = [];
            $scope.departments.push(response.data);
            $scope.depForm = false;
            $scope.newDep = {};
        }, function(response){
            console.debug(response);
        });
    };
    
    $scope.manageDep = function(dep){
        $scope.activeDep = dep;
        
        if (typeof(dep.profs) == 'undefined'){
            api.get('prof_mod', 'list',{
                depID: dep.id
            }).then(function(response){
                console.debug(response);
                dep.profs = response.data;
            }, function(response){
                console.debug(response);
            });
            api.get('profile_mod', 'list',{
                depID: dep.id
            }).then(function(response){
                console.debug(response);
                dep.profiles = response.data;
            }, function(response){
                console.debug(response);
            });
        }
    };
    
    $scope.deleteDep = function(){
        api.get('dep_mod', 'delete', {
            depID: $scope.activeDep.id
        }).then(function(response){
            console.debug(response);
            $scope.departments = flib.eject($scope.departments, $scope.activeDep);
            $scope.activeDep = null;
        }, function(response){
            console.debug(response);
        });
    };
    
    // Profs.
    
    $scope.addProf = function(){
        api.get('prof_mod', 'add',{
            depID: $scope.activeDep.id,
            surname: $scope.newProf.surname,
            name: $scope.newProf.name,
            lastname: $scope.newProf.lastname
        }).then(function(response){
            console.debug(response);
            if (typeof($scope.activeDep.profs) == 'undefined') $scope.activeDep.profs = [];
            $scope.activeDep.profs.push(response.data);
            $scope.profForm = false;
            $scope.newProf = {};
        }, function(response){
            console.debug(response);
        });
    };
    
    $scope.deleteProf = function(prof){
        api.get('prof_mod', 'delete', {
            profID: prof.id
        }).then(function(response){
            console.debug(response);
            $scope.activeDep.profs = flib.eject($scope.activeDep.profs, prof);
        }, function(response){
            console.debug(response);
        });
    };
    
    // Rooms.
    $scope.addRoom = function(){
        api.get('room_mod', 'add', {
            name: $scope.newRoom.name
        }).then(function(response){
            console.debug(response);
            $scope.rooms.push(response.data);
            $scope.newRoom = {};
        }, function(response){
            console.debug(response);
        });
    };
    
    $scope.deleteRoom = function(room){
        api.get('room_mod', 'delete', {
            roomID: room.id
        }).then(function(response){
            console.debug(response);
            $scope.rooms = flib.eject($scope.rooms, room);
        }, function(response){
            console.debug(response);
        });
    };
    // Semesters.
    
    $scope.addSemester = function(){
        api.get('semester_mod', 'add', {
            year: $scope.newSemester.year,
            season: $scope.newSemester.season,
            startTime: flib.getSQLDate($scope.newSemester.startTimestamp),
            endTime: flib.getSQLDate($scope.newSemester.endTimestamp)
        }).then(function(response){
            console.debug(response);
            $scope.semesters.push($scope.newSemester);
            $scope.semesterForm = false;
            $scope.newSemester = {};
        }, function(response){
            console.debug(response);
        });
    };
    
    $scope.deleteSemester = function(semester){
        api.get('semester_mod', 'delete', {
            semesterID: semester.id
        }).then(function(response){
            console.debug(response);
            $scope.semesters = flib.eject($scope.semesters, semester);
        }, function(response){
            console.debug(response);
        });
    };
    
    // Wrapper-functions.
    $scope.getSubjectName = function(id){
        for(i = 0; i< $scope.subjects.length;++i){
            if ($scope.subjects[i].id == id) return $scope.subjects[i].name;
        }
    };
    
    $scope.subjectInProgram = function(subject){
        if (!$scope.activeGroup || !$scope.chosenSemester || !$scope.activeGroup.program) return;
        for(var i=0;i<$scope.activeGroup.program.length;++i){
            if ($scope.activeGroup.program[i].subjectID == subject.id) return true;
        }
        return false;
    };
    
    
    // Rules.
    $scope.addNewRule = function(){
        $scope.ruleForm = true;
        api.get('prof_mod', 'list',{}).then(function(response){
            console.debug(response);
            $scope.profs = response.data;
        }, function(response){
            console.debug(response);
        });
        $scope.newRule = {
            groups: [],
            rooms: [],
            profs: []
        };
    };
    
    $scope.getRules = function(){
        api.get('schedule_mod', 'list',{
            subjectID: $scope.scheduleFilter.subject.id
        }).then(function(response){
            console.debug('rules for subject');
            console.debug(response);
            $scope.rulesSelection = response.data;
        }, function(response){
            console.debug(response);
        });
    };
    
    $scope.deleteRule = function(rule){
        api.get('schedule_mod', 'delete', {
            ruleID: rule.id
        }).then(function(response){
            console.debug(response);
            $scope.rules = flib.eject($scope.rules, rule);
        }, function(response){
            console.debug(response);
        });
    };
    
    $scope.modifyRule = function(rule){
        $scope.ruleForm = true;
        
        $scope.newRule = rule;
        $scope.newRule.subject = flib.findByField($scope.subjects, 'id', rule.subjectID);
        $scope.newRule.prototype = rule;
        $scope.newRule.classes = $scope.generateDates();
                
        api.get('prof_mod', 'list',{}).then(function(response){
            console.debug(response);
            $scope.profs = response.data;
            $scope.newRule.profs=flib.selectArrByField($scope.profs, 'id', $scope.newRule.prototype.profs);
        }, function(response){
            console.debug(response);
        });
        api.get('group_mod', 'program_list', {
            semesterID: $scope.scheduleFilter.semester.id,
            subjectID: $scope.newRule.subject.id
        }).then(function(response){
            console.debug(response);
            $scope.groups = response.data;
            $scope.newRule.groups = flib.selectArrByField($scope.groups, 'id', $scope.newRule.prototype.groups);
        }, function(response){
            console.debug(response);
        });
        $scope.newRule.rooms = flib.selectArrByField($scope.rooms, 'id', $scope.newRule.prototype.rooms);
    };
    
    $scope.addRule = function(){
        var groups = [];
        var classes = [];
        var profs = [];
        var rooms = [];
        
        try{
            for(var i = 0; i< $scope.newRule.groups.length;++i){
                groups.push($scope.newRule.groups[i].id);
            }
        } catch(err){}
        try{
            for(var i=0;i<$scope.newRule.rooms.length;++i){
                rooms.push($scope.newRule.rooms[i].id);
            }
        } catch(err){}
        try{
            for(var i=0;i<$scope.newRule.profs.length;++i){
                profs.push($scope.newRule.profs[i].id);
            }
        } catch(err){}
        try{
            for(var i=0;i<$scope.newRule.classes.length;++i){
                classes.push(flib.getSQLDate($scope.newRule.classes[i]));
            }
        } catch(err){}
        
        var modify = typeof($scope.newRule.prototype) != 'undefined';
        if (modify) {
            var ruleID = $scope.newRule.prototype.id;
        }
        
        api.post('schedule_mod', modify ? 'modify' : 'add',{
            ruleID: ruleID,
            rooms_id: rooms,
            profs_id: profs,
            groups_id: groups,
            dates: classes,
            subjectID: $scope.newRule.subject.id,
            weekDay: $scope.newRule.weekDay,
            weekType: $scope.newRule.weekType,
            classType: $scope.newRule.classType,
            subgroup: $scope.newRule.subgroup != '' ? $scope.newRule.subgroup : null,
            order: $scope.newRule.order
        }).then(function(response){
            console.debug(response);
        }, function(response){
            console.debug(response);
        });
    };
    
    $scope.onRuleSubjectChanged = function(){
        api.get('group_mod', 'program_list', {
            semesterID: $scope.scheduleFilter.semester.id,
            subjectID: $scope.newRule.subject.id
        }).then(function(response){
            console.debug(response);
            $scope.groups = response.data;
            $scope.newRule.groups = [];
        }, function(response){
            console.debug(response);
        });
    };
    
    $scope.appendGroupToRule = function(){
        $scope.newRule.groups.push($scope.chosenGroup);
        $scope.chosenGroup = null;
    };
    $scope.appendProfToRule = function(){
        $scope.newRule.profs.push($scope.chosenProf);
        $scope.chosenProf = null;
    };
    $scope.appendRoomToRule = function(){
        $scope.newRule.rooms.push($scope.chosenRoom);
        $scope.chosenRoom = null;
    };
    
    $scope.removeGroupFromList = function(group){
        $scope.newRule.groups = flib.eject($scope.newRule.groups, group);
    };
    $scope.removeProfFromList = function(prof){
        $scope.newRule.profs = flib.eject($scope.newRule.profs, prof);
    };
    $scope.removeRoomFromList = function(room){
        $scope.newRule.rooms = flib.eject($scope.newRule.rooms, room);
    };
    
    $scope.resetClasses = function(){
        $scope.newRule.classes = $scope.generateDates();
    };
    
    $scope.generateDates = function(){
        if (!$scope.scheduleFilter.semester || !$scope.newRule || !$scope.newRule.order || !$scope.newRule.weekDay || !$scope.newRule.weekType) return null;
        var init_date = new Date($scope.scheduleFilter.semester.startTimestamp.valueOf());
        switch($scope.newRule.order){
            case 1: case '1': init_date.setHours(8); init_date.setMinutes(30); break;
            case 2: case '2': init_date.setHours(10); init_date.setMinutes(15); break;
            case 3: case '3': init_date.setHours(12); init_date.setMinutes(00); break;
            case 4: case '4': init_date.setHours(14); init_date.setMinutes(00); break;
            case 5: case '5': init_date.setHours(15); init_date.setMinutes(45); break;
        }
        var week = 1;
        if (init_date.getDay() > $scope.newRule.weekDay) {
            init_date.setDate(init_date.getDate() + (7 - (init_date.getDay() - $scope.newRule.weekDay)));
            week++;
        } else {
            init_date.setDate(init_date.getDate() + ($scope.newRule.weekDay - init_date.getDay()));
        }
        var step = 50;
        switch(parseInt($scope.newRule.weekType)){
            case 4:
                step = 1; 
                break;
            case 2: 
            case 3: 
                step = 2; 
                if ($scope.newRule.weekType == 3 && week == 2){
                    week++;
                    init_date.setDate(init_date.getDate() + 7);
                } else if ($scope.newRule.weekType == 2 && week == 1){
                    week++;
                    init_date.setDate(init_date.getDate() + 7);
                }
                break;
            case 11:
                step = 4;
                var n = 5 - week;
                week += n;
                init_date.setDate(init_date.getDate() + n*7);
            case 12:
                step = 4;
                if (week != 2){
                    week++;
                    init_date.setDate(init_date.getDate() + 7);
                }
            case 13: 
                step = 4;
                var n = 3 - week;
                week += n;
                init_date.setDate(init_date.getDate() + n*7);
                break;
            case 14: 
                step = 4;
                var n = 4 - week;
                week += n;
                init_date.setDate(init_date.getDate() + n*7);
                break;
        }
        var start_times = [];
        var i = 0;
        while(week < 18 && i++ < 100){
            start_times.push(new Date(init_date.valueOf()));
            week+=step;
            init_date.setDate(init_date.getDate() + step * 7);
        }
        return start_times;
    };
}]);