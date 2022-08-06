var map;
var infowindow;
var activeMarker;
var markersArray = [];
displayMap();
function displayMap(){
    map = new google.maps.Map(document.getElementById('map'), {
        center: {lat: 37.9779031, lng: 24.0136844},
        zoom: 12
    });
}
$(document).ready(function(){
    $(document).on('click', '.get', function(){
        clearMap();
        var btn = $(this);
        var originalText = btn.text();
        $.ajax({
            type: 'POST',
            url: 'get',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function(){
                btn.attr('disabled', 'disabled');
                btn.text('Please wait...');
            },
            complete: function(res){
                btn.removeAttr('disabled');
                btn.text(originalText);
                console.log(res.responseJSON);
                if ( res.responseJSON.schools ){
                    var data = res.responseJSON.schools ;
                    for(var i = 0; i < data.length; i++){
                        var marker = new google.maps.Marker({
                            position: new google.maps.LatLng(data[i].lat, data[i].lng),
                            map: map,
                            tag: data[i],
                        });
                        markersArray.push(marker);
                        setInfoWindow(marker);
                    }
                    //console.log(marker);
                    map.panTo(marker.position);
                } else{
                    toastr.error('An error ocurred!');
                }
                if ( res.responseJSON.categories ){
                    $('.category').show();
                    let html = '<option value="">Select a category</option>';
                    for(cat in res.responseJSON.categories){
                        html += '<option value="'+res.responseJSON.categories[cat]+'">'+res.responseJSON.categories[cat]+'</option>';
                    }
                    $('.category').html(html);
                }
            }
        });
    });
    function setInfoWindow(marker){
        google.maps.event.addListener(marker, 'click', function(e){
            activeMarker = marker;
            showInfoWindow();
        });
    }
    function showInfoWindow(){
        if ( activeMarker.tag ){
            if ( infowindow ) {
                infowindow.close();
            }
            infowindow = new google.maps.InfoWindow();
            let content = "<b style='font-size: 15px;'>"+activeMarker.tag.category+"</b>" + "<br/><br/>" + activeMarker.tag.address+ "<br/><br/>" + activeMarker.tag.info;
            infowindow.setContent(content);
            infowindow.open(map, activeMarker);
        }
    }
    function clearMap(){
        for (var i = 0; i < markersArray.length; i++ ) {
            markersArray[i].setMap(null);
        }
        markersArray.length = 0;
    }
    $(document).on('click', '.search', function(){
        var search = $.trim($('#search').val());
        var category = $.trim($('.category').val());
        var btn = $(this);
        clearMap();
        if ( search ){
            makeSearch(btn, search, category);
        }
    });
    $(document).on('change', '.category', function(){
        var category = $(this).val();
        var search = $.trim($('#search').val());
        var btn = $('.search');
        clearMap();
        if ( category ){
            makeSearch(btn, search, category);
        } else{
            $('.get').trigger('click');
        }
    });
    function makeSearch(btn, search, category){
        var originalText = btn.text();
        $.ajax({
            type: 'POST',
            url: 'search',
            data: {'search': search, 'category': category},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function(){
                btn.attr('disabled', 'disabled');
                btn.text('Please wait...');
            },
            complete: function(res){
                btn.removeAttr('disabled');
                btn.text(originalText);
                console.log(res.responseJSON);
                if ( res.responseJSON ){
                    if ( !res.responseJSON.length ){
                        toastr.warning('No results were found :(');
                        return;
                    }
                    var data = res.responseJSON;
                    toastr.success(data.length + ' results found');
                    for(var i = 0; i < data.length; i++){
                        var marker = new google.maps.Marker({
                            position: new google.maps.LatLng(data[i].lat, data[i].lng),
                            map: map,
                            tag: data[i],
                        });
                        markersArray.push(marker);
                        setInfoWindow(marker);
                    }
                    map.panTo(marker.position);
                } else{
                    toastr.error('An error ocurred!');
                }
            }
        });
    }

}); 