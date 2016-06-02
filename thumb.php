<?php
/*
 * Retrieve a thumbnail from the database.
 * EXAMPLE: 
 *   GET /thumb.php?hex=00000000000000001234567890ABCDEF
 *   GET /thumb.php?base64=abcdefghijklmnopqrstuv==
 */
function tryget($name){
  if(isset($_SERVER[$name])) return $_SERVER[$name];
  return "";
}

error_reporting(E_ALL);

if(isset($_GET['base64'])){
  $md5 = bin2hex(base64_decode(str_replace("-","/",$_GET['base64'])));
} elseif(isset($_GET['hex'])){
  $md5 = $db->real_escape_string($_GET['hex']);
} else{
  exit;
}

if(trim(tryget('HTTP_IF_NONE_MATCH')) == $md5){
  header("HTTP/1.1 304 Not Modified");
  exit;
}

$cfg = json_decode(file_get_contents("cfg.json"), true);
$db = new mysqli($cfg['mysql']['host'], $cfg['mysql']['username'], $cfg['mysql']['password'], $cfg['mysql']['database']);

$q = $db->query("SELECT `data` FROM `thumbs` WHERE `md5`=X'$md5'");
$row = $q->fetch_assoc();
if(strlen($row['data']) > 0){
  if($data[0] == "\x89") {
    header("Content-Type: image/png");
  } else {
    header("Content-Type: image/jpeg");
  }
  header("Expires: ".gmdate('D, d M Y H:i:s \G\M\T', time() + 2592000));
  header("Cache-Control: max-age=172800, public");
  header("Etag: $md5");
  echo $row['data'];
}
else{
  header("Location: nothumb.jpg");
}
exit;
