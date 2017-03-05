<?php
/**
 * JRP Utility Functions
 * 
 * @package  JRP
 * @since  1.0
 */

class JRPUtils
{
    /**
     * Verify that superglobal $_REQUEST array keys exist.
     * @param array $keys Array of $_REQUEST keys.
     * @return bool True if all listed keys exist.
     */
    static public function verify_request_array($keys)
    {
        $result = true;
        
        foreach ($keys as $key) {
            if (    isset($_REQUEST[$key])
                and ($_REQUEST[$key] != '')
            ) {
                $result &= true;
            } else {
                $result &= false;
            }
        }

        return $result;
    }


    /**
     * Print message in PRE HTML tag.
     * 
     * @param string $title String that will first be printed in PRE tag.
     * @param string $message String that will be printed in PRE tag.
     * @return void
     */
    static public function debug_print($title, $message)
    {
        echo("<pre>" . print_r($title, true) . ":<br>" . print_r($message, true) . "</pre>");
    }


    /**
     * Log any message to debug log file (only if debugging is enabled globaly
     * via JRP_DEBUG constant).
     *
     * @param string $message Message to be logged.
     * @param string $debug_log_path Alternative debug log path (optional).
     * @return void
     */
    static public function debug_log($message, $label = '', $debug_log_path = false)
    {
        if (JRP::DEBUG) {
            $output = 'file';
            if (JRP::DEBUG_OUTPUT) {
                $output = JRP::DEBUG_OUTPUT;
            }

            switch ($output) {

                case 'file' :
                    if ($debug_log_path === false) {
                        if (JRP::DEBUG_LOG_PATH) {
                            file_put_contents(
                                JRP::DEBUG_LOG_PATH,
                                ">>> " . date(DATE_RSS, time()) . " :: " . ($label ? "[{$label}] " : "") . print_r($message, true) . "\n",
                                FILE_APPEND
                            );
                        }
                    } else {
                        file_put_contents(
                            $debug_log_path,
                            ">>> " . date(DATE_RSS, time()) . " :: " . ($label ? "[{$label}] " : "") . print_r($message, true) . "\n",
                            FILE_APPEND
                        );
                    }
                    break;

                //case 'display' :
                default:
                    echo(">>> " . date(DATE_RSS, time()) . " :: " . ($label ? "[{$label}] " : "") . print_r($message, true) . "\n");
                    break;

            }
        }
    }


    /**
     * Echo jQuery Tools tooltip div with supplied contents.
     * @param  string $contents Contents to be displayed as tooltip.
     * @return void
     */
    static public function print_as_tooltip($contents)
    {
        ?>
        <div class="tooltip">
        <?php echo $contents; ?>
        </div>
        <?php
    }


    /**
     * Retrieve a page given its name.
     * @param  string $post_name Page name.
     * @param  string $output    (Optional) Output type. OBJECT, ARRAY_N, or ARRAY_A. Default OBJECT.
     * @param  string $post_type (Optional) Post type. Default page.
     * @return WP_Post|null       WP_Post on success or null on failure
     */
    static public function get_page_by_name($post_name, $output = OBJECT, $post_type = 'page')
    {
        global $wpdb;

        $sql = $wpdb->prepare(
            "SELECT
                ID
            FROM
                {$wpdb->posts}
            WHERE
                post_name = %s AND
                post_type = %s",
            $post_name,
            $post_type
        );
        $page = $wpdb->get_var($sql);

        if ($page) {
            return get_post($page, $output);
        }

        return null;
    }


    static public function parse_args_dbl($args, $defaults)
    {
        if (is_array($args)) {
            foreach ($args as $key => $value) {
                if (is_array($value) and isset($defaults[$key])) {
                    $args[$key] = wp_parse_args($args[$key], $defaults[$key]);
                }
            }

            $args = wp_parse_args($args, $defaults);
        }

        return $args;
    }


    static public function get_ip_address()
    {
        $ip = 'UNKNOWN'; 
        if (getenv("HTTP_CLIENT_IP")) 
            $ip = getenv("HTTP_CLIENT_IP"); 
        else if(getenv("HTTP_X_FORWARDED_FOR")) 
            $ip = getenv("HTTP_X_FORWARDED_FOR"); 
        else if(getenv("REMOTE_ADDR")) 
            $ip = getenv("REMOTE_ADDR");

        return $ip;
    }

}
