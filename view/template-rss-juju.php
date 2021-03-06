<?php
/**
 * Juju RSS Feed Template
 *
 * @package  JRP
 * @since  1.0
 */

// <positionfeed>
//     <source>jobshouts.com</source>
//     <sourceurl>http://jobshouts.com</sourceurl>
//     <feeddate>2013-07-15T11:39:27+00:00</feeddate>
//     <job id="2035805">
//         <employer></employer>
//         <title></title>
//         <description></description>
//         <postingdate></postingdate>
//         <joburl></joburl>
//         <location>
//             <state></state>
//             <city></city>
//         </location>
//     </job>
// </positionfeed>

@set_time_limit(300); // 5 minutes
header('HTTP/1.1 200 Ok');
header('Content-Type: text/xml');
$doc = new DOMDocument('1.0','utf-8');
$doc->formatOutput = true;

//root element
$r = $doc->createElement( "positionfeed" );
//append root element to our document
$doc->appendChild( $r );

$source = $doc->createElement("source");
$source->appendChild($doc->createTextNode($feed_settings['simplyhired']['source'])); // put here the name of the source or ID
$r->appendChild($source);

$sourceurl = $doc->createElement("sourceurl");
$sourceurl->appendChild($doc->createTextNode($feed_settings['simplyhired']['sourceurl']));//Put here the url of your site
$r->appendChild($sourceurl);

$feeddate = $doc->createElement("feeddate");
$feeddate->appendChild($doc->createTextNode(date('Y-m-d\TH:i:sP')));//Put here the url of your site
$r->appendChild($feeddate);

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

            $b_id = $doc->createAttribute( "id" );
            $b_id->value = $the_post->ID;
            $b->appendChild($b_id);

            $employer = get_post_meta($the_post->ID, '_Company', true);
            $e = $doc->createElement("employer");
            $e->appendChild($doc->createTextNode( $employer ));
            $b->appendChild( $e );

            $e = $doc->createElement("title");
            $e->appendChild($doc->createTextNode( $the_post->post_title ));
            $b->appendChild( $e );

            $e = $doc->createElement("description");
            $e->appendChild($doc->createTextNode($the_post->post_content));
            $b->appendChild($e);

            $e = $doc->createElement("postingdate");
            $e->appendChild($doc->createTextNode($the_post->post_date));
            $b->appendChild($e);

            $e = $doc->createElement("joburl");
            $e->appendChild($doc->createTextNode( get_permalink($the_post->ID) ));
            $b->appendChild($e);

            $e = $doc->createElement("location");
            //query postmeta table for city and state// then parse it into an array
            $location = get_post_meta($the_post->ID, '_jr_address',true);
            if ($location == "") {
                $location = get_post_meta($the_post->ID, 'geo_address', true);
            }
            $location = explode(',', $location);
            $_city = '';
            $city = $doc->createElement("city");
            if (isset($location[0])) {
                $_city = $location[0];
            }
            $city->appendChild($doc->createTextNode( $_city ));
            $_state = '';
            $state = $doc->createElement("state");
            if (isset($location[1])) {
                $_state = $location[1];
            }
            $state->appendChild($doc->createTextNode( $_state ));
            $e->appendChild($state);
            $e->appendChild($city);
            $b->appendChild($e);

            $r->appendChild( $b );
        }
    }
    $page += 1;
} while ($continue);

echo $doc->saveXML(); 
