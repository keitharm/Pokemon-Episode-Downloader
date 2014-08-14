<?php
// Version
define("VERSION", "1.2.0");
// Enable color
define("COLOR", true);
// Total number of pokemon episodes
define("TOTAL", 832);
define("ENDL", "\n");
define("DELAY", 5000);


if (COLOR) {
    define("GREEN", "\033[32m");
    define("RED", "\033[31m");
    define("PURPLE", "\033[35;1m");
    define("YELLOW", "\033[33m");
    define("WHITE", "\033[0m");
} else {
    define("GREEN", null);
    define("RED", null);
    define("PURPLE", null);
    define("YELLOW", null);
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
    $len = strlen($data);
    $matches = findall($search, $data);
    $found = array();
    foreach ($matches as $val) {
        $bad = false;
        $offset = 0;
        $val += strlen($search);
        while (substr($data, $val+$offset, strlen($ending)) != $ending) {
            $offset++;
            // If we are outside of the range of the string, there is no ending match.
            if ($offset > $len) {
                $bad = true;
                break;
            }
        }
        if (!$bad) {
            $found[] = substr($data, $val, $offset);
        }
    }
    if ($found == false) {
        return false;
    }

    if ($specific == -1) {
        if (count($found) == 1) {
            return $found[0];
        }
        return $found;
    }
    return $found[$specific-1];
}

// Updated function that finds all occurances of needle
function findall($needle, $haystack) {
    $pos = 0;
    $len = strlen($haystack);
    $searchlen = strlen($needle);
    $results = array();

    $data = $haystack;
    while (1) {
        $occurance = strpos($data, $needle);
        if ($occurance === false) {
            return $results;
        } else {
            $pos += $occurance+$searchlen;
            $results[] = $pos-$searchlen;
            $data = substr($haystack, ($pos));
        }
    }
}

function poke($id = 1, $method = 1) {
    $found = false;

    // Try to download via Novamov
    if ($method == 1) {
        $pre_data = file_get_contents("http://pokemonepisode.org/5.php?P-ID=" . $id);
        $data = file_get_contents(extractData($pre_data, "src='", "' s"));

        $file = extractData($data, 'flashvars.file="', '";');
        $key  = extractData($data, 'flashvars.filekey="', '";');
        $post_data = file_get_contents("http://www.novamov.com/api/player.api.php?file=" . $file . "&key=" . $key);

        // Detect if valid video url is found
        $val = extractData($post_data, "url=", "&title");
        if ($val != false) {
            $found = true;
            $url = $val;
        }
    // Try to download via VideoBam
    } else if ($method == 2) {
        $pre_data = file_get_contents("http://pokemonepisode.org/3.php?P-ID=" . $id);
        $data = file_get_contents(extractData($pre_data, "src=\"", "\""));

        // Detect if valid video url is found
        $val = extractData($data, "high: '", "'");
        if ($val != false) {
            $found = true;
            $url = $val;
        }
    // Try to download via DailyMotion
    } else if ($method == 3) {
        $pre_data = file_get_contents("http://pokemonepisode.org/4.php?P-ID=" . $id);
        $data = @file_get_contents(extractData($pre_data, "src=\"", "\""));

        // Detect if valid video url is found
        // Tries high quality URL first
        $val = extractData($data, "stream_h264_hq_url\":\"", "\"");
        if ($val == null) {
            // Try lower quality option if high quality not found
            $val = extractData($data, "stream_h264_url\":\"", "\"");
        }

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
	// File extension
	$headers = get_headers($url, 1);
	$ext = $headers["Content-Type"];
	if (is_array($ext)) {
		$ext = $ext[1];
	}

	if ($ext == "Content-Type: video/x-flv" || $method == 1) {
		$ext = "flv";
	} else {
		$ext = "mp4";
	}
        return array($url, $method, $ext);
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

function download($num, $title, $url, $method = 0) {
    global $seanum, $seaname;

    @mkdir("pokemon");

    // File extension
    $headers = get_headers($url, 1);
    $ext = $headers["Content-Type"];
    if (is_array($ext)) {
        $ext = $ext[1];
    }

    if ($ext == "Content-Type: video/x-flv" || $method == 1) {
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
    echo "\t\tcheck  - View episode download competion status." . ENDL;
    echo "\t save:" . ENDL;
    echo "\t\ttrue   - Save the files into organized directory hierarchy." . ENDL;
    echo "\t\tsave   - Same thing as true." . ENDL;
    echo "\t\t*false - Don't save files, only display URLs." . ENDL;
    echo "\t method:" . ENDL;
    echo "\t\t1      - Use Novamov via pokemonepisode.org to download." . ENDL;
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
        @sort($specific[$key]);
    }
    sort($episode);
    sort($season);
    return array($season, $episode, $specific);
}

function checkCompletion() {
    global $seanum, $seaname, $seaamt;
    $data = checkFiles();

    echo "Episode download completion (" . GREEN . count($data[1]) . "/" . TOTAL . WHITE . ") - " . round(count($data[1])/TOTAL*100) . "%" . ENDL;
    $a = 0;
    foreach($seaname as $val) {
        $a++;
        echo $a . ". " . PURPLE . $val . WHITE . " (" . GREEN . count($data[2][$a . " - " . $val]) . "/" . $seaamt[$a+1] . WHITE . ") - " . round(count($data[2][$a . " - " . $val])/$seaamt[$a+1]*100) . "%" . ENDL;
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

function addSkip($num) {
    $skips = getSkips(true);
    if (!in_array($num, $skips)) {
        file_put_contents(".skip", getSkips() . $num . ENDL);
    }
}

function getSkips($array = false) {
    $skips = @file_get_contents(".skip");
    if (!$array) {
        return $skips;
    }
    return explode(ENDL, $skips);
}
?>
