<?php
$datetime = new DateTime($time, new DateTimeZone('UTC'));
$seconds_ago = time() - $datetime->format('U');
$ago = "";
if ($seconds_ago >= 31536000) {
    $ago = "" . intval($seconds_ago / 31536000) . " years ago";
} elseif ($seconds_ago >= 2419200) {
    $ago = "" . intval($seconds_ago / 2419200) . " months ago";
} elseif ($seconds_ago >= 86400) {
    $ago = "" . intval($seconds_ago / 86400) . " days ago";
} elseif ($seconds_ago >= 3600) {
    $ago = "" . intval($seconds_ago / 3600) . " hours ago";
} elseif ($seconds_ago >= 60) {
    $ago = "" . intval($seconds_ago / 60) . " minutes ago";
} else {
    $ago = "Less than a minute ago";
}
?><time datetime="<?= $datetime->format('c') ?>"><?= $ago ?></time>
