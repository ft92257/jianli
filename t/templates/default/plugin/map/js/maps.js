/*******************************************************************
 * [JishiGou] (C)2005 - 2099 Cenwor Inc.
 *
 * This is NOT a freeware, use is subject to license terms
 *
 * @Package JishiGou $
 *
 * @Filename maps.js $
 *
 * @Author http://www.jishigou.net $
 *
 * @Date 2012-04-23 17:49:36 72553805 868209939 1323 $
 *******************************************************************/


var geocoder;
var map;
var infowindow = null;
var marker = null;
function initialize() {
    geocoder = new google.maps.Geocoder();
    var myLatlng = new google.maps.LatLng(30.271799364972555, 120.16464829444885);
    var myOptions = {
        zoom: 12,
        center: myLatlng,
        disableDefaultUI: true,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    map = new google.maps.Map(document.getElementById("map_canvas"), myOptions)
}
function codeAddress(address, contentString) {
    geocoder.geocode({
        'address': address
    },
    function(results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
            map.setCenter(results[0].geometry.location);
            if (marker != null) {
                marker.setMap(null)
            }
            marker = new google.maps.Marker({
                map: map,
                position: results[0].geometry.location
            });
            if (infowindow != null) {
                infowindow.close()
            }
            infowindow = new google.maps.InfoWindow({
                content: contentString
            });
            infowindow.open(map, marker)
        } else {
            return false
        }
    })
}
onload = function() {
    initialize();
    automap()
}