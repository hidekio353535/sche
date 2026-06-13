<?php
// データベース設定

$dbServer = '';
$dbUser   = '';
$dbPass   = '';
$dbName   = '';


$flag = TRUE;
// MySQLデータベースに接続
if (!$link = mysql_connect($dbServer, $dbUser, $dbPass)) {
  die('Connection Error');
}
// データベース選択
else if (!mysql_select_db($dbName, $link)) {
  die('Select db Error');
}
mysql_query("SET NAMES utf8",$link); 

?>
