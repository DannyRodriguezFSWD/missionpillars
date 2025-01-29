$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

var app = angular.module('CheckInApplication', []);

app.controller('CheckIn', function ($scope, $http, $location, $timeout) {
    $scope.items = [];
    var autocomplete = $('input[name=autocomplete]').val();
    var tagsUrl = $('input[name=url]').val();
    
    $('a.folder').on('click', function(e){
        e.preventDefault();
        $('#overlay').show();
        var parent = $(this).parent('li');
        var found = parent.find('input.tag', this);
        var checked = $(found).prop('checked');
        $(found).prop('checked', !checked);
        var tags = [];
        
        var found = parent.find('input.tag:checked', this);
        $(found).each(function(el){
            tags.push($(this).attr('value'));
        });
        
        $.get(tagsUrl, {tags: tags}).done(function(data){
            $scope.items = data;
            $scope.$apply();
            $('ul.nav-tabs li.nav-item a.nav-link:first').trigger('click');
            $('#overlay').hide();
        }).fail(function(data){
            alert('Oops! something went wrong');
        });
        
    });
    
    $('input.tag').on('click', function(e){
        var tags = [];
        $('input.tag').each(function(e){
            var checked = $(this).prop('checked');
            if(checked){
                tags.push( $(this).val() );
            }
        });
        $.get(tagsUrl, {tags: tags}).done(function(data){
            $scope.items = data;
            $scope.$apply();
            $('#overlay').hide();
            if( confirm( data.length +' Contacts added\n¿Do you want to see contacts?' ) ){
                $('ul.nav-tabs li.nav-item a.nav-link:first').trigger('click');
            }
        }).fail(function(data){
            alert('Oops! something went wrong');
        });
    });
    
    $('input.form').on('click', function(e){
        var forms = [];
        $('input.form').each(function(e){
            var checked = $(this).prop('checked');
            if(checked){
                forms.push( $(this).val() );
            }
        });
        $.get(tagsUrl, {forms: forms}).done(function(data){
            $scope.items = data;
            $scope.$apply();
            $('#overlay').hide();
            if( confirm( data.length +' Contacts added\n¿Do you want to see contacts?' ) ){
                $('ul.nav-tabs li.nav-item a.nav-link:first').trigger('click');
            }
        }).fail(function(data){
            alert('Oops! something went wrong');
        });
    });

    $('#autocomplete').autocomplete({
        serviceUrl: autocomplete,
        onSelect: function (suggestion) {
            var idx = $scope.items.indexOf(suggestion);
            if( idx === -1 ){
                $scope.items.push( suggestion );
                $scope.$apply();
            }
            $('#autocomplete').val('');
        }
    });

    function test2() {
        $http.post($scope.folder.url.content, $scope.folder).then(function (result) {
            if (result.data !== false) {
                $scope.folder.folders = result.data.folders;
                $scope.folder.tags = result.data.tags;
            } else {
                $('#errorModal').modal();
            }
        });
    }

});