<?php
/**
 * Glassdoor RSS Feed Template
 *
 * @package  JRP
 * @since  1.0
 */

// <source>
//     <publisher>jobshouts.com</publisher>
//     <publisherurl>http://jobshouts.com</publisherurl>
//     <lastBuildDate>2013-07-12T15:12:36+00:00</lastBuildDate>
//     <job>
//         <title></title>
//         <date></date>
//         <url></url>
//         <company></company>
//         <city></city>
//         <state></state>
//         <country></country>
//         <description></description>
//         <jobtype></jobtype>
//         <category></category>
//     </job>
//     <job>
//         <title></title>
//         <date></date>
//         <url></url>
//         <company></company>
//         <city></city>
//         <state></state>
//         <country></country>
//         <description></description>
//         <jobtype></jobtype>
//         <category></category>
//     </job>
// </source>

@set_time_limit(300); // 5 minutes
header('HTTP/1.1 200 Ok');
header('Content-Type: text/xml');
$doc = new DOMDocument('1.0','utf-8');
$doc->formatOutput = true;

//root element
$r = $doc->createElement( "source" );
//append root element to our document
$doc->appendChild( $r );

$publisher = $doc->createElement("publisher");
$publisher->appendChild($doc->createTextNode($feed_settings['glassdoor']['publisher'])); // put here the name of the publisher or ID
$r->appendChild($publisher);

$publisherurl = $doc->createElement("publisherurl");
$publisherurl->appendChild($doc->createTextNode($feed_settings['glassdoor']['publisherurl']));//Put here the url of your site
$r->appendChild($publisherurl);

$builddate = $doc->createElement("lastBuildDate");
$builddate->appendChild($doc->createTextNode(date('Y-m-d\TH:i:sP')));//Put here the url of your site
$r->appendChild($builddate);

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

            $title = $doc->createElement("title");
            $title->appendChild($doc->createCDATASection( $the_post->post_title));
            $b->appendChild( $title );

            $date = $doc->createElement("date");
            $date->appendChild($doc->createCDATASection($the_post->post_date));
            $b->appendChild($date);

            $url = $doc->createElement("url");
            $url->appendChild($doc->createCDATASection(get_permalink( $the_post->ID)));
            $b->appendChild($url);

            $company_name = get_post_meta($the_post->ID,'_Company',true);
            $company = $doc->createElement("company");
            $company->appendChild($doc->createCDATASection($company_name));
            $b->appendChild($company);

            //query postmeta table for city and state// then parse it into an array
            $location = get_post_meta($the_post->ID, '_jr_address', true);
            if ($location == "") {
                $location = get_post_meta($the_post->ID, 'geo_address', true);
            }
            $location = explode(',',$location);

            $_city = '';
            $city = $doc->createElement("city");
            if (isset($location[0])) {
                $_city = $location[0];
            }
            $city->appendChild($doc->createCDATASection($_city));
            $b->appendChild($city);

            $_state = '';
            $state = $doc->createElement("state");
            if (isset($location[1])) {
                $_state = $location[1];
            }
            $state->appendChild($doc->createCDATASection($_state));
            $b->appendChild($state);

            $_country = '';
            $country = $doc->createElement("country");
            if (isset($location[2])) {
                $_country = $location[2];
            } else {
                $_country = $_state;
            }
            $country->appendChild($doc->createCDATASection($_country));
            $b->appendChild($country);

            $description = $doc->createElement("description");
            $description->appendChild($doc->createCDATASection(strip_tags($the_post->post_content)));
            $b->appendChild($description);

            $jobtypes_list = '';
            $terms = wp_get_post_terms($the_post->ID, APP_TAX_TYPE);
            if (is_array($terms) and !empty($terms)) {
                foreach ($terms as $term) {
                    $jobtypes_list .= "{$term->slug},";
                }
            }
            $jobtypes_list = rtrim($jobtypes_list, ',');
            $jobtype = $doc->createElement("jobtype");
            $jobtype->appendChild($doc->createCDATASection($jobtypes_list));
            $b->appendChild($jobtype);

            $categories_list = '';
            $terms = wp_get_post_terms($the_post->ID, APP_TAX_CAT);
            if (is_array($terms) and !empty($terms)) {
                foreach ($terms as $term) {
                    $categories_list .= "{$term->name},";
                }
            }
            $categories_list = rtrim($categories_list, ',');
            $category = $doc->createElement("category");
            $category->appendChild($doc->createCDATASection($categories_list));
            $b->appendChild($category);

            // $referencenumber = $doc->createElement("referencenumber");
            // $referencenumber->appendChild($doc->createCDATASection($the_post->ID));
            // $b->appendChild($referencenumber);

            $r->appendChild( $b );
        }
    }
    $page += 1;
} while ($continue);

echo $doc->saveXML(); 
