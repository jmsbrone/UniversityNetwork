app.directive('stretchDown', ['$timeout', function($timeout){
    return {
        restrict: 'A',
        link: function($scope, elem, attr){
            var waitFn = function(){
                if ($scope.$$phase == '$digest') {
                    $timeout(waitFn, 500);
                    return;
                }
                $(elem).height(10);
                var pageHeight = $('md-content[ui-view="header"]').height() + $('md-content[ui-view="body"]').height();
                var viewHeight = $(window).height();
                
                console.debug('page height: ' + pageHeight + ' - window:' + viewHeight);
                var newHeight = (viewHeight > pageHeight ? viewHeight : pageHeight)- $(elem).offset().top;
                $(elem).height(newHeight);
            }
            $timeout(waitFn, 500);
            $(window).resize(function(){
                waitFn();
            });
        }
    }
}]);