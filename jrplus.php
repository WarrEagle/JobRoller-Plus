<?php
/*
Plugin Name: JRPlus
Description: JRPlus is a plugin that extends Jobroller theme functionality by adding API for data access, advanced RSS feed, etc.
Plugin URI:  http://jobshouts.com
Version:     1.0-dev1
Author:      Predrag Bradaric
Author URI:  http://bradaric.com
*/

if (!defined('__JRP_NAME__'))
    define('__JRP_NAME__', plugin_basename(__FILE__));
if (!defined('__JRP_PATH__'))
    define('__JRP_PATH__', dirname(__FILE__));
if (!defined('__JRP_LIB_PATH__'))
    define('__JRP_LIB_PATH__', __JRP_PATH__ . '/lib');
if (!defined('__JRP_RECHECK_PATH__'))
    define('__JRP_RECHECK_PATH__', '?page=' . __JRP_NAME__ . '&rcmSubmitKey=Save%20Key');
if (!defined('__JRP_PLUGIN_URL__'))
    define('__JRP_PLUGIN_URL__', plugin_dir_url(__FILE__));

if (!class_exists('JRP')) {
    /**
     * JRP plugin "main" class.
     *
     * @package  JRP
     * @since  1.0
     */
    class JRP
    {
        /** URL to check for plugin updates. */
        const UPDATE_API_URL          = 'http://localhost/includes/update_check.php';
        /** Plugin name (used in WP backend). */
        const NAME                    = __JRP_NAME__;
        /** Plugin product name. */
        const PRODUCT                 = 'jrp';
        /** Plugin title (used in UI frontend). */
        const TITLE                   = 'JRPlus';
        /** Plugin filesystem path. */
        const PATH                    = __JRP_PATH__;
        /** Plugin libraries path. */
        const LIB_PATH                = __JRP_LIB_PATH__;
        /** Plugin dir URL. */
        const PLUGIN_URL              = __JRP_PLUGIN_URL__;
        /** Plugin version number (most of the plugin UI scripts use this as attribute) */
        const VERSION                 = '1.0-dev1';
        /** Database log table name (used for various messages/status logging). */
        const LOG_TABLE               = 'jrp_log';
        /** Database cache table name (used for shortcodes caching). */
        const CACHE_TABLE             = 'jrp_cache';
        /** Feeds global enable/disable. */
        const FEED_GLASSDOOR          = true;
        const FEED_SIMPLYHIRED        = true;
        const FEED_JUJU               = true;
        const FEED_TWITJOBSEARCH      = true;
        /** Debugging options. */
        const DEBUG                   = true;
        const DEBUG_OUTPUT            = 'file';
        const DEBUG_LOG_PATH          = '/tmp/jrp-debug.log';
        /** Flash messages types constants (error, warning, success, etc.). */
        const MSG_ERROR               = 1;
        const MSG_WARNING             = 2;
        const MSG_SUCCESS             = 3;
        const MSG_INFO                = 4;

        /** @var array List of wp_options items used in the plugin. */
        static public $options = array(
            'jrp_version'          => false,
        );

        /** @var array Array of various plugin tooltips. */
        static public $tooltips = array(
            "tooltip_name" => "<p>Tooltip text.</p>",
        );

        /** @var array Array of various plugin messages. */
        static public $faqs = array(
        );

        static public $default_api_settings = array(
        );

        static public $default_feed_settings = array(
            'glassdoor' => array(
                'enabled' => true,
                'publisher' => '',
                'publisherurl' => '',
            ),
            'simplyhired' => array(
                'enabled' => true,
                'source' => '',
                'sourceurl' => '',
            ),
            'juju' => array(
                'enabled' => true,
                'source' => '',
                'sourceurl' => '',
            ),
            'twitjobsearch' => array(
                'enabled' => true,
                'publisher' => '',
                'publisherurl' => '',
            ),
        );

        static public $default_share_settings = array(
            'enable_share_buttons_in_live_jobs_table' => false,
            'default_share_image' => '',
        );

        /**
         * Messages array. These messages will be displayed
         * at the top of the plugin page.
         * Example:
         *     Array(
         *         1 => Array(
         *             'type' => JRP::MSG_ERROR,
         *             'text' => 'Some error occurred!',
         *             'display' => 'global'
         *         ),
         *         2 => Array(
         *             'type' => JRP::MSG_SUCCESS,
         *             'text' => 'Some success message.',
         *             'display' => 'local'
         *         ),
         *     )
         * @var array
         */
        static public $flash_messages = array();


        /**
         * Register various actions and filters.
         * Init various globals.
         * @return void
         */
        static public function init()
        {
            /**
             * Log initialization.
             */
            // JRP::$log = new MDLog(JRP::PRODUCT, JRP::LOG_TABLE);

            /**
             * Hooks and filters
             */
            register_activation_hook(
                __FILE__,
                array('JRP', 'activate')
            );
            register_deactivation_hook(
                __FILE__,
                array('JRP', 'deactivate')
            );

            add_action(
                'admin_menu',
                array('JRP', 'add_admin_menu'),
                2
            );
            add_action(
                'admin_enqueue_scripts',
                array('JRP', 'add_admin_scripts'),
                10, 1
            );
            add_action(
                'admin_notices',
                array('JRP', 'show_notices')
            );

            add_filter(
                'plugin_row_meta',
                array('JRP', 'set_plugin_meta'),
                10, 2
            );
            add_filter(
                'http_request_args',
                array('JRP', 'no_updates'),
                5, 2
            );

            add_action(
                'wp_enqueue_scripts',
                array('JRP', 'add_scripts'),
                10, 1
            );

            add_action(
                'admin_head',
                array('JRP', 'add_to_admin_head'),
                10, 1
            );

            /**
             * Handle checks for third party plugins
             */
            add_action(
                'admin_init',
                array('JRP', 'check_third_party_plugins')
            );

            /**
             * Handle job activation
             */
            add_action(
                'pending_to_publish',
                array('JRP', 'handle_accepted_job')
            );

            /**
             * Handle mortal AJAX requests
             */
            add_action(
                'wp',
                array('JRP', 'ajax_handler'),
                1
            );

            /**
             * Handle RSS feed requests
             */
            add_action(
                'wp',
                array('JRP', 'rss_feed_handler'),
                1
            );

            /**
             * Initialize share buttons in Live Jobs table
             */
            add_action(
                'jr_dashboard_tab_after',
                array('JRP', 'live_jobs_share_buttons'),
                10, 1
            );

            /**
             *  Auto update
             */
            // Take over the update check
            add_filter(
                'pre_set_site_transient_update_plugins', 
                array('JRP', 'check_for_plugin_update')
            );
            // Take over the Plugin info screen
            add_filter(
                'plugins_api', 
                array('JRP', 'plugin_api_call'), 
                10, 3
            );

            if (isset($_REQUEST['action'])) {
                switch ($_REQUEST['action']) {
                    case '_reinit_':
                        JRP::activate();
                        break;
                    default:
                        break;
                }
            }
            
        }


        /**
         * Run on JRP plugin activation.
         * @return void
         */
        static public function activate()
        {
            global $wpdb;

            // Create plugin database tables.
            JRP::create_tables();

            // Delete hide admin notices flags
            delete_option("jrp_hide_notice_wpbitly");
            delete_option("jrp_hide_notice_wptotwitter");

            // Create WP API docs page.
            if (!($page_id = get_option('jrp_api_docs_page_id')) or (isset($_REQUEST['action']) and ($_REQUEST['action'] == '_reinit_'))) {

                $content = '<h4>Would you like to display our latest jobs on your site?</h4>
<p>Insert one of the following snippets in your page\'s HTML code, in the position where the ads should appear.<br><br></p>
<ol>
    <li> Get latest 5 jobs from all categories and all types, posted in the past 7 days, in random order: <br><br>
        <pre>&lt;script src="' . site_url("api?action=getJobs") . '<br>&amp;type=0&amp;category=0&amp;count=5&amp;random=1&amp;days_behind=7&amp;response=js" type="text/javascript"&gt;<br>&lt;/script&gt;<br><br>&lt;script type="text/javascript"&gt;<strong>showJobs(\'jobber-container\', \'jobber-list\');</strong>&lt;/script&gt;
        </pre>
    </li>
    <br>
    <li>Get the last 10 full-time jobs for programmers, posted in the past 15 days, ordered by publish date (newest on top):
        <br><br>
        <pre>&lt;script src="' . site_url("api?action=getJobs") . '<br>&amp;type=fulltime&amp;category=programmers&amp;count=10&amp;random=0&amp;days_behind=15&amp;response=js" <br>type="text/javascript"&gt;&lt;/script&gt;<br><br>&lt;script type="text/javascript"&gt;<strong>showJobs(\'jobber-container\', \'jobber-list\');</strong>&lt;/script&gt;</pre>
        <br>&nbsp;&nbsp;
    </li>
    <li> Get latest jobs published by a company (e.g. Google):
        <br><br>
        <pre>&lt;script src="' . site_url("api?action=getJobsByCompany") . '<br>&amp;company=google&amp;count=10&amp;response=js" type="text/javascript"&gt;&lt;/script&gt;<br><br>&lt;script type="text/javascript"&gt;<strong>showJobs(\'jobber-container\', \'jobber-list\');</strong>&lt;/script&gt;</pre>
    </li>
</ol>
<p>&nbsp;</p>
<h2>The parameters you can use when calling the API, are:</h2>
<ul>
    <li><strong>action</strong>: "getJobs" - all jobs / "getJobsByCompany" - a single company\'s jobs</li>
    <li><strong>type</strong>: "0" - job-type / "fulltime" / "parttime" / "freelance";</li>
    <li><strong>category</strong>: "0" - all / "programmers" / "designers" / "administrators" / "managers" / "testers" / "editors";</li>
    <li><strong>count</strong>: number of job ads to display;</li>
    <li><strong>random</strong>: "1" - display randomly / "0" - display ordered by publish date (newest on top);</li>
    <li><strong>days_behind</strong>: get only jobs posted in the past X days (type "0" if you don\'t want to limit this);</li>
    <li><strong>response</strong>: "js" - returns jobs as JavaScript code / "json" - returns only a JSON string / "xml" - returns an XML.</li>
</ul>
<h2>Use CSS to style the list:</h2>
<pre>ul.jobber-list {<br>  list-style-type: none;<br>  margin: 0;<br>  padding: 0;<br>}<br>ul.jobber-list li {<br>  margin-bottom: 5px;<br>}</pre>';
                $page = JRPUtils::get_page_by_name("api-docs");

                if ($page !== null) {
                    // Page exists, just update it's content.
                    $new_page = array(
                        'ID'             => $page->ID,
                        'post_title'     => 'Our API',
                        'post_content'   => $content,
                        'post_author'    => '1',
                        'post_type'      => 'page',
                        'post_status'    => 'publish',
                        'comment_status' => 'closed',
                    );

                    $page_id = wp_update_post($new_page);
                } else {
                    // Page doesn't exist, create new page (post).
                    $new_page = array(
                        'post_title'     => 'Our API',
                        'post_content'   => $content,
                        'post_author'    => '1',
                        'post_type'      => 'page',
                        'post_status'    => 'publish',
                        'comment_status' => 'closed',
                        'post_name'      => 'api-docs',
                    );

                    $page_id = wp_insert_post($new_page, true);
                }

                if (is_wp_error($page_id) or !$page_id) {
                    JRP::add_admin_notice($page_id->get_error_message(), JRP::MSG_ERROR);
                } else {
                    update_option('jrp_api_docs_page_id', $page_id);
                }

            }

        }

        
        /**
         * Run on JRP plugin deactivation.
         * @return void
         */
        static public function deactivate()
        {
            JRP::remove_wp_options();
        }


        /**
         * Add JRP menu to admin sidebar menu.
         */
        static public function add_admin_menu()
        {
            add_menu_page(JRP::TITLE . ' Page', JRP::TITLE, 'manage_options', JRP::PRODUCT, array('JRP', 'page_settings'), JRP::PLUGIN_URL . "view/images/icon.png", '4.22');
            add_submenu_page(JRP::PRODUCT, JRP::TITLE . ' Page', 'Settings', 'manage_options', JRP::PRODUCT, array('JRP', 'page_settings'));
        }


        /**
         * Settings page.
         * @return void
         */
        static public function page_settings()
        {
            global $wpdb, $wp_version;
            $page = 'Settings';

            if ($_REQUEST['save_api_settings']) {
                if (isset($_REQUEST['api_settings']) and is_array($_REQUEST['api_settings'])) {
                    self::update_api_settings($_REQUEST['api_settings']);
                }
            }

            if ($_REQUEST['save_feed_settings']) {
                if (isset($_REQUEST['feed_settings']) and is_array($_REQUEST['feed_settings'])) {
                    self::update_feed_settings($_REQUEST['feed_settings']);
                }
            }

            if ($_REQUEST['save_share_settings']) {
                if (isset($_REQUEST['share_settings']) and is_array($_REQUEST['share_settings'])) {
                    self::update_share_settings($_REQUEST['share_settings']);
                }
            }

            $api_settings   = self::get_api_settings();
            $feed_settings  = self::get_feed_settings();
            $share_settings = self::get_share_settings();

            $api_page = JRPUtils::get_page_by_name("api-docs");

            if (isset($_REQUEST['action']) and ($_REQUEST['action'] == '_import_users_')) {
                JRP::import_users();
            }
            if (isset($_REQUEST['action']) and ($_REQUEST['action'] == '_import_user_company_names_')) {
                JRP::import_user_company_names();
            }
            if (isset($_REQUEST['action']) and ($_REQUEST['action'] == '_import_jobs_')) {
                JRP::import_jobs();
            }

            include(JRP::PATH . "/view/settings.php");
        }


        /**
         * Include additional (admin) WP scripts and styles.
         * @param  string $hook_suffix ???
         * @return void
         */
        static public function add_admin_scripts($hook_suffix)
        {
            // Only load when JRP plugin page is displayed
            if (preg_match('@' . JRP::PRODUCT . '@', $hook_suffix)) {
                wp_enqueue_script(array('hoverIntent', 'postbox', 'jquery', 'jquery-ui-core', 'jquery-ui-accordion', 'editor', 'thickbox', 'media-upload', 'custom-header', 'media-editor'));
                wp_enqueue_script('jrp-jquery-tools', plugins_url('/view/resources/jquery-tools/js/jquery.tools.min.js', __FILE__));
                wp_enqueue_script('jrp-datatables', plugins_url('/view/resources/datatables/js/jquery.dataTables.js', __FILE__));
                wp_enqueue_script('jrp-datatables-pipeline', plugins_url('/view/resources/datatables/js/dataTables.pipeline.js', __FILE__));
                wp_enqueue_script('jrp-datatables-inputpagination', plugins_url('/view/resources/datatables/js/jquery.dataTables.inputpagination.js', __FILE__));
                wp_enqueue_script('jrp-utils', plugins_url('/view/js/utils.js?v=' . JRP::VERSION, __FILE__));
                wp_enqueue_script('jrp', plugins_url('/view/js/_common.js?v=' . JRP::VERSION, __FILE__));
                // wp_enqueue_script('jrp-page', plugins_url('/view/js/' . JRP::$page . '.js?v=' . JRP::VERSION, __FILE__));
                
                // wp_enqueue_style('jquery_ui_loc', plugins_url('/css/jquery-ui-custom.css', __FILE__));
                wp_enqueue_style(array('thickbox'));
                wp_enqueue_style('jrp-jquery-ui', plugins_url('/view/resources/jquery-ui/css/jquery-ui-custom.css', __FILE__));
                wp_enqueue_style('jrp', plugins_url('/view/css/_common.css?v=' . JRP::VERSION, __FILE__));
                wp_enqueue_style('jrp-datatables', plugins_url('/view/resources/datatables/css/demo_table_jui.css?v=' . JRP::VERSION, __FILE__));
                // wp_enqueue_style('jrp-page', plugins_url('/view/css/' . JRP::$page . '.css?v=' . JRP::VERSION, __FILE__));
                wp_enqueue_style('jrp-bootstrap-icons', plugins_url('/view/resources/bootstrap/css/bootstrap-icons-only.css?v=' . JRP::VERSION, __FILE__));
                wp_enqueue_style('jrp-bootstrap-labels', plugins_url('/view/resources/bootstrap/css/bootstrap-labels-only.css?v=' . JRP::VERSION, __FILE__));
                wp_enqueue_style('jrp-bootstrap-nav', plugins_url('/view/resources/bootstrap/css/bootstrap-nav-only.css?v=' . JRP::VERSION, __FILE__));

                wp_enqueue_style('google-fonts', 'http://fonts.googleapis.com/css?family=Libre+Baskerville');
                if (function_exists('wp_enqueue_media') and !did_action('wp_enqueue_media')) {
                    wp_enqueue_media();
                }

                preg_match('@/([^/]+)@', $hook_suffix, $matches);
                if (is_array($matches) and !empty($matches)) {
                    wp_enqueue_style('jrp-page', plugins_url('/view/css/' . $matches[1] . '.css?v=' . JRP::VERSION, __FILE__));
                    wp_enqueue_script('jrp-page', plugins_url('/view/js/' . $matches[1] . '.js?v=' . JRP::VERSION, __FILE__));
                } else {
                    wp_enqueue_style('jrp-page', plugins_url('/view/css/settings.css?v=' . JRP::VERSION, __FILE__));
                    wp_enqueue_script('jrp-page', plugins_url('/view/js/settings.js?v=' . JRP::VERSION, __FILE__));
                }

                wp_localize_script(
                    'jrp',
                    'JRP',
                    array(
                        'ajaxurl'     => admin_url('admin-ajax.php'),
                        'product'     => JRP::PRODUCT,
                        'MSG_ERROR'   => JRP::MSG_ERROR,
                        'MSG_WARNING' => JRP::MSG_WARNING,
                        'MSG_SUCCESS' => JRP::MSG_SUCCESS,
                        'MSG_INFO'    => JRP::MSG_INFO,
                    )
                );
            }

        }

        /**
         * Include additional WP scripts and styles.
         * @param  string $hook_suffix ???
         * @return void
         */
        static public function add_scripts($hook_suffix)
        {
            global $pagename;

            if ($pagename == 'submit-job') {

                $ip_address = JRPUtils::get_ip_address();
                $location = ""; 
                $gi = geoip_open(dirname(__FILE__) . '/data/GeoLiteCity.dat', GEOIP_STANDARD); 
                if ($gi) { 
                    $record = geoip_record_by_addr($gi, $ip_address); 
                    $country = $record->country_code; 
                    $region  = $record->region; 
                    $city    = $record->city; 
                    geoip_close($gi); 
                    if (($city !== "")  and ($city !== null) and ($country !== "") and ($country !== null)) { 
                        if ($region == '00') { 
                            $location = "{$city}, {$country}";     
                        } else { 
                            $location = "{$city}, {$region}, {$country}"; 
                        } 
                    } 
                }                 

                wp_enqueue_script('jrp', plugins_url('/view/js/jrfixes.js?v=' . JRP::VERSION, __FILE__));
                wp_localize_script(
                    'jrp',
                    'JRP',
                    array(
                        'ajaxurl' => admin_url('admin-ajax.php'),
                        'stop_round_image' => plugins_url( dirname(JRP::NAME) . '/view/images/stop_round.png'),
                        'location' => $location,
                    )
                );
            }

            wp_enqueue_script('jrp-uri', plugins_url('/view/resources/uri-js/URI.js?v=' . JRP::VERSION, __FILE__));
            if (current_user_can('manage_options')) {
                // wp_enqueue_script(array('jquery', 'jquery-ui-core'));
                // wp_enqueue_script('jquery-tools', plugins_url('/js/jquery.tools.min.js', __FILE__));
                // wp_enqueue_script('jrp-utils', plugins_url('/js/utils.js?v=' . JRP::VERSION, __FILE__));
                wp_localize_script(
                    'jrp-utils',
                    'JRP',
                    array(
                        'ajaxurl' => admin_url('admin-ajax.php'),
                    )
                );
            }
        }


        static public function add_to_admin_head()
        {
            echo "<!--[if lt IE 9]>\n";
            echo "<script src=\"http://ie7-js.googlecode.com/svn/version/2.1(beta4)/IE9.js\"></script>\n";
            echo "<![endif]-->\n";
        }


        /**
         * Add a plugin meta link
         * 
         * @param array $links ???
         * @param string $file ???
         * @return array ???
         */
        static public function set_plugin_meta($links, $file)
        {
            $plugin = plugin_basename(__FILE__);
            if ( $file == $plugin ) {
                return array_merge(
                    $links,
                    array(sprintf('<a href="admin.php?page=%s">%s</a>', JRP::PRODUCT . '/settings', __('Settings')))
                );
            }
            return $links;
        }


        /*
         * Remove this plugin from the update list...
         */
        static public function no_updates($r, $url)
        {
            if (0 !== strpos( $url, 'http://api.wordpress.org/plugins/update-check')) {
                return $r; // Not a plugin update request. Bail immediately.
            }
            
            $plugins = unserialize($r['body']['plugins']);
            $file = JRP::PATH . '/jrp.php';
            unset($plugins->plugins[plugin_basename($file)]);
            unset($plugins->active[array_search(plugin_basename($file), $plugins->active)]);
            $r['body']['plugins'] = serialize($plugins);
            
            return $r;
        }


        static public function remove_wp_options() 
        {
            if(!empty(self::$plugin_options) && is_array(self::$plugin_options)) {
                foreach(self::$plugin_options as $option_name) {
                    delete_option($option_name);                    
                }
            }

            return true;
        }


        /**
         * Show any notices related to JRP (WP header).
         * This function name should be added to 'admin_notices' action.
         * @return boolean False always.
         */
        static public function show_notices()
        {
            $has_dismiss = false;

            if (isset(JRP::$flash_messages) and is_array(JRP::$flash_messages)) {
                foreach(JRP::$flash_messages as $idx=>$message) {
                    if ($message['display'] == 'global') {
                        switch ($message['type']) {
                            case JRP::MSG_ERROR:
                                ?>
                                <div class="error jrp-msg jrp-msg-error">
                                    <?php if (isset($message['dismiss']) and $message['dismiss']): ?>
                                        <?php wp_enqueue_style('jrp-jquery-ui', plugins_url('/view/resources/jquery-ui/css/jquery-ui-custom.css', __FILE__)); ?>
                                        <?php $has_dismiss = true; ?>
                                        <span class="ui-icon ui-icon-closethick close-message" style="float:right; margin-top: 8px; cursor: pointer;" title="Close message" <?php echo isset($message['slug'])? "data-msg_slug='" . $message['slug'] . "'" : ""; ?>></span>
                                    <?php endif; ?>
                                    <p><span class="ui-icon ui-icon-alert" style="float:left"></span>
                                    &nbsp;<strong>[<?php echo JRP::TITLE; ?>] </strong>
                                    <?php echo $message['text']; ?></p>
                                </div>
                                <?php
                                break;
                            case JRP::MSG_WARNING:
                                ?>
                                <div class="updated jrp-msg jrp-msg-warning">
                                    <?php if (isset($message['dismiss']) and $message['dismiss']): ?>
                                        <?php wp_enqueue_style('jrp-jquery-ui', plugins_url('/view/resources/jquery-ui/css/jquery-ui-custom.css', __FILE__)); ?>
                                        <?php $has_dismiss = true; ?>
                                        <span class="ui-icon ui-icon-closethick close-message" style="float:right; margin-top: 8px; cursor: pointer;" title="Close message" <?php echo isset($message['slug'])? "data-msg_slug='" . $message['slug'] . "'" : ""; ?>></span>
                                    <?php endif; ?>
                                    <p><span class="ui-icon ui-icon-alert" style="float:left"></span>
                                    &nbsp;<strong>[<?php echo JRP::TITLE; ?>] </strong>
                                    <?php echo $message['text']; ?></p>
                                </div>
                                <?php
                                break;
                            case JRP::MSG_SUCCESS:
                                ?>
                                <div class="updated jrp-msg jrp-msg-success">
                                    <?php if (isset($message['dismiss']) and $message['dismiss']): ?>
                                        <?php wp_enqueue_style('jrp-jquery-ui', plugins_url('/view/resources/jquery-ui/css/jquery-ui-custom.css', __FILE__)); ?>
                                        <?php $has_dismiss = true; ?>
                                        <span class="ui-icon ui-icon-closethick close-message" style="float:right; margin-top: 8px; cursor: pointer;" title="Close message"></span>
                                    <?php endif; ?>
                                    <p><span class="ui-icon ui-icon-circle-check" style="float:left"></span>
                                    &nbsp;<strong>[<?php echo JRP::TITLE; ?>] </strong>
                                    <?php echo $message['text']; ?></p>
                                </div>
                                <?php
                                break;
                            case JRP::MSG_INFO:
                            default:
                                ?>
                                <div class="updated jrp-msg jrp-msg-info">
                                    <?php if (isset($message['dismiss']) and $message['dismiss']): ?>
                                        <?php wp_enqueue_style('jrp-jquery-ui', plugins_url('/view/resources/jquery-ui/css/jquery-ui-custom.css', __FILE__)); ?>
                                        <?php $has_dismiss = true; ?>
                                        <span class="ui-icon ui-icon-closethick close-message" style="float:right; margin-top: 8px; cursor: pointer;" title="Close message"></span>
                                    <?php endif; ?>
                                    <p><span class="ui-icon ui-icon-info" style="float:left"></span>
                                    &nbsp;<strong>[<?php echo JRP::TITLE; ?>] </strong>
                                    <?php echo $message['text']; ?></p>
                                </div>
                                <?php
                                break;
                        }
                    }
                }

                if ($has_dismiss) {
                    ?>
                    <script type="text/javascript">
                    jQuery(document).ready(function() {
                        jQuery(".jrp-msg .close-message").click(function() {
                            var msg_slug = jQuery(this).data("msg_slug");
                            var that = this;
                            jQuery.ajax({
                                url: ajaxurl,
                                type: 'post',
                                data: "msg_slug=" + msg_slug + "&action=jrp_dismiss_message",
                                dataType: 'json',
                                beforeSend: function() {
                                    jQuery(that).closest(".jrp-msg").remove();
                                },
                                success: function(response) {
                                },
                                error: function(jqXHR, textStatus, errorThrown) {
                                    if (jqXHR.responseText) {
                                        alert(jqXHR.responseText);
                                    } else {
                                        alert("Some error occurred! Please try again.");
                                    }
                                },
                                complete: function(jqXHR, textStatus) {
                                }
                            });
                        });
                    });
                    </script>
                    <?php
                }
            }

            return false;
        }
    

        /**
         * Add auto-update functionallity
         */
        static public function check_for_plugin_update($checked_data)
        {
            if (empty($checked_data->checked))
                return $checked_data;

            $plugin_info = get_site_transient('update_plugins');
            $request_args = array(
                'slug'    => basename(dirname(__FILE__)),
                'version' => $checked_data->checked[plugin_basename(__FILE__)],
            );
            $request_string = self::prepare_request('basic_check', $request_args);
            // Start checking for an update
            $raw_response = wp_remote_post(JRP::UPDATE_API_URL, $request_string);
            if (!is_wp_error($raw_response) && ($raw_response['response']['code'] == 200)) {
                $response = @unserialize($raw_response['body']);
                $response->package = 'unknown';
            }

            if (isset($response) && is_object($response) && !empty($response)) // Feed the update data into WP updater
                $checked_data->response[plugin_basename(__FILE__)] = $response;
            
            return $checked_data;
        }

        static public function plugin_api_call($def, $action, $args)
        {
            // Get the current version
            if (    isset($args->slug)
                and (strpos($args->slug,basename(dirname(__FILE__))) !== false)
            )  {
                $plugin_info = get_site_transient('update_plugins');

                $current_version = $plugin_info->checked[plugin_basename(__FILE__)];
                $args->version = $current_version;
                $request_string = self::prepare_request($action, $args);

                $request = wp_remote_post(JRP::UPDATE_API_URL, $request_string);

                if (is_wp_error($request)) {
                    $def = new WP_Error('plugins_api_failed', __('An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>'), $request->get_error_message());
                } else {
                    $def = unserialize($request['body']);
                    if ($def === false) {
                        $def = new WP_Error('plugins_api_failed', __('An unknown error occurred'), $request['body']);
                    } else {
                        $def->download_link = 'unknown';
                    }
                }
            }

            return $def;
        }

        static public function prepare_request($action, $args = '')
        {
            global $wp_version;
            return array(
                'body' => array(
                    'action'  => $action,
                    'request' => serialize($args),
                    'api-key' => md5(get_bloginfo('url'))
                ),
                'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
            );
        }


        static public function create_tables()
        {
            global $wpdb;

        }


        /**
         * Add plugin flash message to the JRP::$messages array.
         * This message will be displayed in plugin page messages section (at the top of each JRP plugin page).
         * @param string $text Flash message text.
         * @param string $type Type of notice (JRP::MSG_ERROR, JRP::MSG_WARNING, JRP::MSG_SUCCESS, JRP::MSG_INFO).
         */
        static public function add_flash_message($text, $type = 'info')
        {
            JRP::$flash_messages[] = array(
                'text' => $text,
                'type' => $type,
                'display' => 'local',
            );
        }


        /**
         * Add admin notice message to the JRP::$messages array.
         * This message will be displayed in admin notices messages section (at the top of each admin page).
         * @param string $text Notice (message) text.
         * @param string $type Type of notice (JRP::MSG_ERROR, JRP::MSG_WARNING, JRP::MSG_SUCCESS, JRP::MSG_INFO).
         */
        static public function add_admin_notice($text, $type = 'info', $dismiss = false, $slug = '')
        {
            JRP::$flash_messages[] = array(
                'text' => $text,
                'type' => $type,
                'dismiss' => $dismiss,
                'slug'    => $slug,
                'display' => 'global',
            );
        }


        /**
         * Main RSS feeds handler.
         * @return void
         */
        static public function rss_feed_handler()
        {
            if (preg_match('/.rss.*/s', $_SERVER['REQUEST_URI'])) {
                $feed_settings = self::get_feed_settings();
                $uri = rtrim($_SERVER['REQUEST_URI'], '/ ');
                switch($uri) {
                    case '/rss/indeed':
                    case '/rss/glassdoor':
                        if (JRP::FEED_GLASSDOOR && $feed_settings['glassdoor']['enabled']) {
                            include_once(JRP::PATH . "/view/template-rss-glassdoor.php");
                            exit();
                        }
                        break;

                    case '/rss/simplyhired':
                        if (JRP::FEED_SIMPLYHIRED && $feed_settings['simplyhired']['enabled']) {
                            include_once(JRP::PATH . "/view/template-rss-simplyhired.php");
                            exit();
                        }
                        break;

                    case '/rss/juju':
                        if (JRP::FEED_JUJU && $feed_settings['juju']['enabled']) {
                            include_once(JRP::PATH . "/view/template-rss-juju.php");
                            exit();
                        }
                        break;

                    case '/rss/twitterjs':
                        if (JRP::FEED_TWITJOBSEARCH && $feed_settings['twitjobsearch']['enabled']) {
                            include_once(JRP::PATH . "/view/template-rss-twitjobsearch.php");
                            exit();
                        }
                        break;

                    default:
                        break;
                }
            }
        }


        static public function get_api_settings()
        {
            $settings = get_option('jrb_api_settings', array());
            $settings = JRPUtils::parse_args_dbl($settings, self::$default_api_settings);
            return $settings;
        }


        static public function update_api_settings($settings)
        {
            $settings = JRPUtils::parse_args_dbl($settings, self::$default_api_settings);
            update_option('jrb_api_settings', $settings);
            return $settings;
        }


        static public function get_feed_settings()
        {
            $settings = get_option('jrb_feed_settings', array());
            $settings = JRPUtils::parse_args_dbl($settings, self::$default_feed_settings);
            return $settings;
        }


        static public function update_feed_settings($settings)
        {
            $settings = JRPUtils::parse_args_dbl($settings, self::$default_feed_settings);
            update_option('jrb_feed_settings', $settings);
            return $settings;
        }


        static public function get_share_settings()
        {
            $settings = get_option('jrb_share_settings', array());
            $settings = JRPUtils::parse_args_dbl($settings, self::$default_share_settings);
            return $settings;
        }


        static public function update_share_settings($settings)
        {
            $settings = JRPUtils::parse_args_dbl($settings, self::$default_share_settings);
            update_option('jrb_share_settings', $settings);
            return $settings;
        }


        static public function live_jobs_share_buttons($data)
        {
            $share_settings = self::get_share_settings();
            if ($share_settings['enable_share_buttons_in_live_jobs_table'] and ($data = 'job_lister')) {
                ?>
                <script type="text/javascript">
                // JRPlus - Share buttons for Live Jobs table
                jQuery(document).ready(function() {
                    var share_counter = 0;
                    jQuery("<th class='center'>Share</th>").insertAfter(".myjobs_section#live .data_list thead tr:first-child th:nth-child(4)");
                    jQuery(".myjobs_section#live .data_list tbody tr").each(function() {
                        var url = jQuery(this).find("td:first-child a").attr("href");
                        var title = jQuery(this).find("td:first-child a").html();
                        var job_link = jQuery(this).find(".job-edit-link").attr("href");
                        var job_link_uri = URI(job_link);
                        var job_link_search = job_link_uri.search(true);
                        var job_id = "";
                        if (typeof job_link_search.job_edit != "undefined") {
                            job_id = job_link_search.job_edit;
                        }

                        var share_html = '<td style="width:55px"> \
                            <a onclick="jrp_share_with_short_link_and_location(\'facebook\' , \'' + url + '\', \'' + title + '\', \'' + job_id + '\');" href="javascript: void(0)"><img src="<?php echo JRP::PLUGIN_URL ?>/view/images/facebook_16.png" style=""></a> \
                            <a onclick="jrp_share_with_short_link_and_location(\'twitter\' , \'' + url + '\', \'' + title + '\', \'' + job_id + '\');" href="javascript: void(0)"><img src="<?php echo JRP::PLUGIN_URL ?>/view/images/twitter_16.png" style=""></a> \
                            <a onclick="jrp_share_with_short_link_and_location(\'linkedin\' , \'' + url + '\', \'' + title + '\', \'' + job_id + '\');" href="javascript: void(0)"><img src="<?php echo JRP::PLUGIN_URL ?>/view/images/linkedin_16.png" style=""></a> \
                        </td>';
                        jQuery(share_html).insertAfter(jQuery(this).find("td:nth-child(4)"));
                        // stWidget.addEntry({
                        //     "service":"twitter",
                        //     "element": document.getElementById('twitter_button_' + share_counter),
                        //     "url": url,
                        //     "title": title,
                        //     "type": "hcount",
                        //     "text": "Tweet" ,
                        //     "image": "http://w.sharethis.com/images/twitter_counter.png",
                        //     "summary": title
                        // });
                        // stWidget.addEntry({
                        //     "service": "facebook",
                        //     "element": document.getElementById('facebook_button_' + share_counter),
                        //     "url": url,
                        //     "title": title,
                        //     "type": "hcount",
                        //     "text": "Like" ,
                        //     "image": "http://w.sharethis.com/images/facebook_counter.png",
                        //     "summary": title
                        // });
                        share_counter += 2;
                    });
                });

                function jrp_share_with_short_link(service, link, title)
                {
                    /* Open this row */
                    jQuery.ajax({
                        url: jQuery(location).attr("protocol") + "//" + jQuery(location).attr("host"),
                        type: 'post',
                        data: "ajax=jrp-ajax&action=get_shortlink&link=" + link,
                        dataType: 'json',
                        beforeSend: function() {
                        },
                        success: function(response) {
                            switch (service) {
                                case 'facebook':
                                    window.open(
                                        'http://www.facebook.com/share.php?u=' + response + '&t=' + title,
                                        'sharer',
                                        'toolbar=0,status=0,width=548,height=325'
                                    );
                                    break;

                                case 'twitter':
                                    window.open(
                                        'http://www.twitter.com/share?url=' + response + '&text=' + title + ' @ ',
                                        'sharer',
                                        'toolbar=0,status=0,width=548,height=325'
                                    );
                                    break;

                                default:
                                    break;
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            if (jqXHR.responseText) {
                                alert(jqXHR.responseText);
                            } else {
                                alert("Some error occurred! Please try again.");
                            }
                        },
                        complete: function(jqXHR, textStatus) {
                        }
                    });
                }

                function jrp_share_with_short_link_and_location(service, link, title, job_id)
                {
                    /* Open this row */
                    jQuery.ajax({
                        url: jQuery(location).attr("protocol") + "//" + jQuery(location).attr("host"),
                        type: 'post',
                        data: "ajax=jrp-ajax&action=get_shortlink_and_location&link=" + link + "&job_id=" + job_id,
                        dataType: 'json',
                        beforeSend: function() {
                        },
                        success: function(response) {
                            switch (service) {
                                case 'facebook':
                                    window.open(
                                        'http://www.facebook.com/share.php?s=100&p[url]=' + response.short_link + '&p[title]=' + title + '&p[images][0]=' + response.thumbnail_url + '&p[summary]=' + response.excerpt,
                                        'sharer',
                                        'toolbar=0,status=0,width=548,height=325'
                                    );
                                    break;

                                case 'twitter':
                                    window.open(
                                        'http://www.twitter.com/share?url=' + response.short_link + '&text=' + title + ' in ' + response.location + ' @ ',
                                        'sharer',
                                        'toolbar=0,status=0,width=548,height=325'
                                    );
                                    break;

                                case 'linkedin':
                                    window.open(
                                        'http://www.linkedin.com/shareArticle?mini=true&url=' + response.short_link + '&title=' + title + '&summary=' + response.excerpt + '&source=' + response.blog_name,
                                        'sharer',
                                        'toolbar=0,status=0,width=548,height=325'
                                    );
                                    break;

                                default:
                                    break;
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            if (jqXHR.responseText) {
                                alert(jqXHR.responseText);
                            } else {
                                alert("Some error occurred! Please try again.");
                            }
                        },
                        complete: function(jqXHR, textStatus) {
                        }
                    });
                }
                </script>
                <?php
            }
        }


        static public function ajax_handler()
        {
            if (isset($_REQUEST['ajax']) and ($_REQUEST['ajax'] == 'jrp-ajax')) {
                if (isset($_REQUEST['action'])) {
                    switch ($_REQUEST['action']) {

                        case 'get_shortlink':
                            if (isset($_REQUEST['link']) and ($_REQUEST['link'] != '')) {
                                $short_link = JRP::get_shortlink($_REQUEST['link']);
                                echo json_encode($short_link);
                            }
                            break;

                        case 'get_shortlink_and_location':
                            if (isset($_REQUEST['link']) and ($_REQUEST['link'] != '')) {
                                
                                $share_settings = self::get_share_settings();
                                
                                $short_link = JRP::get_shortlink($_REQUEST['link']);
                                $location = '';
                                $thumbnail_url = $share_settings['default_share_image'];
                                $excerpt = '';
                                if (isset($_REQUEST['job_id']) and ($_REQUEST['job_id'] != '') and is_numeric($_REQUEST['job_id'])) {
                                    $job_id = (int) $_REQUEST['job_id'];
                                    $post = get_post($job_id);
                                    if (strlen($post->post_content) > 150) {
                                        $excerpt = htmlspecialchars(substr(strip_tags($post->post_content), 0, 147)) . "...";
                                    } else {
                                        $excerpt = htmlspecialchars(strip_tags($post->post_content));
                                    }
                                    $post_meta = get_post_meta($job_id);
                                    if (isset($post_meta['_jr_address'])) {
                                        $location = $post_meta['_jr_address'][0];
                                    } else if (isset($post_meta['geo_address'])) {
                                        $location = $post_meta['geo_address'][0];
                                    }
                                    $thumbnail_id = get_post_thumbnail_id($job_id);
                                    $thumbnail = wp_get_attachment_image_src($thumbnail_id);
                                    if ($thumbnail[0] != '') {
                                        $thumbnail_url = $thumbnail[0];
                                    }
                                }
                                echo json_encode(array('short_link' => $short_link, 'location' => $location, 'thumbnail_url' => $thumbnail_url, 'excerpt' => $excerpt, 'blog_name' => get_bloginfo('name')));
                            }
                            break;

                        default:
                            break;

                    }
                    exit();
                }
            }
        }


        static public function handle_accepted_job( $post )
        {
            if ($post->post_type == APP_POST_TYPE) {
                if (function_exists('jd_doTwitterAPIPost')) {
                    $url = JRP::get_shortlink(get_permalink($post->ID));
                    jd_doTwitterAPIPost($post->post_title . " @ " . $url, false, $post->ID);
                }
            }
        }


        static public function get_shortlink($link)
        {
            global $wpbitly;

            if (isset($wpbitly) and is_object($wpbitly)) {
                // Submit to Bit.ly API and look for a response
                $url = sprintf( $wpbitly->url['shorten'], $wpbitly->options['bitly_username'], $wpbitly->options['bitly_api_key'], urlencode( $link ) );
                $bitly_response = wpbitly_curl( $url );
                // Success?
                if ( is_array( $bitly_response ) && $bitly_response['status_code'] == 200 ) {
                    $link = $bitly_response['data']['url'];
                }

            }

            return $link;
        }


        static public function check_third_party_plugins()
        {
            global $wpbitly;

            $msg_slug = 'wpbitly';
            if (!get_option("jrp_hide_notice_{$msg_slug}")) {
                // Check if WP Bit.ly plugin is present.
                if (!isset($wpbitly) or !is_object($wpbitly)) {
                    JRP::add_admin_notice('JRPlus plugin links shortening functionality depends on <a href="http://wordpress.org/plugins/wp-bitly/" taget="_blank">WP Bit.ly</a> plugin. Please install/activate <a href="http://wordpress.org/plugins/wp-bitly/" taget="_blank">WP Bit.ly</a> plugin in order to utilize links shortening feature.', JRP::MSG_WARNING, true, $msg_slug);
                }
            }

            $msg_slug = 'wptotwitter';
            if (!get_option("jrp_hide_notice_{$msg_slug}")) {
                // Check if WP to Twitter plugin is present.
                if (!function_exists('jd_doTwitterAPIPost')) {
                    JRP::add_admin_notice('JRPlus plugin post newly approved jobs to Twitter functionality depends on <a href="http://wordpress.org/plugins/wp-to-twitter/" taget="_blank">WP to Twitter</a> plugin. Please install/activate <a href="http://wordpress.org/plugins/wp-to-twitter/" taget="_blank">WP to Twitter</a> plugin in order to utilize links shortening feature.', JRP::MSG_WARNING, true, $msg_slug);
                }
            }

        }


        static public function add_random_job_posts($count, $start)
        {

            $companies = array(
                'Hirogen Gunsmith',
                'Ocampa Clothing Co.',
                'Suliban Transport Co.',
                'Star Fleet Utility Co.',
                'Klingon Thrift Shop',
                'Cardasian Bakery',
                'Romulan Grocery Shop',
                'Andorian Brewery',
                'Borg Socks & Belts',
                'Ferengi Locksmith',
            );

            $contents = array(
                'Suspendisse lectus leo, consectetur in tempor sit amet, placerat quis neque. Etiam luctus porttitor lorem, sed suscipit est rutrum non. Curabitur lobortis nisl a enim congue semper. Aenean commodo ultrices imperdiet. Vestibulum ut justo vel sapien venenatis tincidunt. Phasellus eget dolor sit amet ipsum dapibus condimentum vitae quis lectus.',
                'Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. In euismod ultrices facilisis. Vestibulum porta sapien adipiscing augue congue id pretium lectus molestie. Proin quis dictum nisl. Morbi id quam sapien, sed vestibulum sem. Duis elementum rutrum mauris sed convallis.',
                'Vivamus fermentum semper porta. Nunc diam velit, adipiscing ut tristique vitae, sagittis vel odio. Maecenas convallis ullamcorper ultricies. Curabitur ornare, ligula semper consectetur sagittis, nisi diam iaculis velit, id fringilla sem nunc vel mi.',
                'Aliquam at massa ipsum. Quisque bibendum purus convallis nulla ultrices ultricies. Nullam aliquam, mi eu aliquam tincidunt, purus velit laoreet tortor, viverra pretium nisi quam vitae mi. Fusce vel volutpat elit. Nam sagittis nisi dui.',
                'Morbi odio libero, scelerisque eget nulla non, condimentum auctor velit. Maecenas eleifend, felis non dignissim facilisis, dui turpis euismod justo, eu bibendum arcu lectus non augue. Donec pulvinar, nunc vitae pulvinar interdum, mauris urna adipiscing nisl, vitae cursus elit nibh iaculis sem. Etiam metus felis, ullamcorper ac ipsum nec, tempus blandit velit. Duis commodo erat sit amet lectus ultricies bibendum eu quis magna. Vivamus tristique elit sem, accumsan feugiat massa ultricies nec.',
                'Etiam convallis neque lobortis nulla pellentesque egestas eu sit amet dolor. Phasellus sodales nunc ac sapien lacinia viverra. Interdum et malesuada fames ac ante ipsum primis in faucibus. Fusce non justo lectus. Donec vitae tellus eget felis iaculis posuere ut a neque.',
                'Donec scelerisque, velit vel tempor iaculis, ante quam iaculis odio, malesuada tristique sapien turpis sit amet orci. Nam in feugiat ipsum. Nunc felis mi, hendrerit in viverra sed, convallis in elit. Maecenas purus ipsum, lacinia eget rhoncus eget, feugiat a est. Aliquam vel enim neque. Nam imperdiet luctus eros, non vestibulum nunc molestie id.',
                'Duis faucibus ipsum est, ut viverra lorem porttitor eget. Aliquam lacinia nulla nunc, a tristique magna feugiat eu. Aenean rhoncus, odio sed tristique cursus, arcu dolor aliquet orci, sit amet porta urna nibh eget diam. Etiam convallis neque lobortis nulla pellentesque egestas eu sit amet dolor. Phasellus sodales nunc ac sapien lacinia viverra.',
                'Pellentesque nibh felis, elementum non scelerisque non, adipiscing vitae risus. Quisque vitae auctor nunc. Vivamus non tellus ac tortor volutpat adipiscing. Aliquam eget nisi a nibh molestie sollicitudin luctus et nunc. Proin aliquet quis nisi ac hendrerit. Sed lectus metus, bibendum vitae dolor eu,',
                'Integer tristique dictum ligula, quis interdum magna luctus vitae. Maecenas et metus nec quam molestie rutrum sed et nibh.

Morbi odio libero, scelerisque eget nulla non, condimentum auctor velit. Maecenas eleifend, felis non dignissim facilisis, dui turpis euismod justo, eu bibendum arcu lectus non augue. Donec pulvinar, nunc vitae pulvinar interdum, mauris urna adipiscing nisl, vitae cursus elit nibh iaculis sem.',
                'Nam dapibus nisl mauris, sed pharetra leo dictum quis. Proin accumsan dapibus sapien, non viverra nisi lobortis quis. Nullam ac hendrerit elit. In laoreet faucibus rhoncus.',
            );
            
            for ($i = 0; $i < $count; $i++) {
                $company = $companies[rand(0, count($companies)-1)];
                $content = $contents[rand(0, count($contents)-1)];

                $new_post = array(
                    'comment_status' => 'closed',
                    'ping_status' => 'closed',
                    'post_author' => 0,
                    'post_category' => '',
                    'post_content' => $content,
                    'post_excerpt' => $content,
                    'post_name' => '',
                    'post_parent' => 0,
                    'post_status' => 'publish',
                    'post_title' => 'Test Job Post ' . ($start + $i),
                    'post_type' => 'job_listing',
                );
                $post_id = wp_insert_post($new_post);
                if ($post_id) {
                    $cats = get_terms( APP_TAX_CAT, array('hide_empty' => 0));
                    $random = $cats[rand(0,count($cats)-1)]->term_id;
                    JRPUtils::debug_log($random, 'RANDOM CAT');
                    wp_set_post_terms($post_id, $random, APP_TAX_CAT);

                    $types = get_terms( APP_TAX_TYPE, array('hide_empty' => 0));
                    $random = $types[rand(0,count($types)-1)]->term_id;
                    JRPUtils::debug_log($random, 'RANDOM TYPE');
                    wp_set_post_terms($post_id, $random, APP_TAX_TYPE);

                    $salaries = get_terms( APP_TAX_SALARY, array('hide_empty' => 0));
                    $random = $salaries[rand(0,count($salaries)-1)]->term_id;
                    JRPUtils::debug_log($random, 'RANDOM SALARY');
                    wp_set_post_terms($post_id, $random, APP_TAX_SALARY);
                    
                    update_post_meta($post_id, '_Company', $company);
                    update_post_meta($post_id, '_CompanyURL', strtolower(preg_replace('/[^a-zA-Z0-9]/s', '', $company)) . ".com");
                    update_post_meta($post_id, '_how_to_apply', 'Just apply!');
                    // update_post_meta($post_id, 'geo_address', '');
                    // update_post_meta($post_id, 'geo_country', '');
                    // update_post_meta($post_id, 'geo_short_address', '');
                    // update_post_meta($post_id, 'geo_short_address_country', '');
                }
            }
        }


        static public function import_user_company_names()
        {   
            // SELECT
            //     u.*, e.name as company, e.address, e.city, e.state, e.phone, e.administrative, e.email as email2, e.zip, e.postlimit, e.authorize_profileid
            // FROM
            //     users u
            //     INNER JOIN employer e ON e.id = u.employer_id

            global $wpdb;

            @set_time_limit(600);

            if (($handle = fopen(JRP::PATH . "/users.csv", "r")) !== false) {
                $first_row = fgetcsv($handle, 0, ",", '"');
                while (($user_data = fgetcsv($handle, 0, ",", '"')) !== false) {
                    $u = array(
                        'email'      => $user_data[1],
                        'first_name' => $user_data[3],
                        'last_name'  => $user_data[4],
                        'created_on' => $user_data[10],
                        'address'    => $user_data[18],
                        'city'       => $user_data[19],
                        'state'      => $user_data[20],
                        'phone'      => $user_data[21],
                    );
                    $uid = email_exists($u['email']);
                    if ($uid) {
                        // Update WP user
                        $user_data = array(
                            'ID'       => $uid,
                            'nickname' => $user_data[17],
                        );
                        $user_id = wp_update_user( $user_data );
                        if (!$user_id) {
                            JRPUtils::debug_log($u['email'], 'COMPANY NAME IMPORT FAILED');
                        } else {
                            // JRPUtils::debug_log($u['email'] . ' -> ' . $user_data['nickname'], 'COMPANY NAME IMPORT');
                        }
                    }
                }
                fclose($handle);
            }
        }


        static public function import_users()
        {   
            // SELECT
            //     u.*, e.name as company, e.address, e.city, e.state, e.phone, e.administrative, e.email as email2, e.zip, e.postlimit, e.authorize_profileid
            // FROM
            //     users u
            //     INNER JOIN employer e ON e.id = u.employer_id

            global $wpdb;

            @set_time_limit(600);

            $plans = jr_get_available_plans();
            $plan_id = 0;
            if ( is_array($plans) ) {
                foreach ( $plans as $key => $plan ) {
                    if ($plan['post_data']->post_title == 'Free Trial') {
                        $plan_id = $plan['post_data']->ID;
                    }
                }
            }

            if (($handle = fopen(JRP::PATH . "/users.csv", "r")) !== false) {
                $first_row = fgetcsv($handle, 0, ",", '"');
                while (($user_data = fgetcsv($handle, 0, ",", '"')) !== false) {
                    $u = array(
                        'email'      => $user_data[1],
                        'first_name' => $user_data[3],
                        'last_name'  => $user_data[4],
                        'created_on' => $user_data[10],
                        'address'    => $user_data[18],
                        'city'       => $user_data[19],
                        'state'      => $user_data[20],
                        'phone'      => $user_data[21],
                    );
                    $uid = email_exists($u['email']);
                        // wp_delete_user($uid);
                        // $uid = 0;
                    if (!$uid) {
                        // Create new WP user
                        $user_data = array(
                            'user_login'   => $u['email'],
                            'user_pass'    => 'like1said', //wp_generate_password(8, false),
                            'user_email'   => $u['email'],
                            'first_name'   => $u['first_name'],
                            'last_name'    => $u['last_name'],
                            'display_name' => $u['first_name'] . ' ' . $u['last_name'],
                            'role'         => 'job_lister',
                        );
                        $user_id = wp_insert_user( $user_data );
                        if ($user_id) {
                            // Update user_login with email (WP strips special characters like + and . from user_login field).
                            $sql = $wpdb->prepare("UPDATE {$wpdb->prefix}users SET `user_login` = %s WHERE `ID` = %d", $u['email'], $user_id);
                            $wpdb->query($sql);
                            // wp_new_user_notification( $user_id, $user_data['user_pass'] );
                            // jr_give_pack_to_user($user_id, $plan_id);
                        } else {
                            GWICUtils::debug_log($u['email'], 'IMPORT FAILED');
                        }
                    }
                }
                fclose($handle);
            }
        }


        static public function import_jobs()
        {
            // SELECT
            //     j.id as job_id,
            //     j.employer_id,
            //     u.id as user_id,
            //     u.email as email,
            //     t.var_name AS type,
            //     j.title AS title,
            //     j.description AS description,
            //     j.company AS company,
            //     j.url AS url,
            //     j.apply AS apply,
            //     em.name as employer_name,
            //     j.instructions,
            //     j.created_on AS mysql_date,
            //     j.views_count AS views_count,
            //     j.outside_location AS outside_location,
            //     j.poster_email AS poster_email,
            //     j.apply_online AS apply_online,
            //     cat.name AS category_name,
            //     c.name as city_name,
            //     c.state as city_state,
            //     em.address as address,
            //     em.city as city,
            //     em.state as state
            // FROM
            //     jobs j 
            //     LEFT JOIN employer em ON em.id = j.employer_id
            //     LEFT JOIN users u ON u.employer_id = em.id
            //     LEFT JOIN categories cat ON j.category_id = cat.id
            //     LEFT JOIN cities c ON c.id = j.city_id
            //     LEFT JOIN types t ON t.id = j.type_id
            // WHERE
            //     j.is_active = 1 AND
            //     u.email != 'emily.rosenblatt@etonien.com'

            global $wpdb;

            @set_time_limit(600);

            if (($handle = fopen(JRP::PATH . "/jobs.csv", "r")) !== false) {
                $first_row = fgetcsv($handle, 0, ",", '"');
                while (($job_data = fgetcsv($handle, 0, ",", '"')) !== false) {
                    $job = array(
                        'id'             => $job_data[0],
                        'employer_email' => $job_data[3],
                        'type'           => $job_data[4],
                        'title'          => $job_data[5],
                        'description'    => $job_data[6],
                        'company'        => $job_data[7],
                        'companyurl'     => $job_data[8],
                        '_how_to_apply'  => $job_data[9],
                        'date'           => trim($job_data[12]),
                        'views'          => trim($job_data[13]),
                        'category'       => trim($job_data[17]),
                        'city'           => $job_data[21],
                        'state'          => $job_data[22],
                    );
                    
                    $uid = email_exists($job['employer_email']);

                    if ($uid) {
                        $new_post = array(
                            'comment_status' => 'closed',
                            'ping_status' => 'closed',
                            'post_author' => $uid,
                            'post_category' => '',
                            'post_content' => $job['description'],
                            'post_date' => $job['date'],
                            'post_excerpt' => $job['description'],
                            // 'post_name' => '',
                            'post_parent' => 0,
                            'post_status' => 'publish',
                            'post_title' => $job['title'],
                            'post_type' => 'job_listing',
                        );
                        $post_id = wp_insert_post($new_post);
                        if ($post_id) {
                            $result = $wpdb->query($wpdb->prepare(
                                "UPDATE {$wpdb->posts} SET ID = %d WHERE ID = %d",
                                $job['id'],
                                $post_id
                            ));
                            if ($result !== false) {
                                $post_id = $job['id'];
                            } else {
                                JRPUtils::debug_log("Couldn't update post ID ({$post_id}) with old job ID (" . $job['id'] . ")!", 'ERROR');
                            }
                            $cats = get_terms( APP_TAX_CAT, array('hide_empty' => 0));
                            $cats_eq = array(
                                'Program / Web Dev' => 'Programming',
                                'Database' => 'Data',
                                'Office / Clerical' => 'Office',
                                'Consultants' => 'Consultants',
                                'Executive / Management' => 'Executive / Management',
                                'Engineering / Arch' => 'Engineering',
                                'Sales / Marketing' => 'Sales',
                                'Human Resources' => 'Human Resources',
                                'Recruiting / Sourcing' => 'Recruiting',
                                'Telecomm Jobs' => 'Telecomm Jobs',
                                'Energy / Oil and Gas' => 'Energy',
                                'Quality / Test' => 'Quality Control',
                                'New Media / Mobile' => 'Mobile',
                                'General' => 'General',
                                'Accounting / Finance' => 'Accounting',
                                'Customer Service' => 'Customer Service',
                                'Legal / Paralegal' => 'Legal',
                                'Skilled Trades' => 'Skilled Trades',
                                'Entry Lvl / Internship' => 'Entry Lvl / Internship',
                                'Healthcare / Nursing' => 'Healthcare',
                                'Retail / Hospitality' => 'Hospitality',
                                'Pharma / Biotech' => 'Biotech',
                                'Business Intelligence' => 'Business Intelligence',
                                'Green / Renewable' => 'Renewable',
                                'Education / Teaching' => 'Education',
                                'Systems / Networking' => 'Systems',
                                'Manufacturing /  Supply' => 'Manufacturing',
                                'Graphics / Design' => 'Graphics / Design',
                                'Insurance' => 'Insurance',
                                'IT / Tech Support' => 'IT / Tech Support',
                                'Advertising' => 'Advertising',
                            );
                            $cat = 0;
                            foreach ($cats as $c) {
                                if ($c->name == $cats_eq[$job['category']]) {
                                    $cat = $c->term_id;
                                    break; 
                                }
                            }
                            JRPUtils::debug_log($post_id . " => " . $cat . " (" . $job['category'] . ", " . $cats_eq[$job['category']] . ")", 'POST CAT');
                            wp_set_post_terms($post_id, $cat, APP_TAX_CAT);

                            $types = get_terms( APP_TAX_TYPE, array('hide_empty' => 0));
                            $types_eq = array(
                                'fulltime' => 'Full-Time',
                                'parttime' => 'Part-Time',
                                'freelance' => 'Freelance',
                            );
                            $type = 0;
                            foreach ($types as $t) {
                                if ($t->name == $types_eq[$job['type']]) {
                                    $type = $t->term_id;
                                    break; 
                                }
                            }
                            JRPUtils::debug_log($post_id . " => " . $type . " (" . $job['type'] . ", " . $types_eq[$job['type']] . ")", 'POST TYPE');
                            wp_set_post_terms($post_id, $type, APP_TAX_TYPE);

                            update_post_meta($post_id, '_Company', $job['company']);
                            update_post_meta($post_id, '_CompanyURL', $job['companyurl']);
                            update_post_meta($post_id, '_how_to_apply', $job['_how_to_apply']);
                            update_post_meta($post_id, 'jr_total_count', $job['views']);

                            $address = '';
                            if ($job['city'] == 'NULL') {
                                $job['city'] = '';
                            }
                            if (isset($job['city']) and ($job['city'] != '') and ($job['city'] != 'NULL')) {
                                $address = $job['city'];
                            }
                            if ($job['state'] == 'NULL') {
                                $job['state'] = '';
                            }
                            if (isset($job['state']) and ($job['state'] != '') and ($job['state'] != 'NULL')) {
                                $address .= ', ' . $job['state'];
                            }
                            if ($address != '') {
                                $address .= ', US';
                            }
                            if ($address != '') {
                                $geo_url = _jr_get_geolocation_url($address);
                                $result = wp_remote_get($geo_url);
                                if( !is_wp_error( $result ) ) {
                                    $json = json_decode($result['body']);
                                    if (isset($json->status) and ($json->status == "OK")) {
                                        foreach ($json->results as $result){
                                            $lat = $result->geometry->location->lat;
                                            $lng = $result->geometry->location->lng;
                                        }
                                    }
                                }
                                if (isset($lat)) {
                                    update_post_meta($post_id, 'geo_address', $address);
                                    update_post_meta($post_id, 'geo_country', 'US');
                                    update_post_meta($post_id, 'geo_short_address', $job['city']);
                                    update_post_meta($post_id, 'geo_short_address_country', 'US');
                                    update_post_meta($post_id, '_jr_geo_latitude', $lat);
                                    update_post_meta($post_id, '_jr_geo_longitude', $lng);
                                }
                            }
                        }
                    } else {
                        JRPUtils::debug_log("Can't find user with " . $job['employer_email'] . " email!", 'ERROR');
                    }
                }
                fclose($handle);
            }

        }

    }

}

/**
 * Include base classes.
 */
include_once(JRP::LIB_PATH . '/jrpajax.class.php');
include_once(JRP::LIB_PATH . '/jrputils.class.php');
include_once(JRP::LIB_PATH . '/jrpapi.class.php');
include_once(JRP::LIB_PATH . '/geoip-api-php/geoipcity.inc'); 

/**
 * Initialize plugin.
 */
JRP::init();
JRPAjax::init();
JRPAPI::init();

?>
