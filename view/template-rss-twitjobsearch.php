<?php
/**
 * TwitJobSearch RSS Feed Template
 *
 * @package  JRP
 * @since  1.0
 */

// <jobs>
//     <publisher-name>jobshouts.com</publisher-name>
//     <publisher-url>http://jobshouts.com</publisher-url>
//     <job>
//         <id></id>
//         <date>2013-07-12 17:54:33</date>
//         <title></title>
//         <company></company>
//         <url></url>
//         <jobtype></jobtype>
//         <location></location>
//         <description></description>
//         <category></category>
//     </job>
// </jobs>

@set_time_limit(300); // 5 minutes
header('HTTP/1.1 200 Ok');
header('Content-Type: text/xml');
$doc = new DOMDocument('1.0','utf-8');
$doc->formatOutput = true;

//root element
$r = $doc->createElement( "positionfeed" );
//append root element to our document
$doc->appendChild( $r );

$publisher = $doc->createElement("publisher-name");
$publisher->appendChild($doc->createTextNode($feed_settings['twitjobsearch']['publisher'])); // put here the name of the publisher or ID
$r->appendChild($publisher);

$publisherurl = $doc->createElement("publisher-url");
$publisherurl->appendChild($doc->createTextNode($feed_settings['twitjobsearch']['publisherurl']));//Put here the url of your site
$r->appendChild($publisherurl);

$per_page = 25;
$page = 1;
do {
    // Get jobs.
    $args = array(
        'post_type'      => 'job_listing',
        'posts_per_page' => $per_page,
        'paged'          => $page,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC'
    );
    $feed_jobs = new WP_Query($args);

    $continue = false;
    if (is_array($feed_jobs->posts) and !empty($feed_jobs->posts)) {
        if (count($feed_jobs->posts) >= $per_page) {
            $continue = true;
        }
        foreach ($feed_jobs->posts as $the_post) {

            $b = $doc->createElement( "job" );

            $e = $doc->createElement("id");
            $e->appendChild($doc->createTextNode( $the_post->ID ));
            $b->appendChild( $e );

            $e = $doc->createElement("date");
            $e->appendChild($doc->createTextNode($the_post->post_date));
            $b->appendChild($e);

            $e = $doc->createElement("title");
            $e->appendChild($doc->createTextNode( $the_post->post_title ));
            $b->appendChild( $e );

            $company = get_post_meta($the_post->ID, '_Company', true);
            $e = $doc->createElement("company");
            $e->appendChild($doc->createTextNode( $company ));
            $b->appendChild( $e );

            $e = $doc->createElement("url");
            $e->appendChild($doc->createTextNode( get_permalink($the_post->ID) ));
            $b->appendChild($e);

            $jobtypes_list = '';
            $terms = wp_get_post_terms($the_post->ID, APP_TAX_TYPE);
            if (is_array($terms) and !empty($terms)) {
                foreach ($terms as $term) {
                    $jobtypes_list .= "{$term->slug},";
                }
            }
            $jobtypes_list = rtrim($jobtypes_list, ',');
            $jobtype = $doc->createElement("jobtype");
            $jobtype->appendChild($doc->createTextNode($jobtypes_list));
            $b->appendChild($jobtype);

            $e = $doc->createElement("location");
            //query postmeta table for city and state// then parse it into an array
            $location = get_post_meta($the_post->ID, '_jr_address',true);
            if ($location == "") {
                $location = get_post_meta($the_post->ID, 'geo_address', true);
            }
            $location = explode(',', $location);
            $_city = '';
            if (isset($location[0])) {
                $_city = $location[0];
            }
            $e->appendChild($doc->createTextNode( $_city ));
            $b->appendChild($e);

            $e = $doc->createElement("description");
            $e->appendChild($doc->createTextNode($the_post->post_content));
            $b->appendChild($e);

            $categories_list = '';
            $terms = wp_get_post_terms($the_post->ID, APP_TAX_CAT);
            if (is_array($terms) and !empty($terms)) {
                foreach ($terms as $term) {
                    $categories_list .= "{$term->name},";
                }
            }
            $categories_list = rtrim($categories_list, ',');
            $category = $doc->createElement("category");
            $category->appendChild($doc->createTextNode($categories_list));
            $b->appendChild($category);

            $r->appendChild( $b );
        }
    }
    $page += 1;
} while ($continue);

echo $doc->saveXML(); 
