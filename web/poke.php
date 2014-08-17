<?php
require_once("global.php");

$mirror = $_GET['mirror'];
$id = $_GET['id'];

if (($id < 1 && $id > TOTAL) || !is_numeric($id)) {
    header('Location: index.php');
    die;
}

if ($mirror == 1) {
    $episode = poke($id, 1);
} else if ($mirror == 2) {
    $episode = poke($id, 2);
} else if ($mirror == 3) {
    $episode = poke($id, 3);
} else if ($mirror == 4) {
    $episode = poke($id, 4);
} else if ($mirror == 5) {
    $episode = poke($id, 5);
} else {
    header('Location: index.php');
    die;
}
$headers = get_headers($episode[0], 1);
$ext = $headers['Content-Type'][1];
if ($ext == "video/x-flv") {
    $ext = "flv";
} else {
    $ext = "mp4";
}

if (is_array($headers['Content-Length'])) {
    $length = $headers['Content-Length'][1];
} else {
    $length = $headers['Content-Length'];
}

addDownload($id);
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="[' . $id . '] - ' . getTitle($id) . '.' . $ext . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . $length);
ob_clean();
flush();
readfile($episode[0]);
exit;
?>
