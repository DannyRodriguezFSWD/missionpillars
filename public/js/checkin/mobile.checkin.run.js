$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

var app = angular.module('CheckInApplication', []);

app.controller('CheckIn', function ($scope, $http, $location, $timeout) {
    $('#overlay').hide();
    $scope.items = [];
    $scope.clear = function(){
        $scope.search = '';
        $('#search').val('').focus();
    };
    
    $scope.check = function(idx){
        $scope.items[idx].checked = 'checked';
        console.log( $scope.items[idx] );
    };
    
    var urlOn = $('input[name=url]').val();
    var urlOff = $('input[name=url]').val();
    
    function x(){
        $.get(urlOn, {id: idx}).done(function(data){
            $scope.items = data;
            $scope.$apply();
        }).fail(function(data){
            alert('Oops! something went wrong');
        });
    }

});