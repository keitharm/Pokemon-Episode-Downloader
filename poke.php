<?php
require_once("global.php");

// Start up display
displayLogo();
echo "Version " . VERSION . ENDL . ENDL;

// Mode/episode number
$arg = @$argv[1];
if ($arg != "all") {
    if (strpos($arg, "-") == false) {
        if (!is_numeric($arg)) {
            if ($arg == "help") {
                displayUsage();
                die;
            } else {
                die(RED . "Error: Unknown mode. Must be #, #-#, or all." . WHITE . ENDL . "Type php poke.php help for usage." . WHITE . ENDL);
            }
        }
    }
}

// Download files option
$save = @$argv[2];
if ($save == null || $save == "false") {
    $save = false;
} else if ($save != "false" && $save != "true" && $save != "save") {
    die(RED . "Error: Save mode is neither true/save nor false." . WHITE . ENDL . "Type php poke.php help for usage." . WHITE . ENDL);
}

// Method to download files with
$method = @$argv[3];
if ($method == null) {
    $method = "all";
} else if (!in_array($method, array(1,2,3,"all"))) {
    die(RED . "Error: Method must be 1, 2, 3, or all." . WHITE . ENDL . "Type php poke.php help for usage." . WHITE . ENDL);
}

// Attempt to fetch all pokemon episodes
if ($arg == "all") {

    // Start timer
    $time_start = microtime(true);
    $already = already();
    $skips = getSkips(true);

    for ($a = 1; $a < TOTAL; $a++) {
        if (in_array($a, $already) && $save == true) {
            echo PURPLE . "Skipping episode " . $a . " - It has already been downloaded." . WHITE . ENDL;
            usleep(DELAY);
            continue;
        } else if (in_array($a, $skips) && $save == true) {
            echo YELLOW . "Skipping episode " . $a . " - On .skip blacklist." . WHITE . ENDL;
            usleep(DELAY);
            continue;
        }
        $episode = poke($a, $method);
        if ($episode != null) {
            $title = pokeTitle($a);
            echo GREEN . "Found episode " . $a . " (" . $title . ") - method #" . $episode[1] . " - " . $episode[0] . WHITE . ENDL;
            if ($save == "true" || $save == "save") {
                download($a, $title, $episode[0]);
            }
        } else {
            echo RED . "Episode " . $a . " not found - adding to blacklist." . WHITE . ENDL;
            addSkip($a);
        }
    }
    echo "Data fetched in " . (microtime(true)-$time_start) . " seconds." . ENDL;

// Retrieve a range of episodes
} else if (strpos($arg, "-") != false) {

    // Start timer
    $time_start = microtime(true);
    $already = already();
    $skips = getSkips(true);

    // Extract range
    $range = explode("-", $arg);
    // Range error checking
    if ($range[0] > $range[1] || !is_numeric($range[0]) || !is_numeric($range[1])) {
        die(RED . "Error: Episode range is invalid." . WHITE . ENDL);
    }

    // Fetch episode data
    echo "Attempting to retrieve Pokemon Episodes " . GREEN . $range[0] . WHITE . " - " . GREEN . $range[1] . WHITE . ENDL;
    for ($a = $range[0]; $a <= $range[1]; $a++) {
        if (in_array($a, $already) && $save == true) {
            echo PURPLE . "Skipping episode " . $a . " - It has already been downloaded." . WHITE . ENDL;
            usleep(DELAY);
            continue;
        } else if (in_array($a, $skips) && $save == true) {
            echo YELLOW . "Skipping episode " . $a . " - On .skip blacklist." . WHITE . ENDL;
            usleep(DELAY);
            continue;
        }
        $episode = poke($a, $method);
        if ($episode != null) {
            $title = pokeTitle($a);
            echo GREEN . "Found episode " . $a . " (" . $title . ") - method #" . $episode[1] . " - " . $episode[0] . WHITE . ENDL;
            if ($save == "true" || $save == "save") {
                download($a, $title, $episode[0]);
            }
        } else {
            echo RED . "Episode " . $a . " not found - adding to blacklist." . WHITE . ENDL;
            addSkip($a);
        }
    }
    echo "Data fetched in " . (microtime(true)-$time_start) . " seconds." . ENDL;

// Retrieve a single specified episode
} else if (is_numeric($arg)) {

    // Error message if no episode specified
    if ($arg == null) {
        die(RED . "Error: No episode specified." . ENDL . WHITE . displayUsage() . ENDL);
    }

    // Start timer
    $time_start = microtime(true);
    $already = already();
    $skips = getSkips(true);

    if (in_array($arg, $already) && $save == true) {
        echo PURPLE . "Skipping episode " . $arg . " - It has already been downloaded." . WHITE . ENDL;
        $skip = true;
    } else if (in_array($arg, $skips) && $save == true) {
        echo YELLOW . "Skipping episode " . $arg . " - On .skip blacklist." . WHITE . ENDL;
        usleep(DELAY);
        continue;
    }

    if (!$skip) {
        // Fetch episode data
        echo "Attempting to retrieve Pokemon Episode " . GREEN . "#" . $arg . ENDL;
        $episode = poke($arg, $method);

        if ($episode != null) {
            $title = pokeTitle($arg);
            echo GREEN . "Found episode " . $arg . " (" . $title . ") - method #" . $episode[1] . " - " . $episode[0] . WHITE . ENDL;
            if ($save == "true" || $save == "save") {
                download($arg, $title, $episode[0]);
            }
        } else {
            echo RED . "Episode " . $arg . " not found - adding to blacklist." . WHITE . ENDL;
            addSkip($arg);
        }
    }
    echo "Data fetched in " . (microtime(true)-$time_start) . " seconds." . ENDL;
} else {
    echo RED . "Error: Unknown input!" . WHITE . ENDL;
    echo displayUsage();
}
?>
