<?php
// データベース設定

/*
$dbServer = 'localhost';
$dbUser   = 'root';
$dbPass   = 'root';
$dbName   = 'matsushima';
*/

/*
$dbServer = 'mysql300.db.sakura.ne.jp';
$dbUser   = 'digitaling';
$dbPass   = 'bonjovi-1';
$dbName   = 'digitaling_matsushima';
*/


$dbServer = 'mysql446.db.sakura.ne.jp';
$dbUser   = 'granz';
$dbPass   = 'wkgt554byw';
$dbName   = 'granz_matsushima';


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
