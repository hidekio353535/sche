<?PHP
require_once("db_connect.php");

if(isset($_REQUEST['id']))
	$id = $_REQUEST['id'];
else {
	echo "<p>現場IDが不正です。</p>";
	exit();	
}
if($id == "" || !$id) {
	echo "<p>現場IDが不正です。</p>";
	exit();	
}

if(isset($_REQUEST['mode']))
	$mode = $_REQUEST['mode'];
else
	$mode = "";

//フィールド配列
$t_fields = array();

//現場情報セット
$sql = "SELECT * FROM matsushima_genba 
			LEFT OUTER JOIN matsushima_moto ON matsushima_moto.moto_id = matsushima_genba.g_moto_id
			LEFT OUTER JOIN matsushima_tantou ON matsushima_tantou.t_id = matsushima_genba.g_tantou_id
			
			
			
			WHERE g_id = '{$id}'";
$query = mysql_query($sql);
$num = mysql_num_rows($query);

while ($row = mysql_fetch_object($query)) {
	$t_fields['colmun_1'] = $row->moto;
	$t_fields['colmun_2'] = $row->g_genba;
	$t_fields['colmun_3'] = $row->g_genba_address;
	$t_fields['colmun_4'] = $row->g_moto_tantou;
	$t_fields['colmun_5'] = $row->g_moto_tantou_tel;
	$t_fields['colmun_7'] = $row->t_tantou;

	$sql_hat = "select * from matsushima_slip_hat 
							left outer join matsushima_seko ON matsushima_seko.seko_id = matsushima_slip_hat.s_seko_id
							where 
							s_genba_id =  '{$id}' 
							and
							s_seko_id != 0
							and
							s_seko_id is not null
							
							group by s_seko_id
							order by s_seko_id
							";
	$query_hat = mysql_query($sql_hat);
	while ($row_hat = mysql_fetch_object($query_hat)) {
		$t_fields['colmun_6'] .= $row_hat->seko_nik."/";
	}
	$t_fields['colmun_6'] = preg_replace('/\/$/', '', $t_fields['colmun_6']);
}

//初期値セット
$sql = "SELECT * FROM matsushima_sheet WHERE st_s_id = '{$id}'";
$query = mysql_query($sql);
$num = mysql_num_rows($query);

while ($row = mysql_fetch_object($query)) {
	$t_fields[$row->st_key] = $row->st_val;
}

//予定日取得
//建
$sql_gc = "SELECT * FROM `matsushima_slip_hat` 
				WHERE s_genba_id = '{$id}' AND (s_seko_kubun_id = 1 OR s_seko_kubun_id = 2) ORDER BY `matsushima_slip_hat`.`s_id`  DESC LIMIT 1";
$query_gc = mysql_query($sql_gc);
$num_gc = mysql_num_rows($query_gc);
if($num_gc) {
	$row_gc = mysql_fetch_object($query_gc);
	if($row_gc->s_st_date != "0000-00-00")
		$t_fields['colmun_12'] = date('Y年m月d日', strtotime($row_gc->s_st_date));
}

//払
$sql_gc = "SELECT * FROM `matsushima_slip_hat` 
				WHERE s_genba_id = '{$id}' AND (s_seko_kubun_id = 3) ORDER BY `matsushima_slip_hat`.`s_id`  DESC LIMIT 1";
$query_gc = mysql_query($sql_gc);
$num_gc = mysql_num_rows($query_gc);
if($num_gc) {
	$row_gc = mysql_fetch_object($query_gc);
	if($row_gc->s_st_date != "0000-00-00")
		$t_fields['colmun_13'] = date('Y年m月d日', strtotime($row_gc->s_st_date));
}

//塞
$sql_gc = "SELECT * FROM `matsushima_slip_hat` 
				WHERE s_genba_id = '{$id}' AND (s_seko_kubun_id = 4) ORDER BY `matsushima_slip_hat`.`s_id`  DESC LIMIT 1";
$query_gc = mysql_query($sql_gc);
$num_gc = mysql_num_rows($query_gc);
if($num_gc) {
	$row_gc = mysql_fetch_object($query_gc);
	if($row_gc->s_st_date != "0000-00-00")
		$t_fields['colmun_14'] = date('Y年m月d日', strtotime($row_gc->s_st_date));
}

//盛
$sql_gc = "SELECT * FROM `matsushima_slip_hat` 
				WHERE s_genba_id = '{$id}' AND (s_seko_kubun_id = 5) ORDER BY `matsushima_slip_hat`.`s_id`  DESC LIMIT 1";
$query_gc = mysql_query($sql_gc);
$num_gc = mysql_num_rows($query_gc);
if($num_gc) {
	$row_gc = mysql_fetch_object($query_gc);
	if($row_gc->s_st_date != "0000-00-00")
		$t_fields['colmun_15'] = date('Y年m月d日', strtotime($row_gc->s_st_date));
}


//現調情報取得
$sql_gc = "SELECT * FROM `matsushima_slip_sche` 
				LEFT OUTER JOIN matsushima_tantou ON matsushima_tantou.t_id = matsushima_slip_sche.s_tantou_id
				WHERE s_genba_id = '{$id}' ORDER BY `matsushima_slip_sche`.`s_id`  DESC LIMIT 1";
$query_gc = mysql_query($sql_gc);
$num_gc = mysql_num_rows($query_gc);
if($num_gc) {
	$row_gc = mysql_fetch_object($query_gc);
	
	if($row_gc->s_st_date != "0000-00-00")
		$t_fields['colmun_34'] = date('Y年m月d日', strtotime($row_gc->s_st_date));
	
	$t_fields['colmun_37'] = $row_gc->t_tantou;
	
}


//表示専用
function show_span($_id , $_class) {
	global $t_fields;
	global $mode;

	return '<span id="'.$_id.'" class="'.$_class.'" >' . $t_fields[$_id] . '</span>';
}

//フィールド表示関数
function show_field($_id , $_class , $_size) {

	global $t_fields;
	global $mode;
	
	if($mode == "prn")
		return $t_fields[$_id];
	else
		return '<input type="text" id="' . $_id. '" class="' . $_class . '" size="' . $_size . '" value="' . $t_fields[$_id] . '"  />';

}

//フィールド表示関数
function show_textarea($_id) {

	global $t_fields;
	global $mode;
	
	if($mode == "prn")
		return '<span style="color:red">' . nl2br($t_fields[$_id]) . '</span>';
	else
		return '<textarea id="' . $_id. '" class="' . $_class . ' biko_naka"  rows="5" cols="60">' . $t_fields[$_id] . '</textarea>';

}

function show_checkbox($_id , $_class , $_size, $_label = "") {

	global $t_fields;
	global $mode;
	
	if($mode == "prn") {
		if($t_fields[$_id])
			return '<img src="../img/reten.jpg" width="14" height="14"><span style="font-weight:bold;font-size:10pt;color:red">' . $_label . '</span>';
		else
			return $_label;
	}
	else {
		if($t_fields[$_id])
			return '<input type="checkbox" id="'.$_id.'" class="'.$_class.'" value="1" checked="checked" /> <label for="'.$_id.'">' . $_label . '</label>';
		else
			return '<input type="checkbox" id="'.$_id.'" class="'.$_class.'" value="0"  /> <label for="'.$_id.'">' . $_label . '</label>';
	}
}

if($mode == "prn") {
$css = '
<style type="text/css">

		table {
			margin:0;
			padding:0;
			font-size:8pt;
		}
		
		td {
			vertical-align:middle;
		}
		
		.waku {
			border: 1px solid #000;
		}
		.waku-t {
			border-top: 1px solid #000;
		}
		.waku-l {
			border-left: 1px solid #000;
		}
		.waku-r {
			border-right: 1px solid #000;
		}
		.waku-b {
			border-bottom: 1px solid #000;
		}
	
		.waku2 {
			border: 2px solid #000;
		}
		.waku2-t {
			border-top: 2px solid #000;
		}
		.waku2-l {
			border-left: 2px solid #000;
		}
		.waku2-r {
			border-right: 2px solid #000;
		}
		.waku2-b {
			border-bottom: 2px solid #000;
		}
		
		.bgkuro {
			color: #FFF;
			background-color: #333;
		}
		.size0 {
			font-size:1pt;
		}
		.size8 {
			font-size:8pt;
		}
		.size9 {
			font-size:9pt;
		}
		.size10 {
			font-size:10pt;
		}
		.size11 {
			font-size:11pt;
		}
		.size12 {
			font-size:12pt;
		}
		.size13 {
			font-size:13pt;
		}
		.size14 {
			font-size:14pt;
		}
		.size15 {
			font-size:15pt;
		}
		.size16 {
			font-size:16pt;
		}
		.size17 {
			font-size:17pt;
		}
		.size18 {
			font-size:18pt;
		}
		.size20 {
			font-size:20pt;
		}
		.size22 {
			font-size:22pt;
		}
		.size24 {
			font-size:24pt;
		}
		.tac {
			text-align:center !important;
		}
		.tal {
			text-align:left !important;
		}
		.tar {
			text-align:right !important;
		}
		
		.uline {
			border-bottom:solid 1px #000;
		}
		
		.space {
		}

		table#sheet-table {
		}
		table#sheet-table tr {
		}
		table#sheet-table td {
			border:1px solid #666;
			text-align:center;
		}

		td.title {
			background-color:#DBEEF4;
			font-weight:bold;
			text-align:center;
			vertical-align:middle  !important;
		}
		
		td.nbb {
			border-bottom:none !important;
		}
		
		h2.main-title {
			font-size:16pt;
			margin:0 !important;
			padding:0 !important;
			margin-bottom:1% !important;
			margin-left:1.5% !important;
		}
		
		.biko {
			height:100px;
			text-align:left !important;
		}

</style>
<span style="text-align:right;padding:0;margin:0;font-size:8pt">現場ID : ' . $id . '</span>
';
}
else {
$css = '
<style type="text/css">

		table#sheet-table {
			width:97%;
			margin:0 1.5%;
			table-layout:fixed;
		}
		table#sheet-table tr {
		}
		table#sheet-table td {
			border:1px solid #666;

			width:12%;
			padding:0 0.5%;
			vertical-align:middle;
			font-size:12px;
			
			text-align:center;

		}
		
		td.title {
			background:#DBEEF4;
			font-weight:bold;
			text-align:center;
			vertical-align:middle  !important;
		}
		
		td.nbb {
			border-bottom:none !important;
		}
		
		h2.main-title {
			font-size:21px;
			margin:1% 1.5%;
		}

		.tac {
			text-align:center  !important;
		}
		.tal {
			text-align:left  !important;
		}
		.tar {
			text-align:right  !important;
		}
		.biko_naka {
			font-size:12px  !important;
			font-weight:normal !important;
			color:red !important;
		}
		
</style>
';
}

$html_head = '
<h2 class="main-title">GRANZ株式会社 作業指示書</h2>
<table cellspacing="0" cellpadding="3" id="sheet-table">
		<col width="80">
		<col width="80">
		<col width="80">
		<col width="80">
		<col width="80">
		<col width="80">
		<col width="80">
		<col width="80">
		<tr>
				<td class="title">顧客名</td>
				<td colspan="7"  class="tal">' .  show_span("colmun_1" , "tal") . '</td>
		</tr>
		<tr>
				<td class="title">現場名</td>
				<td colspan="7" class="tal">' .  show_span("colmun_2" , "tal") . '</td>
		</tr>
		<tr>
				<td class="title">現場住所</td>
				<td colspan="7" class="tal">' .  show_span("colmun_3" , "tal") . '</td>
		</tr>
		<tr>
				<td class="title">元請担当者</td>
				<td class="tac">' .  show_span("colmun_4" , "tal") . '様</td>
				<td colspan="2" class="tac"> TEL　' .  show_span("colmun_5" , "tac") . '</td>
				<td class="title">発注先</td>
				<td class="tac">' .  show_span("colmun_6" , "tac") . '</td>
				<td class="title">担当営業</td>
				<td class="tac">' .  show_span("colmun_7" , "tac") . '</td>
		</tr>
';
$html = $css . $html_head;
$html .= '
		<tr>
				<td class="title">作業時間</td>
				<td>' .  show_checkbox("colmun_8" , "tac" , 2, "ＡＭ") . ' </td>
				<td>・</td>
				<td>' .  show_checkbox("colmun_9" , "tac" , 2, "ＰＭ") . ' </td>
				<td></td>
				<td>' .  show_field("colmun_10" , "tac" , 2) . ' 時</td>
				<td>～</td>
				<td>' .  show_field("colmun_11" , "tac" , 2) . ' 時</td>
		</tr>
		<tr>
				<td class="title" width="80">予定日</td>
				<td width="120">建 ' .  show_span("colmun_12" , "tac" ) . '</td>
				<td width="26"></td>
				<td width="120">払 ' .  show_span("colmun_13" , "tac" ) . '</td>
				<td width="26"></td>
				<td width="120">塞 ' .  show_span("colmun_14" , "tac" ) . '</td>
				<td width="26"></td>
				<td width="120">盛 ' .  show_span("colmun_15" , "tac" ) . '</td>
		</tr>
		<tr>
				<td class="title" width="80">上棟予定日</td>
				<td width="80">' .  show_field("colmun_16" , "tac" , 2) . ' 月</td>
				<td width="80">' .  show_field("colmun_17" , "tac" , 2) . ' 日</td>
				<td width="80"></td>
				<td class="title" width="80">土台予定日</td>
				<td width="80">' .  show_field("colmun_18" , "tac" , 2) . ' 月</td>
				<td width="80">' .  show_field("colmun_19" , "tac" , 2) . ' 日</td>
				<td width="78"></td>
		</tr>
		<tr>
				<td class="title">建方</td>
				<td colspan="7" class="tal">' .  show_checkbox("colmun_20" , "tac" , 2, "外部足場") . '・' .  show_checkbox("colmun_21" , "tac" , 2, "内部足場") . ' ・' .  show_checkbox("colmun_22" , "tac" , 2, "吊足場") . ' ・' .  show_checkbox("colmun_23" , "tac" , 2,"他足場") . ' ・' .  show_checkbox("colmun_24" , "tac" , 2, "ローリング") . '</td>
		</tr>
		<tr>
				<td rowspan="2" class="title">足場使用目的</td>
				<td colspan="7" class="tal">' .  show_checkbox("colmun_25" , "tac" , 2, "新築") . '（先行開口' .  show_checkbox("colmun_26" , "tac" , 2, "有") . ' ・ ' .  show_checkbox("colmun_27" , "tac" , 2, "無") . '）・' .  show_checkbox("colmun_28" , "tac" , 2, "上棟後") . ' </td>
		</tr>
		<tr>
				<td colspan="7" class="tal"> ' .  show_checkbox("colmun_29" , "tac" , 2, "改修") . ' （' .  show_checkbox("colmun_30" , "tac" , 2, "塗装用") . ' ・' .  show_checkbox("colmun_31" , "tac" , 2, "解体用") . ' ・' .  show_checkbox("colmun_32" , "tac" , 2, "屋根のみ") . ' ・' .  show_checkbox("colmun_33" , "tac" , 2, "太陽光") . ' ）</td>
		</tr>
		<tr>
				<td class="title">現調</td>
				<td colspan="4">' .  show_span("colmun_34" , "tac") . '</td>
				<td>現調者</td>
				<td colspan="2">' .  show_span("colmun_37" , "tac") . ' </td>
		</tr>
		<tr>
				<td class="title">面積</td>
				<td>延床 ' .  show_field("colmun_38" , "tac" , 2) . ' ㎡ </td>
				<td>   ・</td>
				<td>架面積 ' .  show_field("colmun_39" , "tac" , 4) . ' ㎡</td>
				<td>・</td>
				<td>' .  show_checkbox("colmun_40" , "tac" , 2,"部分架け") . ' </td>
				<td></td>
				<td>' .  show_checkbox("colmun_41" , "tac" , 2,"面架け") . ' </td>
		</tr>
		<tr>
				<td class="title">シート</td>
				<td colspan="7" class="tal">' .  show_checkbox("colmun_42" , "tac" , 2,"無") . ' ・' .  show_checkbox("colmun_43" , "tac" , 2,"有") . ' （' .  show_checkbox("colmun_44" , "tac" , 2,"東") . ' ' .  show_checkbox("colmun_45" , "tac" , 2,"西") . ' ' .  show_checkbox("colmun_46" , "tac" , 2,"南") . ' ' .  show_checkbox("colmun_47" , "tac" , 2,"北") . ' ）' .  show_checkbox("colmun_48" , "tac" , 2,"メッシュ") . ' ・' .  show_checkbox("colmun_49" , "tac" , 2,"イメージ") . ' ・' .  show_checkbox("colmun_50" , "tac" , 2,"先方") . ' ・' .  show_checkbox("colmun_51" , "tac" , 2,"防炎") . ' ・ ' .  show_checkbox("colmun_52" , "tac" , 2,"防音") . ' </td>
		</tr>
		<tr>
				<td class="title">車両</td>
				<td colspan="7" class="tal">' .  show_checkbox("colmun_53" , "tac" , 2,"ショート") . ' ・' .  show_checkbox("colmun_54" , "tac" , 2,"3ｔロング") . ' ・' .  show_checkbox("colmun_55" , "tac" , 2,"3ｔワイド") . ' ・' .  show_checkbox("colmun_56" , "tac" , 2,"４ｔ") . ' ・' .  show_checkbox("colmun_57" , "tac" , 2,"大型") . ' ・' .  show_checkbox("colmun_58" , "tac" , 2,"他パーキング") . ' </td>
		</tr>
		<tr>
				<td class="title">道路使用申請</td>
				<td colspan="3">' .  show_checkbox("colmun_59" , "tac" , 2,"有") . ' ・'  .  show_checkbox("colmun_61" , "tac" , 2,"無") . ' ' .  show_field("colmun_62" , "tac" , 2) . ' 月' .  show_field("colmun_63" , "tac" , 2) . '日～' .  show_field("colmun_64" , "tac" , 2) . '月' .  show_field("colmun_65" , "tac" , 2) . '日まで</td>
				<td class="title">道路占用</td>
				<td colspan="3">' .  show_checkbox("colmun_66" , "tac" , 2,"有") . ' ・' .  show_checkbox("colmun_67" , "tac" , 2,"無") . ' ' .  show_field("colmun_68" , "tac" , 2) . ' 月' .  show_field("colmun_69" , "tac" , 2) . ' 日～' .  show_field("colmun_70" , "tac" , 2) . ' 月' .  show_field("colmun_71" , "tac" , 2) . ' 日まで</td>
		</tr>
		<tr>
				<td class="title">警備手配</td>
				<td colspan="3">' .  show_checkbox("colmun_72" , "tac" , 2,"有") . '・' .  show_checkbox("colmun_73" , "tac" , 2,"無") . ' ' .  show_field("colmun_74" , "tac" , 2) . '名' .  show_field("colmun_75" , "tac" , 2) . '月' .  show_field("colmun_76" , "tac" , 2) . '日～' .  show_field("colmun_77" , "tac" , 2) . '月 ' .  show_field("colmun_78" , "tac" , 2) . '日</td>
				<td class="title"></td>
				<td></td>
				<td></td>
				<td></td>
		</tr>
		<tr>
				<td class="title">足場仕様</td>
				<td colspan="3">' .  show_checkbox("colmun_79" , "tac" , 2,"通常") . ' ・' .  show_checkbox("colmun_80" , "tac" , 2,"本足場") . ' ・（内' .  show_field("colmun_81" , "tac" , 4) . '本・外 ' .  show_field("colmun_82" , "tac" , 4) . '本）</td>
				<td class="title">屋根工事</td>
				<td colspan="2">' .  show_checkbox("colmun_83" , "tac" , 2,"有") . ' ・' .  show_checkbox("colmun_84" , "tac" , 2,"無") . ' </td>
				<td></td>
		</tr>
		<tr>
				<td class="title">屋根足場</td>
				<td>' .  show_checkbox("colmun_85" , "tac" , 2,"有") . ' </td>
				<td colspan="2">( ' .  show_field("colmun_86" , "tac" , 4) . ' ㎡)     </td>
				<td colspan="2" class="title">足場高さ屋根上</td>
				<td>H  ' .  show_field("colmun_85-2" , "tac" , 4) . ' ｍ </td>
				<td>' .  show_checkbox("colmun_86-2" , "tac" , 2,"４") . '・' .  show_checkbox("colmun_86-3" , "tac" , 2,"５") . '・' .  show_checkbox("colmun_86-4" , "tac" , 2,"６") . '</td>
		</tr>
		<tr>
				<td class="title">階段</td>
				<td>' .  show_checkbox("colmun_87" , "tac" , 2,"有") . ' ・</td>
				<td>' .  show_checkbox("colmun_88" , "tac" , 2,"無") . ' ・</td>
				<td>' .  show_checkbox("colmun_89" , "tac" , 2,"梯子") . ' </td>
				<td></td>
				<td colspan="2"></td>
				<td></td>
		</tr>
		<tr>
				<td class="title">小運搬</td>
				<td>' .  show_checkbox("colmun_90" , "tac" , 2,"有") . ' （' .  show_field("colmun_91" , "tac" , 4) . 'm）</td>
				<td>' .  show_checkbox("colmun_92" , "tac" , 2,"一輪車") . ' </td>
				<td>' .  show_checkbox("colmun_93" , "tac" , 2,"必要") . ' </td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
		</tr>
		<tr>
				<td class="title">荷上</td>
				<td colspan="7">エレベーター使用（' .  show_checkbox("colmun_94" , "tac" , 2,"可") . ' ・' .  show_checkbox("colmun_95" , "tac" , 2,"不可") . ' ）・' .  show_checkbox("colmun_96" , "tac" , 2,"レッカー") . ' ・' .  show_checkbox("colmun_97" , "tac" , 2,"他") . ' </td>
		</tr>
		<tr>
				<td class="title">下屋</td>
				<td>' .  show_checkbox("colmun_98" , "tac" , 2, "同日") . ' </td>
				<td>・</td>
				<td>' .  show_checkbox("colmun_99" , "tac" , 2, "別工程") . ' </td>
				<td class="title">ベランダ"</td>
				<td>' .  show_checkbox("colmun_101" , "tac" , 2, "同日") . ' </td>
				<td>・</td>
				<td>' .  show_checkbox("colmun_102" , "tac" , 2, "別工程") . ' </td>
		</tr>
		<tr>
				<td class="title">建物構造</td>
				<td>' .  show_checkbox("colmun_103" , "tac" , 2, "木造") . ' </td>
				<td>' .  show_checkbox("colmun_104" , "tac" , 2, "ＡＬＣ") . ' </td>
				<td>' .  show_checkbox("colmun_105" , "tac" , 2, "ＲＣ") . ' </td>
				<td class="title">壁つなぎ</td>
				<td colspan="3">' .  show_checkbox("colmun_106" , "tac" , 2, "ＯＫ") . ' ・' .  show_checkbox("colmun_107" , "tac" , 2, "NG") . ' </td>
		</tr>
		<tr>
				<td class="title">朝顔養生</td>
				<td>' .  show_checkbox("colmun_108" , "tac" , 2, "有") . ' （' .  show_field("colmun_109" , "tac" , 4) . 'ｍ）</td>
				<td>' .  show_checkbox("colmun_110" , "tac" , 2, "コンパネ") . ' ・</td>
				<td>' .  show_checkbox("colmun_111" , "tac" , 2, "足場板") . ' </td>
				<td class="title">壁つなぎ</td>
				<td>' .  show_checkbox("colmun_112" , "tac" , 2, "引掛") . ' </td>
				<td>・</td>
				<td>' .  show_checkbox("colmun_113" , "tac" , 2, "やらず") . ' </td>
		</tr>
		<tr>
				<td class="title">層間ネット</td>
				<td>' .  show_checkbox("colmun_114" , "tac" , 2, "有") . ' （' .  show_field("colmun_115" , "tac" , 4) . 'ｍ）</td>
				<td>' .  show_checkbox("colmun_116" , "tac" , 2, "自社") . ' ・</td>
				<td>' .  show_checkbox("colmun_117" , "tac" , 2, "リース") . ' </td>
				<td class="title">壁つなぎ</td>
				<td>' .  show_checkbox("colmun_118" , "tac" , 2, "2層2スパン") . ' </td>
				<td>' .  show_checkbox("colmun_119" , "tac" , 2, "2層3スパン") . ' </td>
				<td>・' .  show_checkbox("colmun_120" , "tac" , 2, "他") . ' </td>
		</tr>
		<tr>
				<td class="title">車庫部開口</td>
				<td colspan="2">' .  show_checkbox("colmun_121" , "tac" , 2, "有") . ' （' .  show_field("colmun_122" , "tac" , 4) . 'ｍ）</td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
		</tr>
		<tr>
				<td class="title">養生</td>
				<td>' .  show_checkbox("colmun_123" , "tac" , 2, "スポンジ") . ' ・</td>
				<td>' .  show_checkbox("colmun_124" , "tac" , 2, "養生シート") . ' ・</td>
				<td>' .  show_checkbox("colmun_125" , "tac" , 2, "カラーコーン") . '  ・</td>
				<td>' .  show_checkbox("colmun_126" , "tac" , 2, "バー") . '  ・</td>
				<td>' .  show_checkbox("colmun_127" , "tac" , 2, "コンパネ") . ' </td>
				<td></td>
				<td></td>
		</tr>
		<tr>
				<td class="title">仮囲工事</td>
				<td>' .  show_checkbox("colmun_128" , "tac" , 2, "有") . '・' .  show_checkbox("colmun_129" , "tac" , 2, "無") . ' </td>
				<td>' .  show_field("colmun_130" , "tac" , 4) . 'm</td>
				<td colspan="2">(' .  show_checkbox("colmun_131" , "tac" , 2, "シート") . ' ・' .  show_checkbox("colmun_132" , "tac" , 2, "防炎") . ' ・' .  show_checkbox("colmun_133" , "tac" , 2, "鋼板") . ' ・' .  show_checkbox("colmun_134" , "tac" , 2, "アドフラット") . ' ）</td>
				<td>H  ' .  show_field("colmun_134-2" , "tac" , 4) . ' m</td>
				<td>ゲート</td>
				<td>' .  show_checkbox("colmun_135" , "tac" , 2, "有") . ' ・' .  show_checkbox("colmun_136" , "tac" , 2, "無") . '</td>
		</tr>
		<tr>
				<td class="title">ローリング</td>
				<td>' .  show_field("colmun_137" , "tac" , 4) . ' 基</td>
				<td colspan="6">（' .  show_checkbox("colmun_138" , "tac" , 2, "階段") . ' ・' .  show_checkbox("colmun_139" , "tac" , 2, "梯子") . ' ・' .  show_checkbox("colmun_140" , "tac" , 2, "無") . ' ）' .  show_checkbox("colmun_141" , "tac" , 2, "３") . ' ・' .  show_checkbox("colmun_142" , "tac" , 2, "４") . ' ・' .  show_checkbox("colmun_143" , "tac" , 2, "５") . ' ・' .  show_checkbox("colmun_144" , "tac" , 2, "６") . '　' .  show_field("colmun_144-2" , "tac" , 3) . ' 段　腰手摺' .  show_checkbox("colmun_145" , "tac" , 2, "有") . ' ・' .  show_checkbox("colmun_146" , "tac" , 2, "無") . ' </td>
		</tr>
		<tr>
				<td class="title">リース手配</td>
				<td>' .  show_checkbox("colmun_147" , "tac" , 2, "有") . ' ・' .  show_checkbox("colmun_148" , "tac" , 2, "無") . ' </td>
				<td colspan="3">品目（' .  show_checkbox("colmun_149" , "tac" , 2, "トイレ") . ' ' .  show_checkbox("colmun_150" , "tac" , 2, "水洗") . ' ・' .  show_checkbox("colmun_151" , "tac" , 2, "軽水洗") . ' ）</td>
				<td>他（' .  show_field("colmun_152" , "tac" , 4) . '</td>
				<td></td>
				<td></td>
		</tr>
		<tr>
				<td rowspan="5" class="title">注意事項</td>
				<td colspan="7" class="nbb tal" style="text-align:left;color:blue">１、挨拶と掃除、現場マナーは必ず守ること！！</td>
		</tr>
		<tr>
				<td colspan="7" class="tal" style="text-align:left;color:blue">2、施工ガイドラインを守ること。</td>
		</tr>
		<tr>
				<td colspan="7" class="tal" style="text-align:left;color:blue">3、先行時圧縮ジャッキは、支柱にセットしておく事！！</td>
		</tr>
		<tr>
				<td colspan="7" class="tal" style="text-align:left;color:blue">4、施工時にキズつけたり、破損させた場合早急に担当者に電話して下さい！！</td>
		</tr>
		<tr>
				<td colspan="7" class="tal" style="text-align:left;color:blue">5、現場内禁煙です。喫煙は車内で！！</td>
		</tr>
		<tr>
				<td  class="title biko">備考</td>
				<td  colspan="7" class="tal" style="text-align:left">' . show_textarea("colmun_153") . '</td>
		</tr>
</table>
';

if($mode != "prn") {
	echo $html;
	echo '<div style="padding:1.5%">';
	echo '<p style="color:red">※ 項目を変更すると自動的に保存されます。保存ボタンを押す必要はありません。</p>';
	echo '<p style="color:red">※ タブキーを押すと次の項目にカーソルが移動します。</p>';
	echo '<p style="color:red">※ チェックボックスは項目名をクリックしてもチェックのオンオフが可能です。</p>';
	echo "<input type='button' class='print_sheet button' value='作業指示書印刷'>";
	echo '　　<input type="button" id="all-clear" class="" value="全ての項目をクリアする" />';
	echo '</div>';
}
?>
