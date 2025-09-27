<?php
require_once("db_connect.php");

//パラメータ受け取り
if(isset($_REQUEST['parm'])){
	$parm = $_REQUEST['parm'];
} else {
	echo "fail";
	exit();
}

//file_put_contents("sql.txt", $parm[0]);

//文字列をデコード
$sql = $parm[0];
$sql_ar = explode(';;',$sql);

$tmp_id1 = 0;
$tmp_id2 = 0;

foreach($sql_ar as $key=>$s) {
  //文字化け対象を置換して実行

  if($tmp_id1)
  	$s = preg_replace('/temp_main_id/', $tmp_id1, $s);

  if($tmp_id2)
  	$s = preg_replace('/temp_slip_id/', $tmp_id2, $s);

  $query = mysql_query($s);
  if(!$query) {
	echo "fail";
	exit();
  }

  if($key==0)
	  $tmp_id1 = mysql_insert_id();
  else if($key==1)
	  $tmp_id2 = mysql_insert_id();

//file_put_contents("sql.txt", $s, FILE_APPEND);

}

echo $tmp_id1;
