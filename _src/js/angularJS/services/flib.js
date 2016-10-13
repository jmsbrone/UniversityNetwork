app.service('flib', function(){
    return {
        eject: function(arr, el){
            var newArr = [];
            for(var i=0;i<arr.length;++i){
                if (arr[i] == el) continue;
                newArr.push(arr[i]);
            }
            return newArr;
        },
        getSQLDate: function(t){
            return t.getFullYear() + '-' + (t.getMonth() + 1) + '-' + t.getDate() + ' ' + t.getHours() + ':' + t.getMinutes() + ':' + t.getSeconds();
        },
        findByField: function(arr, v, f){
            for(var i=0;i<arr.length;++i){
                if (arr[i][v] == f) return arr[i];
            }
            return null;
        },
        selectArrByField: function(arr1, field, arr2){
            var res = [];
            for(var k=0;k<arr2.length;++k){
                for(var i = 0;i<arr1.length;++i){
                    if (arr1[i][field] == arr2[k][field]) {
                        res.push(arr1[i]);
                        break;
                    }
                }
            }
            return res;
        }
    }
});