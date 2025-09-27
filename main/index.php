<!doctype html>
<html>
<head>
<meta charset="UTF-8">
	<meta name="GOOGLEBOT" content="NOINDEX, NOFOLLOW" />
	<meta name="ROBOTS" content="NOINDEX, NOFOLLOW" />
	<meta http-equiv="X-UA-Compatible" content="IE=8 ; IE=9" />

<meta name="viewport" content="width=816px">

<title>スケジュール管理システム</title>
<link rel="stylesheet" type="text/css" href="../css/reset.css">
	<link href="../css/ui-lightness/jquery-ui-1.9.2.custom.css" rel="stylesheet">
	<script src="../js/jquery-1.8.3.js"></script>
	<script src="../js/jquery-ui-1.9.2.custom.js"></script>
	<script src="../js/jquery.ui.datepicker-ja.js"></script>
	<script src="../js/jquery.ui.touch-punch.min.js"></script>
	

<!--[if !IE]>
<link rel="stylesheet" type="text/css" href="http://code.jquery.com/mobile/latest/jquery.mobile.min.css" />
<link rel="stylesheet" type="text/css" href="http://dev.jtsage.com/cdn/datebox/latest/jqm-datebox.min.css" /> 
<script type="text/javascript" src="http://code.jquery.com/mobile/latest/jquery.mobile.min.js"></script>
<script type="text/javascript" src="http://dev.jtsage.com/cdn/datebox/latest/jqm-datebox.core.min.js"></script>
<script type="text/javascript" src="http://dev.jtsage.com/cdn/datebox/latest/jqm-datebox.mode.calbox.min.js"></script>
<script type="text/javascript" src="http://dev.jtsage.com/cdn/datebox/i18n/jquery.mobile.datebox.i18n.ja.utf8.js"></script>
<![endif]--> 

<script src="control.js?Ver=20250923"></script>

<style>

body {
	font-size:12px;
	line-height:1.4;
	position:relative;
}

a, a:active, a:visited {
	color:#333;
	text-decoration:none;
}

a:hover {
	color:#03C;
	text-decoration:underline;
}

#main-cal-area {
	padding:10px;
	overflow:scroll;
}

#ctr_area {
	padding-bottom:10px;
}

table.cal {
	table-layout:fixed;
	width:1px;
}

table.cal th, table.cal td{
	width:100px;
	border:1px solid #999;
}
table.cal td{
	position:relative;
	vertical-align:top;
	overflow:visible;
	padding-bottom:20px;
	height:20px;
}
table.cal th{
	background:#EEE;
}

.box {
	width:99px;
	height:66px;
	height:85px;
	height: 150px;
	background:#FFC;
	border-bottom:#CCC 1px solid;
	border-right:#CCC 1px solid;
	position:relative;
	z-index:1;
	
	overflow:hidden;
}

.boxh {
	width:99px;
	background:#ffb8b8;
	color:#FFF;
	border-bottom:#CCC 1px solid;
	border-right:#CCC 1px solid;
	position:relative;
	z-index:1;
}

.boxip {
	width:99px;
	background:#ccffe5;
	color:#FFF;
	border-bottom:#CCC 1px solid;
	border-right:#CCC 1px solid;
	position:relative;
	z-index:1;
	color:#000;
}

.boxip2 {
	width:99px;
	background:#FFFAC2;
	color:#FFF;
	border-bottom:#CCC 1px solid;
	border-right:#CCC 1px solid;
	position:relative;
	z-index:1;
	color:#000;
}

.boxip3 {
	width:99px;
	background:#a8d3ff;
	color:#FFF;
	border-bottom:#666 1px solid;
	border-right:#CCC 1px solid;
	position:relative;
	z-index:1;
	color:#000;
}

.add-btn {
	position:absolute;
	bottom:2px;
	left:2px;

	color:#CCC;
	font-size:10px;
}

.td-active {
	background:none;
}

.td-hover {
	background:#FCF;
}

.tantou-col-1 {
	background:#d1ffff;
}
.tantou-col-2 {
	background:#d1ffe8;
}
.tantou-col-3 {
	background:#d1ffd1;
}
.tantou-col-4 {
	background:#ffd6ff;
}

.tantou-col-5 {
	background:#ff7f7f;
}
.tantou-col-6 {
	background:#7fffff;
}
.tantou-col-7 {
	background:#bf7fff;
}
.tantou-col-8 {
	background:#ff7fbf;
}
.tantou-col-9 {
	background:#d1ffe8;
}
.tantou-col-10 {
	background:#7fffbf;
}
.tantou-col-11 {
	background:#ffff7f;
}
.tantou-col-12 {
	background:#ffbf7f;
}
.tantou-col-13 {
	background:#d1ffe8;
}
.tantou-col-14 {
	background:#d1ffe8;
}
.tantou-col-15 {
	background:#d1ffe8;
}
.tantou-col-17 {
	background:#C5E1A5;
}
.tantou-col-20 {
/*	background:#FFE082;*/
}
.tantou-col-22 {
	background:#E1BEE7;
}

.tantou-col-24 {
	background:#00ACC1;
}
.tantou-col-25 {
	background:#9ACD32;
}

.azuma-box {
	background:#ffd6ea;
}

.hilight-box {
	background:red;
	color:#FFF;
}

#search_area {
	padding-bottom:10px;
}

.tac {
	text-align:center;
}
.tar {
	text-align:right;
}

.buttonf {
	padding:2px 20px;
	margin-left:5px;
}

.closeButton {
	border:#999 1px solid;
	padding:5px 15px;
	text-align:center;
	margin-right:5px;
	margin-bottom:5px;
}

/*
複数日のある現場をカウントして、その数だけpaddingする
*/
.z1 {
	padding-top:100px;
}


/* icon フォーマット */

.icon_blue {
	color:#FFF;
	background:blue;
	font-family:"ＭＳ Ｐ明朝", "MS PMincho", "ヒラギノ明朝 Pro W3", "Hiragino Mincho Pro", serif;
	font-size:12px;
	font-weight:normal;
	padding:1px;
	border:#CCC 1px solid;
}

.icon_red {
	color:#FFF;
	background:red;
	font-family:"ＭＳ Ｐ明朝", "MS PMincho", "ヒラギノ明朝 Pro W3", "Hiragino Mincho Pro", serif;
	font-size:12px;
	font-weight:normal;
	padding:1px;
	border:#CCC 1px solid;
}

.icon_gray {
	color:#FFF;
	background:gray;
	font-family:"ＭＳ Ｐ明朝", "MS PMincho", "ヒラギノ明朝 Pro W3", "Hiragino Mincho Pro", serif;
	font-size:12px;
	font-weight:normal;
	padding:1px;
	border:#CCC 1px solid;
}

.icon_normal {
	color:#000;
	background:#FFF;
	font-family:"ＭＳ Ｐ明朝", "MS PMincho", "ヒラギノ明朝 Pro W3", "Hiragino Mincho Pro", serif;
	font-size:12px;
	font-weight:normal;
	padding:1px;
	border:#CCC 1px solid;
}

.icon_gencho_red {
	color:#000;
	background:#FFF;
	font-family:"ＭＳ Ｐ明朝", "MS PMincho", "ヒラギノ明朝 Pro W3", "Hiragino Mincho Pro", serif;
	font-size:12px;
	font-weight:normal;
	padding:1px;
	border:#CCC 1px solid;
}
.icon_gencho_blue {
	color:#000;
	background:#FFF;
	font-family:"ＭＳ Ｐ明朝", "MS PMincho", "ヒラギノ明朝 Pro W3", "Hiragino Mincho Pro", serif;
	font-size:12px;
	font-weight:normal;
	padding:1px;
	border:#CCC 1px solid;
}

table.add-sche-table td {
	padding-bottom:10px;
}


table.ippan-table td {
	vertical-align:top;
	padding:3px 15px;
}

table.search-result td {
	padding:1px 5px;
}

.sjump {
	border:1px solid #CCC;
	padding:5px 10px;
	margin-left:5px;
}
.sjump:hover {
	text-decoration:none;
	color:#999;
}

/* オプショナルエリア */
#optional-area {
	position:absolute;
	width:300px;
	height:300px;
	top:0px;
	left:0;
	background:#DDD;
	border:#CCC 1px solid;
	display:none;
	z-index:3;
}
#optional-area-inner {
	overflow:auto;
	width:290px;
	height:270px;
	padding:5px;
	margin-bottom:20px;
}

#optional-area .closeButton{
	position:absolute;
	right:3px;
	bottom:1px;
}

#optional-area .closeButton:hover{
	cursor:pointer;
	-ms-filter: "alpha( opacity=60 )"; 
	filter: alpha( opacity=60 ); 
	opacity: 0.6;
}
#optional-area .edit-table th {
	text-align:left;
	padding-right:15px;
}

.tel {
	text-decoration:underline;
}
.tel:hover {
	text-decoration:none;
}

/* free-icon */
.free-icon {
	color: #000;
	background-color: #fff;
	/*border:1px solid #000;*/
	font-size: 10px;
	margin-left: 5px;
	margin-right: 5px;
}	
	
/* 点滅 */
.blinking{
	-webkit-animation:blink 1.5s ease-in-out infinite alternate;
    -moz-animation:blink 1.5s ease-in-out infinite alternate;
    animation:blink 1.5s ease-in-out infinite alternate;
}
@-webkit-keyframes blink{
    0% {opacity:0.4;}
    100% {opacity:1;}
}
@-moz-keyframes blink{
    0% {opacity:0.4;}
    100% {opacity:1;}
}
@keyframes blink{
    0% {opacity:0.4;}
    100% {opacity:1;}
}

</style>

</head>

<body>

<?php
$user = $_SERVER['REMOTE_USER'];
//$user = "granz";
//$user = "user";
echo "<input type='hidden' id='user' value='{$user}' />";

if(isset($_REQUEST['sid']))
	echo "<input type='hidden' id='jump_sid' value='".$_REQUEST['sid']."' />";
if(isset($_REQUEST['date']))
	echo "<input type='hidden' id='jump_date' value='".$_REQUEST['date']."' />";
else
	echo "<input type='hidden' id='jump_date' value='' />";

?>

<div id="main-cal-area">
</div>

<div id="optional-area">
    <div id="optional-area-inner">
    </div>
	<span class='closeButton'>閉じる</span>
</div>

</body>
</html>
