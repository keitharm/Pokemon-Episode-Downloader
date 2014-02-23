<?php
require_once("global.php");

// Argument mode/episode number
$arg = $argv[1];

/*
// Specific force method
$method = $argv[2];
if ($method == null) {
    $method = 1;
} else if (!is_numeric($method) || $method > 3 || $method < 1) {
    die("Error: Specified method is invalid!\n");
}
*/
$method = 1;

$save = $argv[2];

if ($save == null) {
    $save = false;
} else if ($save != "false" && $save != "true") {
    die("Error: Save mode is neither true nor false!\n");
}

// Attempt to fetch all pokemon episodes
if ($arg == "all") {

    // Start timer
    $time_start = microtime(true);

    for ($a = 1; $a < 808; $a++) {
        $episode = poke($a, $method);
        if ($episode != null) {
            $title = pokeTitle($a);
            echo "\033[32mFound episode " . $a . " (" . $title . ") - method #" . $episode[1] . " - " . $episode[0] . "\033[0m\n";
            if ($save == "true") {
                download($a, $title, $episode[0]);
            }
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
        $episode = poke($a, $method);
        if ($episode != null) {
            $title = pokeTitle($a);
            echo "\033[32mFound episode " . $a . " - method #" . $episode[1] . " - " . $episode[0] . "\033[0m\n";
            if ($save == "true") {
                download($a, $title, $episode[0]);
            }
        } else {
            echo "\033[31mEpisode " . $a . " not found!\033[0m\n";
        }
    }
    echo "\nData fetched in " . (microtime(true)-$time_start) . " seconds.\n";

// Retrieve a single specified episode
} else if (is_numeric($arg)) {

    // Error message if no episode specified
    if ($arg == null) {
        die("\033[31mError: No episode specified!\n\033[0mUsage: php poke.php [all | episode_number | range]\n");
    }

    // Start timer
    $time_start = microtime(true);

    // Fetch episode data
    echo "Attempting to retrieve Pokemon Episode \033[32m#" . $arg . "\033[0m";
    $episode = poke($arg, $method);

    if ($episode != null) {
        $title = pokeTitle($arg);
        echo "\n\033[32mFound episode " . $arg . " - method #" . $episode[1] . " - " . $episode[0] . "\033[0m\n";
        if ($save == "true") {
            download($arg, $title, $episode[0]);
        }
    } else {
        echo "\n\033[31mEpisode " . $arg . " not found!\033[0m\n";
    }
    echo "\n\nData fetched in " . (microtime(true)-$time_start) . " seconds.\n";
} else {
    die("Error: Unknown input!\nUsage: php poke.php [all | episode_number | range]\n");
}
?>