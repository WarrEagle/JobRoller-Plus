/**
 * JRP Javascript
 *
 * @package  JRP
 * @author  Predrag Bradaric
 * @since  1.0
 */

jQuery(document).ready(function(){

    // Init flash messages.
    FlashMessage.init();

    // Init tooltips
    jQuery(".rcm-tooltip img[title]").tooltip();

});


/**
 * Flash messages handling.
 * @return {Object} FlashMessage object singleton.
 */
var FlashMessage = new function() {

    /**
     * Initialize flash message events.
     * @return {void}
     */
    this.init = function () {
        // Close flash message functionality.
        jQuery("#flash-messages").on('click', ".close-message", function() {
            jQuery(this).parent().remove();
        });
    };

    /**
     * Display text in a flash message of specified type.
     * @param  {string} text Message to display.
     * @param  {integer} type Message type (error, warning, success, info).
     * @return {void}
     */
    this.show = function (text, type)
    {
        var content = '';
        switch (type) {
            case JRP.MSG_ERROR:
                content = '<div class="error jrp-msg-error js-generated-message">' +
                    '<span class="ui-icon ui-icon-alert" style="float:left"></span>' +
                    '&nbsp;<strong></strong>' +
                    text +
                    '<span class="ui-icon ui-icon-closethick close-message" style="float:right" title="Close message"></span>' +
                    '</div>';
                break;
            case JRP.MSG_WARNING:
                content = '<div class="updated jrp-msg-warning js-generated-message">' +
                    '<span class="ui-icon ui-icon-alert" style="float:left"></span>' +
                    '&nbsp;<strong></strong>' +
                    text +
                    '<span class="ui-icon ui-icon-closethick close-message" style="float:right" title="Close message"></span>' +
                    '</div>';
                break;
            case JRP.MSG_SUCCESS:
                content = '<div class="updated jrp-msg-success js-generated-message">' +
                    '<span class="ui-icon ui-icon-circle-check" style="float:left"></span>' +
                    '&nbsp;<strong></strong>' +
                    text +
                    '<span class="ui-icon ui-icon-closethick close-message" style="float:right" title="Close message"></span>' +
                    '</div>';
                break;
            case JRP.MSG_INFO:
            default:
                content = '<div class="updated jrp-msg-info js-generated-message">' +
                    '<span class="ui-icon ui-icon-info" style="float:left"></span>' +
                    '&nbsp;' +
                    text +
                    '<span class="ui-icon ui-icon-closethick close-message" style="float:right" title="Close message"></span>' +
                    '</div>';
                break;
        }
        jQuery("#flash-messages").append(content);
    };

    /**
     * Remove message from page.
     * @param  {boolean} all Whether to remove only locally generated messages (false) or all (true). Default false.
     * @return {void}
     */
    this.removeAll = function (all)
    {
        all = typeof all !== 'undefined' ? all : false;
        if (all) {
            jQuery("#flash-messages *").remove();
        } else {
            jQuery("#flash-messages .js-generated-message").remove();
        }
    };

}

