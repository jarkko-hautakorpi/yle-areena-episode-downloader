<?php
/*
 * YLE Areena video crawler
 * Find video links from a episode listing page and download them using yle-dl.
 * http://aajanki.github.io/yle-dl/
 * Run as a cron job to download episodes.
 * 0 1 * * * php -f /home/john/.cronscripts/yle_episode_downloader.php >> /home/john/.cronscripts/download.log
 *
 */
require __DIR__ . '/vendor/autoload.php';
use PHPHtmlParser\Dom;

$page_URL = "http://areena.yle.fi/1-2540138";
$saved_videos_folder = "/home/john/Videos/YLE/Yle_uutiset/";


$dom = new Dom;
$dom->loadFromUrl($page_URL);

// Find the dom element with videos and loop them through
$newslist = $dom->find('ul.program-list li');

if (count($newslist) >= 1) {
    foreach ($newslist as $news) {
        // Get video ID
        $data_item_id = $news->getAttribute('data-item-id');

        /* <time itemprop="startDate" datetime="2015-07-10T20:30:00.000+03:00"> */
        $timestamp_dom = $news->find('time[itemprop=startDate]');
        $timestamp = $timestamp_dom->getAttribute('datetime');

        $weekday = "_" . date('l', strtotime($timestamp));
        $pubDate = strftime("%Y-%m-%d_klo_%H.%M", strtotime($timestamp));
        $filename = "Yle_uutiset_" . $pubDate . $weekday . ".flv";
        $url = "http://areena.yle.fi/" . $data_item_id;

        if (!file_exists($saved_videos_folder . $filename)) {
            echo "\nDownloading video: " . $filename . "\n";
            $current_folder = getcwd();
            chdir($saved_videos_folder);
            try {
                $result = exec("yle-dl -o " . $filename . " " . $url);
            } catch (Exception $exc) {
                echo $exc->getTraceAsString();
            }
            chdir($current_folder);
        }
    }
}
