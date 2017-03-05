/**
 * JRP Javascript
 *
 * @package  JRP
 * @author  Predrag Bradaric
 * @since  1.0
 */

jQuery(document).ready(function() {

    // Image selection only available in WP 3.5+ (using new WP media library)
    if (typeof wp != "undefined") {
        // Select image
        jQuery(".postbox").on('click', ".choose-default-share-image", function(event) {
            event.preventDefault();

            // Redefine wp.media.editor.insert event
            wp.media.editor.insert = function(h) {
                var src;
                if (jQuery(h).is("img")) {
                    src = jQuery(h).attr("src");
                } else {
                    src = jQuery(jQuery(h).find("img")[0]).attr("src");
                }
                jQuery("input[name='share_settings[default_share_image]']").val(src);
                jQuery("#default-share-image").html("").append("<img src='" + src + "'>");
            }

            if (typeof wp.media.editor.get(3001) == "undefined") {
                wp.media.editor.add(3001, {multiple: false});
            }
            wp.media.editor.open(3001);
            return false;
        });
    }

});