<?php
require_once("config.php");

// Version
define("VERSION", "1.1.5");
// Total number of pokemon episodes
define("TOTAL", 809);

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

function database() {
    global $config;
    try {
        $db = new PDO("mysql:host=localhost;port=3306;dbname=" . $config['db']['dbname'], $config['db']['username'], $config['db']['password']);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        return $db;
    } catch (PDOException $e) {
        echo "Uh oh, something went wrong...";
    }
}

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

function getTitle($num) {
    $data = file_get_contents("title.txt");
    $ex = explode("\n", $data);
    return $ex[$num-1];
}

function seasonNum($episode) {
    global $seanum, $seaname;
    
    for ($a = 1; $a < count($seanum); $a++) {
        if ($episode >= $seanum[$a] && $episode < $seanum[($a+1)]) {
            return $a;
        }
    }
}

function hpbar($current, $max, $total = 0) {
    if ($current == 0 && $total == 0) {
        $current = 50;
    }
    $percent = round(($current/$max)*100);
    if ($percent >= 50) {
        $color = 'rgb(139,238,132)';
    }
    if ($percent >= 20 && $percent <= 50) {
        $color = 'rgb(215,174,111)';
    }
    if ($percent <= 20) {
        $color = 'rgb(164,72,72)';
    }
    return "<div style='border-radius: 10px; -webkit-box-shadow: inset 0 2px 5px #AAA; border: 1px solid; background: #FFF; width: 100px; height: 5px; overflow: hidden; ' title='" . $percent . "% - " . $total . " vote(s)'><div style='width: " . $percent . "%; background-color: " . $color . "; border-radius: 10px; height: 5px;'></div></div>";
}

function color($current, $max) {
    $percent = round(($current/$max)*100);

    $green = round(($percent*255)/100);
    $red = 255-$green;
    if ($percent < 0) {
    $rgb = "rgb(255, 0, 00)";
    }
    return "rgb(" . $red . ", " . $green . ", 00)";
}

function addRating($id, $val) {
    $db = database();

    // Create new record
    if (!doesExist("poke", "id", $id)) {
        $statement = $db->prepare("INSERT INTO `poke` (`id` ,`rating_raw` ,`rating` ,`total`) VALUES (?, ?, ?, ?);");
        $statement->execute(array($id, $val, $val, 1));
    // Add to previous record
    } else {
        $statement = $db->prepare("UPDATE `poke` SET `rating_raw` = ?,`rating` = ?,`total` = ? WHERE `id` = ?;");
        $ex = explode(" ", (getValue($id, "rating_raw") . " " . $val));
        $avg = round(array_avg($ex));
        if (getValue($id, "total") == 0) {
            $avg = $val;
        }
        $statement->execute(array(getValue($id, "rating_raw") . " " . $val, $avg, (getValue($id, "total")+1), $id));
        #echo "UPDATE `poke` SET `rating_raw` = '" . getValue($id, "rating_raw") . "\n" . $val . "',`rating` = '" . $avg . "',`total` = '" . (getValue($id, "total")+1) . "' WHERE `id` = " . $id . ";";
        #die;
    }
}

function doesExist($table, $fieldname, $value) {
    $db = database();
    $statement = $db->prepare("SELECT * FROM $table WHERE $fieldname = ?");
    $statement->execute(array($value));
    $info = $statement->FetchObject();
    if ($info != null) {
        return 1;
    } else {
        return 0;
    }
}

function getValue($id, $fieldname) {
    $db = database();
    $statement = $db->prepare("SELECT * FROM `poke` WHERE `id` = ?");
    $statement->execute(array($id));
    $info = $statement->fetchObject();

    return $info->$fieldname;
}

function array_avg($array) {
    return round(array_sum($array)/count($array), 2);
}

function alreadyRated() {
    $ex = explode("A", $_COOKIE['already']);
    return $ex;
}

function addDownload($id) {
    $db = database();

    // Create new record
    if (!doesExist("poke", "id", $id)) {
        $statement = $db->prepare("INSERT INTO `poke` (`id`, `downloads`) VALUES (?, ?);");
        $statement->execute(array($id, 1));
    // Add to previous record
    } else {
        $statement = $db->prepare("UPDATE `poke` SET `downloads` = ? WHERE `id` = ?;");
        $statement->execute(array((getValue($id, "downloads")+1), $id));
    }
}
?>