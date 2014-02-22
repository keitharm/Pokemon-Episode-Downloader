<?php
require_once("global.php");

// Attempt to fetch all pokemon episodes
if ($argv[1] == "test") {
    for ($a = 1; $a < 808; $a++) {
        $episode = poke($a);
        if ($episode != null) {
            echo "\033[32mFound episode " . $a . " - method #" . $episode[1] . " - " . $episode[0] . "\033[0m\n";
        } else {
            echo "\033[31mEpisode " . $a . " not found!\033[0m\n";
        }
    }
// Retrieve a single specified episode
} else {
    $id = $argv[1];

    // Error message if no episode specified
    if ($id == null) {
        die("\033[31mError: No episode specified!\n\033[0mUsage: php poke.php [episode_number]\n");
    }

    // Start timer
    $time_start = microtime(true);

    // Fetch episode data
    echo "Attempting to retrieve Pokemon Episode \033[32m#" . $id . "\033[0m";

    $episode = poke($id);
    if ($episode != null) {
        echo "\n\033[32mFound episode " . $id . " - method #" . $episode[1] . " - " . $episode[0] . "\033[0m";
        echo "\n\nData fetched in " . (microtime(true)-$time_start) . " seconds.\n";
    } else {
        echo "\n\033[31mEpisode " . $id . " not found!\033[0m\n";
    }
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