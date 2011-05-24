<?php

/*
Plugin Name: ThirdPresence mobile video plugin
Version: 0.1
Plugin URI: http://wiki.thirdpresence.com/index.php/WordPress_Plugin_Description
Description: WordPress blog.
Author: ThirdPresence Ltd.
Author URI: http://www.thirdpresence.com
*/

//Account id/name
$account = [your thirdpresence account];

//Set width and height for the default video player
$width = 486;
$height = 412;

//Set default video variable
$videoid = 0;

$controlbar = 1;
$play_text = "Play";

$d = 0;

function Thirdpresence_Parse($content)
{
    $content = preg_replace_callback("/\[thirdpresence ([^]]*)\/\]/i", "Thirdpresence_Resolver", $content);
    return $content;
}

function Thirdpresence_Resolver($matches)
{
    global $video, $account, $width, $height, $arguments,$d,$controlbar,$play_text;
    $output = '';
    $matches[1] = str_replace(array('&#8221;','&#8243;'), '', $matches[1]);
    preg_match_all('/(\w*)=\"(.*?)\"/i', $matches[1], $attributes);
    $arguments = array();

    foreach ( (array) $attributes[1] as $key => $value ) {
        $arguments[$value] = str_replace('"', '', $attributes[2][$key]);
    }

    if($d) {
         print_r( $arguments );
    }

    if (( !array_key_exists('video', $arguments) )) {
        return '<div style="background-color:#f99; padding:10px;">Thirdpresence Widget Error: Required parameter "video" is missing!</div>';
        exit;
    }
    else {
	$video = $arguments['video'];
    }

    if( array_key_exists('width', $arguments) ) {
        $width = $arguments['width'];
    }

    if( array_key_exists('playtext', $arguments) ) {
        $play_text = $arguments['playtext'];
        $play_text = urlencode($play_text);
    }

    if( array_key_exists('controlbar', $arguments) ) {
        $controlbar = $arguments['controlbar'];
    }

    if (function_exists('curl_init')) {
	    $include_url = 'http://' . $account . '.thirdpresence.com/dls/t/wp-plugin-inc.jsp?iid=' . $video . '&w=' . $width . '&pt=' . $play_text . '&b=' . $controlbar;
            if($d) {
	       $output .= '<a href="'. $include_url .'">'. $include_url .'</a>';
            }

	    $ch = curl_init($include_url);
	    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    $output .= curl_exec($ch);
	    curl_close($ch);
    } else {
        $output = "ERROR! You have to be cURL mobule installed at php.";
    }

    return $output;
}

//Add a filter hook - this registers the function for all content
//text (Pages and Posts) to search for the [CONTRIBUTOR_WIDGET] tag.
add_filter('the_content', 'Thirdpresence_Parse');

?>