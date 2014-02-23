<?php
$seanum = array(1 => 1, 83, 119, 160, 212, 277, 317, 369, 423, 469, 521, 573, 626, 660, 710, 759, 784, 804, 808);
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
    return false;
}

function poke($id = 1, $method = 1) {
    // To fix the method number the user selects
    --$method;

    $found = false;

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

function pokeTitle($num) {
    $data = file_get_contents("http://pokemonepisode.org/episode-" . $num);
    $title = extractData($data, "&#8211; ", "<", 1);
    return $title;
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
    @shell_exec("wget -O \"pokemon/" . seasonNum($num) . " - " . $seaname[seasonNum($num)] . "/[" . $num . "] - " . $title . "." . $ext . "\" " . $url . " --continue");

}

function seasonNum($episode) {
    global $seanum, $seaname;
    
    for ($a = 1; $a < count($seanum); $a++) {
        if ($episode >= $seanum[$a] && $episode < $seanum[($a+1)]) {
            return $a;
        }
    }
}
?>