// 7.5.3-36-gea36ae7


//TODO: Convert this into a jQuery plugin

var addon_google_maps = {
    useMarkerIcon : false,

    markerIcon : {
        path: null,
        fillColor: "#77f",
        fillOpacity: 0.3,
        scale: 10,
        strokeColor: "black",
        strokeWeight: 3
    },

    defaultMapOptions : {
        zoom: 13,
        mapTypeId: null
    },

    setGoogleValues : function () {
        //Cannot set values based on google constants until document is done
        //loading, so cannot have it as part of the setting definition.  Set it
        //here instead (if not previously set)
        addon_google_maps.defaultMapOptions = addon_google_maps.defaultMapOptions || google.maps.MapTypeId.ROADMAP;
        addon_google_maps.markerIcon.path = addon_google_maps.markerIcon.path || google.maps.SymbolPath.BACKWARD_CLOSED_ARROW;
    },

    //give a way for other scripts to access the map after the fact
    mapHook : null,

    init : function (lat, longitude, location, canvas) {
        if (!lat || !longitude) {
            return;
        }
        addon_google_maps.setGoogleValues();

        //alert('lat: '+lat+' - long: '+longitude);

        var myLatlng = new google.maps.LatLng(lat, longitude);
        var mapOptions = addon_google_maps.defaultMapOptions;

        mapOptions.center = myLatlng;

        if (!mapOptions.mapTypeId) {
            //just to make sure it is set correctly
            mapOptions.mapTypeId = google.maps.MapTypeId.ROADMAP;
        }

        var map = new google.maps.Map(
            document.getElementById(canvas),
            mapOptions
        );

        addon_google_maps.mapHook = map;

        var markerOptions = {
            position: myLatlng,
            map: map//,
            //Do NOT do title at this point, since it's just a hover thingy.. Will need to re-visit later
            //title: '{$location|escape_js}'
        };
        if (addon_google_maps.useMarkerIcon) {
            addon_google_maps.markerIcon.path = google.maps.SymbolPath.BACKWARD_CLOSED_ARROW;
            markerOptions.icon = addon_google_maps.markerIcon;
        }

        var marker = new google.maps.Marker(markerOptions);
        //return the marker in case it is useful for custom stuff
        return marker;
    }
};
