/**
 * Jobroller Theme Fixes
 * 
 * @package Jobroller Plus
 * @since 1.0
 */

var jrp_geocoder;
var jrp_latlng;
var jrp_address;

jQuery(document).ready(function(){
    jQuery("input[name='your_name']").addClass("required");
    jQuery("input[name='jr_address']").addClass("required");

    var p = jQuery("input[name='jr_address']").closest("fieldset").find("p:first");
    p.html(p.html() + " Make sure you click find address after you have entered the location. - See more at: http://jobshouts.com/submit-job/#sthash.MwaGEklA.dpuf");

    jrp_wait_for_google('jrp_geolocate');


    // jQuery("input[name='job_submit']").click(function(event) {

        // var name         = jQuery("input[name='your_name']");
        // var location     = jQuery("input[name='jr_address']");

        // var name_val     = jQuery.trim(name.val());
        // var location_val = jQuery.trim(location.val());

        // if ((name_val == '') || (location_val == '')) {

        //     var scroll = true;
            
        //     if (name_val == '') {
        //         location.parent().find("label.error").remove();
        //         name.parent().append('<label for="post_title" generated="true" class="error" style=""><img src="' + JRP.stop_round_image + '" style="float:left; margin-top:2px;">&nbsp;This field is required.</label>');
        //         name.addClass('error');
        //         var name_pos = name.position(); 
        //         jQuery('html, body').animate({ 
        //             scrollTop: name_pos.top 
        //         }, 400); 
        //         scroll = false;
        //     } 

        //     if (location_val == '') {
        //         location.parent().find("label.error").remove();
        //         location.parent().append('<label for="post_title" generated="true" class="error" style=""><img src="' + JRP.stop_round_image + '" style="float:left; margin-top:2px;">&nbsp;This field is required.</label>');
        //         location.addClass('error');
        //         if (scroll) {
        //             var location_pos = location.position(); 
        //             jQuery('html, body').animate({ 
        //                 scrollTop: location_pos.top 
        //             }, 400); 
        //             scroll = false;
        //         }
        //     }

        //     event.preventDefault();
        //     return false;
        // }
    // });

});


function jrp_wait_for_google(action)
{
    if ((typeof google != "undefined") && (typeof google.maps != "undefined")) {
        console.log('calling ' + action);
        window[action]();
    } else {
        setTimeout('jrp_wait_for_google("' + action + '")', 100);
        console.log('waiting for google... ' + action);
    }
}

function jrp_geolocate()
{
    if ((typeof google != "undefined") && (typeof google.maps != "undefined")) {
        jQuery("input[name='jr_address']").val(JRP.location);
        jQuery("input#geolocation-load").click();
        navigator.geolocation.getCurrentPosition(function(position) {
            jrp_latlng = new google.maps.LatLng(position.coords.latitude,position.coords.longitude);
            jrp_geocoder = new google.maps.Geocoder();
            jrp_geocoder.geocode({'latLng': jrp_latlng}, function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    if (results[1]) {
                        jrp_address = results[1].formatted_address;
                        console.log(jrp_address);
                        jQuery("input[name='jr_address']").val(jrp_address);
                        jQuery("input#geolocation-load").click();
                    } else {
                        console.log("no location");
                    }
                } else {
                    console.log('Geocoder failed due to: ' + status);
                }
            });
        });
    } else {
        console.log('no google');
    }

}