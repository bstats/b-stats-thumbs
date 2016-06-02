<?php
function dlUrl($url){
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_TIMEOUT, 60);
  curl_setopt($ch, CURLOPT_ENCODING, "gzip");
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
  $data = curl_exec($ch);
  curl_close($ch);
  return $data;
}
$cfg = json_decode(file_get_contents("cfg.json"), true);
$db = new mysqli($cfg['mysql']['host'], $cfg['mysql']['username'], $cfg['mysql']['password'], $cfg['mysql']['database']);
$db->query(file_get_contents("init.sql"));
define("EXEC_TIME",$cfg['cycle_time']);
$i = 0;
$boards = $cfg['boards'];
$site = $cfg['site'];

while(true){
  $starttime = time();
  $bad_hashes = json_decode(dlUrl("$site/api/bannedHashes"),true);
  foreach($bad_hashes as $hash){
    $q =  $db->query("DELETE FROM `thumbs` WHERE `md5` = UNHEX('$hash')");
  }

  foreach($boards as $board){
    echo time().": Starting download for $board...".PHP_EOL;

    $checkstmt = $db->prepare("SELECT `md5`,`op` FROM `thumbs` WHERE `md5`=?");

    $insertstmt = $db->prepare("INSERT INTO `thumbs` (`md5`,`data`,`op`) "
                             . "VALUES (?,?,?) ON DUPLICATE KEY UPDATE `data`=VALUES(`data`),`op`=VALUES(`op`)");

                             $checkstmt->bind_param("s",$bin);
    $checkstmt->bind_result($selected_md5, $selected_op);
    $insertstmt->bind_param("ssi",$bin,$data,$op);
    $media = json_decode(dlUrl("$site/api/board/$board/activeMedia"),true);
    foreach($media as $item){
      $bin = base64_decode(str_replace("-","/",$item['md5']));
      $op = $item['op'];
      if(in_array(bin2hex($bin),$bad_hashes)){ echo "*"; continue; }
      $checkstmt->execute();
      $checkstmt->store_result();
      if($checkstmt->num_rows > 0) {
        $checkstmt->fetch();
        if($item['op'] != 1)
          continue;
        if($selected_op == 1)
          continue;
      }
      $data = dlUrl(str_replace(
              ['%board%','%ext%','%tim%','%filename%'],
              [$board, $item['ext'], $item['tim'], $item['filename']],
              $cfg['format']));
      if($data !== FALSE && strpos($data,"<html>") !== 0){
        $insertstmt->execute();
        if($insertstmt->errno)
          echo "e";
        else{
         echo $item['op'] ? 'o' : ".";
        }
      }
      else{
        echo "x";
      }
    }
    echo PHP_EOL;
  }
  $diff = time() - $starttime;
  if($diff < EXEC_TIME){
    $sleep = EXEC_TIME - $diff;
    echo "Sleeping $sleep sec(s)...".PHP_EOL;
    @sleep($sleep);
  }
}
