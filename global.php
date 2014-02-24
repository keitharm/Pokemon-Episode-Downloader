<?php
// Version
define("VERSION", "1.0.5");
// Enable color
define("COLOR", true);
// Total number of pokemon episodes
define("TOTAL", 809);
define("ENDL", "\n");


if (COLOR) {
    define("GREEN", "\033[32m");
    define("RED", "\033[31m");
    define("PURPLE", "\033[35;1m");
    define("WHITE", "\033[0m");
} else {
    define("GREEN", null);
    define("RED", null);
    define("PURPLE", null);
    define("WHITE", null);
}

$seanum = array(1 => 1, 83, 119, 160, 212, 277, 317, 369, 423, 469, 521, 573, 626, 660, 710, 759, 784, 804, TOTAL);
$seaamt = array(1 => 1, 83, 36, 41, 52, 65, 40, 52, 54, 46, 52, 52, 53, 34, 50, 49, 25, 20, TOTAL-804);
$seaname = array(1 => "Indigo League",
    "The Orange Island League",
    "The Johto Journeys",
    "Johto League Champions",
    "Master Quest",
    "Advanced",
    "Advanced Challenge",
    "Advanced Battle",
    "Battle Frontier",
    "Diamond and Pearl",
    "Diamond and Pearl Battle Dimension",
    "Diamond and Pearl Galactic Battle",
    "Diamond and Pearl Sinnoh League Victors",
    "Black and White",
    "Black and White Rival Destinies",
    "Black and White Adventures in Unova",
    "Black and White Adventures in Unova and Beyond",
    "XY");

function extractData($data, $search, $ending, $specific = -1) {
    $matches = findall($search, $data);
    foreach ($matches as &$val) {
        $offset = 0;
        $val += strlen($search);
        while (substr($data, $val+$offset, strlen($ending)) != $ending) {
            $offset++;
        }
        $val = substr($data, $val, $offset);
    }
    if ($matches == false) {
        return false;
    }

    if ($specific == -1) {
        if (count($matches) == 1) {
            return $matches[0];
        }
        return $matches;
    }
    return $matches[$specific-1];
}

// Function I found online
// Rewrote it to look nicer (so many comments in the last version!)
function findall($needle, $haystack) { 
    $buffer = '';
    $pos = 0;
    $end = strlen($haystack);
    $getchar = '';
    $needlelen = strlen($needle); 
    $found = array();
    
    while ($pos < $end) { 
        $getchar = substr($haystack, $pos, 1);
        if ($getchar != "\\n" || $buffer < $needlelen) { 
            $buffer = $buffer . $getchar;
            if (strlen($buffer) > $needlelen) { 
                $buffer = substr($buffer, -$needlelen);
            }
            if ($buffer == $needle) { 
                $found[] = $pos - $needlelen + 1;
            } 
        } 
        $pos++;
    } 
    if (array_key_exists(0, $found)) { 
        return $found;
    }
    return array();
}

function poke($id = 1, $method = 1) {
    $found = false;

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
        $data = @file_get_contents(extractData($pre_data, "src=\"", "\""));

        // Detect if valid video url is found
        $val = extractData($data, "stream_h264_url\":\"", "\"");
        if ($val != false) {
            $found = true;

            // Since DailyMotion adds extraneous slashes
            $url = stripslashes($val);
        }
    } else if ($method == "all") {
        $method_one = poke($id, 1);
        if ($method_one != null) {
            return $method_one;
        }

        $method_two = poke($id, 2);
        if ($method_two != null) {
            return $method_two;
        }

        $method_three = poke($id, 3);
        if ($method_three != null) {
            return $method_three;
        }
    } else {
        return null;
    }

    if ($found) {
        return array($url, $method);
    } else {
        return null;
    }
}

function pokeTitle($num) {
    $data = file_get_contents("http://pokemonepisode.org/episode-" . $num);
    $title = extractData($data, "&#8211; ", "<", 1);
    str_replace("&amp;", "&", $title);
    return stripslashes(html_entity_decode($title));
}

function download($num, $title, $url) {
    global $seanum, $seaname;

    @mkdir("pokemon");

    // File extension
    $headers = get_headers($url);
    $ext = $headers[3];
    if ($ext == "Content-Type: video/x-flv") {
        $ext = "flv";
    } else {
        $ext = "mp4";
    }
    @mkdir("pokemon/" . seasonNum($num) . " - " . $seaname[seasonNum($num)]);
    $current = current_episodes();
    if (strpos($current, $num . ENDL) === false) {
        file_put_contents(".current", $current . $num . ENDL);
    }
    @shell_exec("wget -O \"pokemon/" . seasonNum($num) . " - " . $seaname[seasonNum($num)] . "/[" . $num . "] - " . $title . "." . $ext . "\" " . $url . " --continue");
    $current = file_get_contents(".current");
    file_put_contents(".current", str_replace($num . ENDL, null, $current));
}

function seasonNum($episode) {
    global $seanum, $seaname;
    
    for ($a = 1; $a < count($seanum); $a++) {
        if ($episode >= $seanum[$a] && $episode < $seanum[($a+1)]) {
            return $a;
        }
    }
}

function displayLogo() {
    echo " ____       _                                  _____       _               _" . ENDL;
    echo "|  _ \ ___ | | _____ _ __ ___   ___  _ __     | ____|_ __ (_)___  ___   __| | ___" . ENDL;
    echo "| |_) / _ \| |/ / _ \ '_ ` _ \ / _ \| '_ \\    |  _| | '_ \| / __|/ _ \ / _` |/ _ \\" . ENDL;
    echo "|  __/ (_) |   <  __/ | | | | | (_) | | | |   | |___| |_) | \__ \ (_) | (_| |  __/" . ENDL;
    echo "|_|   \___/|_|\_\___|_| |_| |_|\___/|_| |_|   |_____| .__/|_|___/\___/ \__,_|\___|" . ENDL;
    echo "                                                    |_|" . ENDL;
    echo " ____                      _                 _" . ENDL;
    echo "|  _ \  _____      ___ __ | | ___   __ _  __| | ___ _ __" . ENDL;
    echo "| | | |/ _ \ \ /\ / / '_ \| |/ _ \ / _` |/ _` |/ _ \ '__|" . ENDL;
    echo "| |_| | (_) \ V  V /| | | | | (_) | (_| | (_| |  __/ |" . ENDL;
    echo "|____/ \___/ \_/\_/ |_| |_|_|\___/ \__,_|\__,_|\___|_|" . ENDL;
}

function displayUsage() {
    echo "Usage: php poke.php mode [save] [method]" . ENDL;
    echo "\t mode:" . ENDL;
    echo "\t\t#      - Specific episode fetch." . ENDL;
    echo "\t\t#-#    - Range of episodes to fetch." . ENDL;
    echo "\t\tall    - Fetch all episodes." . ENDL;
    echo "\t save:" . ENDL;
    echo "\t\ttrue   - Save the files into organized directory hierarchy." . ENDL;
    echo "\t\tsave   - Same thing as true." . ENDL;
    echo "\t\t*false - Don't save files, only display URLs." . ENDL;
    echo "\t method:" . ENDL;
    echo "\t\t1      - Use loadup.ru via pokemonepisode.org to download." . ENDL;
    echo "\t\t2      - Use VideoBam via pokemonepisode.org to download." . ENDL;
    echo "\t\t3      - Use DailyMotion via pokemonepisode.org to download." . ENDL;
    echo "\t\t*all   - Use all 3 modes via pokemonepisode.org to download." . ENDL;
    echo ENDL;
    echo "\t* = default option" . ENDL . ENDL;
}

function checkFiles() {
    $raw = dirToArray("pokemon");
    unset($raw[0]);
    foreach ($raw as $key => $val) {
        $sub_raw = dirToArray("pokemon/" . $key);
        $season[] = extractNum($key);
        foreach ($sub_raw as $sub_key => $sub_val) {
            $episode[] = extractNum($sub_val);
            $specific[$key][] = extractNum($sub_val);
        }
        sort($specific[$key]);
    }
    sort($episode);
    sort($season);
    return array($season, $episode, $specific);
}

function checkCompletion() {
    global $seanum, $seaname, $seaamt;
    $data = checkFiles();

    echo "Episode download completion (" . GREEN . count($data[1]) . "/" . TOTAL . WHITE . ") - " . round(count($data[1])/TOTAL*100) . "%" . ENDL;
    foreach($seaname as $val) {
        $a++;
        echo PURPLE . $val . WHITE . " (" . count($data[2][$a . " - " . $val]) . "/" . $seaamt[$a+1] . ") - " . ENDL;
    }
}

function extractNum($string) {
    if ($string[0] == "[") {
        return extractData($string, "[", "]");
    } else {
        return trim(substr($string, 0, 2));
    }
}

function dirToArray($dir) {
   $result = array();
   $cdir = @scandir($dir);

   foreach ($cdir as $key => $value) {
      if (!in_array($value,array(".","..",".DS_Store"))) {
         if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
            $result[$value] = dirToArray($dir . DIRECTORY_SEPARATOR . $value);
         } else {
            $result[] = $value;
         }
      }
   }
   return $result;
}

function already() {
    $data = checkFiles();
    $current = current_episodes();
    $ex = explode(ENDL, $current);

    foreach ($data[1] as $key => $val) {
        if (in_array($val, $ex)) {
            unset($data[1][$key]);
        }
    }
    return $data[1];
}

function current_episodes() {
    return @file_get_contents(".current");
}
?>