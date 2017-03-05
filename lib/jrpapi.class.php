<?php
/**
 * JRPAPI Class
 * 
 * @package  JRP
 * @since  1.0
 */
class JRPAPI
{
    static public $default_attributes = array(
        // 'action'      => '',
        'type'        => '0',
        'category'    => '0',
        'count'       => '0',
        'random'      => '0',
        'days_behind' => '0',
        'response'    => 'xml',
    );

    /**
     * Initialize JRP API functionality.
     * @return void
     */
    static public function init()
    {
        /** Add various AJAX actions and filters. */
        add_action(
            'wp',
            array('JRPAPI', 'handle_request'),
            1
        );
    }


    /**
     * Handle API requests.
     * @return void
     */
    static public function handle_request()
    {
        global $wpdb;

        if (preg_match('/.api[\/?]/s', $_SERVER['REQUEST_URI'])) {
            $query = array();
            parse_str($_SERVER['QUERY_STRING'], $query);
            
            $query = wp_parse_args($query, self::$default_attributes);

            if (is_array($query) and !empty($query)) {

                $sql_job_type = '';
                if (($query['type'] != "0") and ($query['type'] != "")) {
                    $sql_job_type = $wpdb->prepare("tt.taxonomy = 'job_type' AND t.slug = %s AND", $query['type']);
                }
                $sql_job_cat = '';
                if (($query['category'] != "0") and ($query['category'] != "")) {
                    $sql_job_cat  = $wpdb->prepare("tt.taxonomy = 'job_cat' AND t.slug = %s AND", $query['category']);
                }
                $sql_days_behind = '';
                if (is_numeric($query['days_behind']) and ($query['days_behind'] != '0')) {
                    $sql_days_behind = "AND post_date > DATE_FORMAT(DATE_SUB(NOW(), INTERVAL " . $wpdb->escape($query['days_behind']) . " DAY), '%Y-%m-%d 00:00:00')";
                }
                $sql_order = 'ORDER BY post_date DESC';
                if (($query['random'] != "0") and ($query['random'] != "")) {
                    $sql_order = '';
                }
                $sql_limit = '';
                if (is_numeric($query['count']) and ($query['count'] > 0)) {
                    $sql_limit = $wpdb->prepare("LIMIT %d", $query['count']);
                }

                if (isset($query['action'])) {
                    $results = array();
                    switch ($query['action']) {
                        case 'getJobs':
                            $sql = 
                                "SELECT
                                    ID, post_name as url_title, post_title as title, post_date as date, post_content
                                FROM
                                    {$wpdb->posts}
                                WHERE
                                    ID IN (
                                    SELECT 
                                        p.ID
                                    FROM
                                        {$wpdb->posts} p
                                        LEFT JOIN {$wpdb->prefix}term_relationships tr ON tr.object_id = p.ID
                                        LEFT JOIN {$wpdb->prefix}term_taxonomy tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
                                        LEFT JOIN {$wpdb->prefix}terms t ON t.term_id = tt.term_id
                                    WHERE
                                        {$sql_job_cat}
                                        p.ID IN (
                                            SELECT
                                                p.ID
                                            FROM
                                                {$wpdb->posts} p
                                                LEFT JOIN {$wpdb->prefix}term_relationships tr ON tr.object_id = p.ID
                                                LEFT JOIN {$wpdb->prefix}term_taxonomy tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
                                                LEFT JOIN {$wpdb->prefix}terms t ON t.term_id = tt.term_id
                                            WHERE
                                                {$sql_job_type}
                                                p.post_status = 'publish' AND
                                                p.post_type = 'job_listing'
                                        )
                                )
                                {$sql_days_behind}
                                {$sql_order}
                                {$sql_limit}";
                            // JRPUtils::debug_log($sql, 'API QUERY getJobs');
                            $results = $wpdb->get_results($sql, ARRAY_A);
                            self::display($results, $query['response']);
                            break;
                            
                        case 'getJobsByCompany':
                            $sql_company = '';
                            if (isset($query['company']) and ($query['company'] != '')) {
                                $sql_company = $wpdb->prepare("AND pm.meta_key = '_Company' AND pm.meta_value = %s", $query['company']);
                            }
                            $sql = 
                                "SELECT
                                    ID, post_name as url_title, post_title as title, post_date as date, post_content
                                FROM
                                    {$wpdb->posts}
                                WHERE
                                    ID IN (
                                        SELECT
                                            p.ID
                                        FROM
                                            {$wpdb->posts} p
                                            LEFT JOIN {$wpdb->prefix}postmeta pm ON pm.post_id = p.ID
                                        WHERE
                                            p.ID IN (
                                                SELECT 
                                                    p.ID
                                                FROM
                                                    {$wpdb->posts} p
                                                    LEFT JOIN {$wpdb->prefix}term_relationships tr ON tr.object_id = p.ID
                                                    LEFT JOIN {$wpdb->prefix}term_taxonomy tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
                                                    LEFT JOIN {$wpdb->prefix}terms t ON t.term_id = tt.term_id
                                                WHERE
                                                    {$sql_job_cat}
                                                    p.ID IN (
                                                        SELECT
                                                            p.ID
                                                        FROM
                                                            {$wpdb->posts} p
                                                            LEFT JOIN {$wpdb->prefix}term_relationships tr ON tr.object_id = p.ID
                                                            LEFT JOIN {$wpdb->prefix}term_taxonomy tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
                                                            LEFT JOIN {$wpdb->prefix}terms t ON t.term_id = tt.term_id
                                                        WHERE
                                                            {$sql_job_type}
                                                            p.post_status = 'publish' AND
                                                            p.post_type = 'job_listing'
                                                    )
                                            )
                                            {$sql_company}
                                    )
                                {$sql_days_behind}
                                {$sql_order}
                                {$sql_limit}";
                            JRPUtils::debug_log($sql, 'SQL');
                            // JRPUtils::debug_log($sql, 'API QUERY getJobsByCompany');
                            $results = $wpdb->get_results($sql, ARRAY_A);
                            self::display($results, $query['response']);
                            break;

                        default:
                            break;
                    }
                } else {
                    echo "Missing 'action' attribute!";
                }
                exit();
            }
        }
    }


    /**
     * Prints input data (array) to string with desired format (XML, JSON or JS).
     * @param  array $data   Data array.
     * @param  string $format Output format (default XML).
     * @return void
     */
    static public function display($data, $format = 'xml', $referer = '')
    {
        if (is_array($data)) {

            switch ($format) {

                case 'xml':
                    header('Content-Type: text/xml; charset="utf-8"');
                    $response = '<?xml version="1.0" encoding="utf-8"?>';
                    $response .= '<jobs>';
                    foreach ($data as $job) {
                        $post_content = $job['post_content'];
                        if (strlen($job['post_content']) > 150) {
                            $post_content = htmlspecialchars(substr(strip_tags($job['post_content']), 0, 147)) . "...";
                        }
                        $post_meta = get_post_meta($job['ID']);
                        $response .= '<job>';
                        $response .= '<title><![CDATA[' . $job['title'] . ' at ' . $post_meta['_Company'][0] . ']]></title>';
                        $response .= '<url>' . site_url('job/' . $job['ID'] . '/' . $job['url_title']) . '/</url>';
                        $response .= '<date>' . $job['date'] . '</date>';
                        $response .= '<description>' . $post_content . '</description>';
                        $response .= '</job>';
                    }
                    $response .= '</jobs>';
                    echo $response;
                    break;

                case 'json':
                    header('Content-type: text/javascript'); 
                    foreach ($data as $idx=>$job) {
                        if (isset($job['post_content'])) {
                            unset($job['post_content']);
                            unset($data[$idx]['post_content']);
                        }
                        $post_meta = get_post_meta($job['ID']);
                        $data[$idx]['company'] = '';
                        if (isset($post_meta['_Company'])) {
                            $data[$idx]['company'] = $post_meta['_Company'][0];
                        }
                        $data[$idx]['location'] = '';
                        if (isset($post_meta['_jr_address'])) {
                            $data[$idx]['location'] = $post_meta['_jr_address'][0];
                        } else if (isset($post_meta['geo_address'])) {
                            $data[$idx]['location'] = $post_meta['geo_address'][0];
                        }
                    }
                    $response = json_encode($data);
                    echo $response;
                    break;

                case 'js':
                    header('Content-type: text/javascript'); 
                    foreach ($data as $idx=>$job) {
                        if (isset($job['post_content'])) {
                            unset($job['post_content']);
                            unset($data[$idx]['post_content']);
                        }
                        $post_meta = get_post_meta($job['ID']);
                        $data[$idx]['company'] = '';
                        if (isset($post_meta['_Company'])) {
                            $data[$idx]['company'] = $post_meta['_Company'][0];
                        }
                        $data[$idx]['location'] = '';
                        if (isset($post_meta['_jr_address'])) {
                            $data[$idx]['location'] = $post_meta['_jr_address'][0];
                        } else if (isset($post_meta['geo_address'])) {
                            $data[$idx]['location'] = $post_meta['geo_address'][0];
                        }
                    }
                    $url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
                    $referer = base64_encode($url);

                    $response = 'var jobs = ';
                    $response .= json_encode($data);
                    $response .= ';';
                    $response .= '
                    function showJobs(html_container, css_class)
                    {
                        var html = "<ul class=\"" + css_class + "\">";
                        for (j = 0; j < jobs.length; j++)
                        {
                            //html += "<li><a target=\"_blank\" href=\"' . site_url('job/') . '" + jobs[j].ID + "/" + jobs[j].url_title + "/' . $referer . '/\">" + jobs[j].title + " at " + jobs[j].company + "</a></li>";
                            html += "<li><a target=\"_blank\" href=\"' . site_url('job/') . '" + jobs[j].ID + "/" + jobs[j].url_title + "/' . $referer . '/\">" + jobs[j].title + " (" + jobs[j].location + ")</a></li>";
                        }
                        html += "</ul>";
                        
                        if (document.getElementById(html_container))
                        {
                            document.getElementById(html_container).innerHTML = html;   
                        }
                        else
                        {
                            document.write("<div id=\"" + html_container + "\">" + html + "</div>");
                        }
                    }';
                    echo $response;
                    break;

                default:
                    break;

            }

        }

    }


}