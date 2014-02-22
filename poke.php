<?php
require_once("global.php");

// Argument mode/episode number
$arg = $argv[1];

// Attempt to fetch all pokemon episodes
if ($arg == "test") {

    // Start timer
    $time_start = microtime(true);

    for ($a = 1; $a < 808; $a++) {
        $episode = poke($a);
        if ($episode != null) {
            echo "\033[32mFound episode " . $a . " - method #" . $episode[1] . " - " . $episode[0] . "\033[0m\n";
        } else {
            echo "\033[31mEpisode " . $a . " not found!\033[0m\n";
        }
    }
    echo "\nData fetched in " . (microtime(true)-$time_start) . " seconds.\n";

// Retrieve a range of episodes
} else if (strpos($arg, "-") != false) {

    // Start timer
    $time_start = microtime(true);
    // Extract range
    $range = explode("-", $arg);
    // Range error checking
    if ($range[0] > $range[1] || !is_numeric($range[0]) || !is_numeric($range[1])) {
        die("\033[31mError: Episode range is invalid!\n");
    }

    // Fetch episode data
    echo "Attempting to retrieve Pokemon Episodes \033[32m" . $range[0] . "\033[0m - \033[32m" . $range[1] . "\033[0m\n";
    for ($a = $range[0]; $a <= $range[1]; $a++) {
        $episode = poke($a);
        if ($episode != null) {
            echo "\033[32mFound episode " . $a . " - method #" . $episode[1] . " - " . $episode[0] . "\033[0m\n";
        } else {
            echo "\033[31mEpisode " . $a . " not found!\033[0m\n";
        }
    }
    echo "\nData fetched in " . (microtime(true)-$time_start) . " seconds.\n";

// Retrieve a single specified episode
} else if (is_numeric($arg)) {

    // Error message if no episode specified
    if ($arg == null) {
        die("\033[31mError: No episode specified!\n\033[0mUsage: php poke.php [test | episode_number | range]\n");
    }

    // Start timer
    $time_start = microtime(true);

    // Fetch episode data
    echo "Attempting to retrieve Pokemon Episode \033[32m#" . $arg . "\033[0m";
    $episode = poke($arg);

    if ($episode != null) {
        echo "\n\033[32mFound episode " . $arg . " - method #" . $episode[1] . " - " . $episode[0] . "\033[0m";
    } else {
        echo "\n\033[31mEpisode " . $arg . " not found!\033[0m\n";
    }
    echo "\n\nData fetched in " . (microtime(true)-$time_start) . " seconds.\n";
} else {
    die("\033[31mError: Unknown input!\n\033[0mUsage: php poke.php [test | episode_number | range]\n");
}

function poke($id = 1) {
    $found = false;
    $method = 0;

    while (!$found) {
        // Give up after 3 (can't quite crack how the 4th one works yet) methods
        if (++$method == 4) {
            return null;
        }

        // Try to download via loadup.ru
        if ($method == 1) {
            $data = file_get_contents("http://pokemonepisode.org/1.php?P-ID=" . $id);

            // Detect if valid video url is found
            $val = extractData($data, "file=", "\" wid");
            if ($val != false) {
                $found = true;
                $url = $val;
            }
        // Try to download via VideoBam
        } else if ($method == 2) {
            $pre_data = file_get_contents("http://pokemonepisode.org/2.php?P-ID=" . $id);
            $data = file_get_contents(extractData($pre_data, "src=\"", "\""));

            // Detect if valid video url is found
            $val = extractData($data, "high: '", "'");
            if ($val != false) {
                $found = true;
                $url = $val;
            }
        // Try to download via DailyMotion
        } else if ($method == 3) {
            $pre_data = file_get_contents("http://pokemonepisode.org/3.php?P-ID=" . $id);
            $data = file_get_contents(extractData($pre_data, "src=\"", "\""));

            // Detect if valid video url is found
            $val = extractData($data, "stream_h264_url\":\"", "\"");
            if ($val != false) {
                $found = true;

                // Since DailyMotion adds extraneous slashes
                $url = stripslashes($val);
            }
        } else {
            return null;
        }
    }
    return array($url, $method);
}
?>