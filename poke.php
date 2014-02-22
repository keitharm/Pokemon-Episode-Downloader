<?php
require_once("global.php");

// Poke episode to download
$id = $argv[1];

$found = false;
$attempt = 0;
while (!$found) {
    // Give up after 3 (can't quite crack how the 4th one works yet) methods
    if (++$attempt == 4) {
        die("Failed to locate pokemon episode :(\n");
    }

    // Try to download via loadup.ru
    if ($attempt == 1) {
        $data = file_get_contents("http://pokemonepisode.org/1.php?P-ID=" . $id);

        // Detect if valid video url is found
        $val = extractData($data, "file=", "\" wid");
        if ($val != false) {
            $found = true;
            $url = $val;
        }
    // Try to download via VideoBam
    } else if ($attempt == 2) {
        $pre_data = file_get_contents("http://pokemonepisode.org/2.php?P-ID=" . $id);
        $data = file_get_contents(extractData($pre_data, "src=\"", "\""));

        // Detect if valid video url is found
        $val = extractData($data, "high: '", "'");
        if ($val != false) {
            $found = true;
            $url = $val;
        }
    // Try to download via DailyMotion
    } else if ($attempt == 3) {
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
        die;
    }
}
echo "attempt #" . $attempt . "\n";
echo $url . "\n";
?>