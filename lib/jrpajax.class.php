<?php
/**
 * JRPAjax Class
 * 
 * @package  JRP
 * @since  1.0
 */
class JRPAjax
{
    /**
     * Initialize JRP AJAX functionality.
     * @return void
     */
    static public function init()
    {
        /** Add various AJAX actions and filters. */
        add_action(
            'wp_ajax_jrp_dismiss_message',
            array('JRPAjax', 'jrp_dismiss_message')
        );

    }


    static public function jrp_dismiss_message()
    {
        if (isset($_REQUEST['msg_slug']) and ($_REQUEST['msg_slug'] != '')) {

            update_option('jrp_hide_notice_' . $_REQUEST['msg_slug'], true);

            echo json_encode('ok');
            exit();
        }
    }

}