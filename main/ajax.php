<?php

require_once("db_connect.php");

$week = array("日","月","火","水","木","金","土");
//1セルの幅
$width = 101;
$height = 66;
$height = 85;
$height = 150;

if(isset($_REQUEST['parm'])){
	$parm = $_REQUEST['parm'];
	$flag = $parm[0];

} else {
	echo false;
	exit();
}

if($flag == "SHOW_CAL") {
	
	$st_date = $parm[1];
	$kikan = $parm[2];
	$mobile = $parm[3];
	$user = $parm[4];
	$hilight = $parm[5];
	
//echo $mobile.":".$user;

	//コントロール領域
	echo "<div id='ctr_area'>";
?>
		<img src="../img/logo.jpg" style="height:2em;vertical-align:middle" alt="granz logo">
		表示開始日 <input name="date" id="current_date" type="date" data-role="datebox" data-options="{'mode':'calbox'}" class="tac" value="<?php echo $st_date; ?>" size="12" />
<?php		
		echo "<input type='button' id='update-cal' class='buttonf' value='表示更新' />";
		echo "<input type='button' class='buttonf' value='前' onclick='calcDate(-1)'/>";
		echo "<input type='button' class='buttonf' value='次' onclick='calcDate(1)' />";
		echo "<input type='hidden' id='current_kikan' class='button tac' value='{$kikan}' size='2' />";
		echo "<input type='button' class='buttonf' value='本日' onclick='setToday()' />";
		echo "<input type='button' class='buttonf' value='ピッタリ' onclick='setKikan(10)' />";
		echo "<input type='button' class='buttonf' value='1週間' onclick='setKikan(7)' />";
		echo "<input type='button' class='buttonf' value='2週間' onclick='setKikan(14)' />";
		echo "<input type='button' class='buttonf' value='3週間' onclick='setKikan(21)' />";
		echo "<input type='button' class='buttonf' value='4週間' onclick='setKikan(28)' />";
		if(is_admin($user))
			echo "<a href='../../system2/main/' target='_blank' class='sjump'>受発注システム</a>";
	echo "</div>";

	echo "<div id='search_area'>";
		echo "検索条件：現場ID <input type='text' id='search_gid' value='' size='3' />";
		echo " 発注ID <input type='text' id='search_sid' value='' size='3' />";
		echo " キーワード <input type='text' id='search_wd' value='' size='10' />";
	
		echo " 区分 <select id='search_kubun_id'>";
		echo "<option value='0'>選択して下さい</option>";
		$sql_s = "SELECT * FROM matsushima_kouji_syu_hat";
		$query_s = mysql_query($sql_s);
		while ($row_s = mysql_fetch_object($query_s)) {
			echo "<option value='{$row_s->sy_id}'>{$row_s->sy_name_nik}</option>";
		}
		echo "</select>";		

		echo " 元請 <select id='search_moto_id'>";
		echo "<option value='0'>選択して下さい</option>";
		$sql_s = "SELECT * FROM matsushima_moto ORDER BY kana";
		$query_s = mysql_query($sql_s);
		while ($row_s = mysql_fetch_array($query_s)) {
			if($row_s[9])
				$branch = "({$row_s[9]})";
			else
				$branch = "";

			if($row_s[10])
				$kana = "[{$row_s[10]}]";
			else
				$kana = "";
			
			echo "<option value='{$row_s[0]}'>{$kana}{$row_s[2]} {$branch}</option>";
		}
		echo "</select>&nbsp;&nbsp;";


		echo " 職方 <select id='search_seko_id'>";
		echo "<option value='0'>選択して下さい</option>";
		$sql_s = "SELECT * FROM matsushima_seko WHERE is_schshow = 1 AND is_seko_show = 1 ORDER BY orderc";
		$query_s = mysql_query($sql_s);
		while ($row_s = mysql_fetch_object($query_s)) {
			echo "<option value='{$row_s->seko_id}'>{$row_s->seko_nik}</option>";
		}
		echo "</select>";		

		echo " 担当 <select id='search_tantou_id'>";
		echo "<option value='0'>選択して下さい</option>";
		$sql_s = "SELECT * FROM matsushima_tantou WHERE is_show_tantou = 1 ORDER BY t_order, t_id";
		$query_s = mysql_query($sql_s);
		while ($row_s = mysql_fetch_object($query_s)) {
			echo "<option value='{$row_s->t_id}'>{$row_s->t_tantou}</option>";
		}
		echo "</select>";		
		
		echo "<input type='button' id='' class='buttonf' value='検索' onclick='search_sche()' />";
		echo "<input type='checkbox' id='dd_chk_flag'> D&Dしない";
	echo "</div>";
	
	echo "<table class='cal'>";
	
	//タイトル
	echo "<tr>";
	echo "<th></th>";
	for($i = 0;$i < $kikan;$i++) {
		//日付加算
		$st_dt = strtotime("+{$i} day", strtotime($st_date));
		$cur_date = date('m月d日',$st_dt);
		$date_ar[$i] = date('Y-m-d',$st_dt);
		//曜日取得
		$wk_dt = getdate($st_dt);
		if($i==0)
			echo "<th><a href=javascript:setCurrent('{$date_ar[$i]}')>{$cur_date}({$week[$wk_dt['wday']]})</a><input type='button' class='buttonf' value='前' onclick='calcDate(-1)'/></th>";
		else if($i == $kikan-1)
			echo "<th><a href=javascript:setCurrent('{$date_ar[$i]}')>{$cur_date}({$week[$wk_dt['wday']]})</a><input type='button' class='buttonf' value='次' onclick='calcDate(1)'/></th>";
		else
			echo "<th><a href=javascript:setCurrent('{$date_ar[$i]}')>{$cur_date}({$week[$wk_dt['wday']]})</a></th>";
	}
	echo "</tr>";
	
	
	//重複回避用
	$dbl = "0";
	$dbljv = "0";
	
	//日付見設定
	

	//職方数
	$sql = "SELECT * FROM `matsushima_seko` WHERE is_schshow = 1 ORDER BY orderc, seko_id";
	$query = mysql_query($sql);
	
	//user control
	if(is_admin_ei($user)) {
		$seko[] = "一般スケジュール";
		$seko_id[] = 1000;
		
		//現調の担当設定	
		$sql_g = "SELECT distinct(s_tantou_id), t_tantou_nik FROM matsushima_slip_sche LEFT OUTER JOIN matsushima_tantou ON matsushima_tantou.t_id = matsushima_slip_sche.s_tantou_id WHERE s_tantou_id is not null AND s_tantou_id != 0 AND t_gencho = 1 ORDER BY t_order";
		$query_g = mysql_query($sql_g);
		$num_g = mysql_num_rows($query_g);
		while ($row_g = mysql_fetch_object($query_g)) {
			$seko[] = $row_g->t_tantou_nik;
			$seko_id[] = 1000 + $row_g->s_tantou_id;
		}
		
		$seko[] = "未割り当て";
		$seko_id[] = 0;
	}

	while ($row = mysql_fetch_object($query)) {
		
		//外注表示
		if(preg_match('/田野畑/',$row->seko_nik))
			$seko[] = $row->seko_nik."(外注)";
		else
			$seko[] = $row->seko_nik;
		
		$seko_id[] = $row->seko_id;
	}
	$seko[] = "JV";
	$seko_id[] = 100;

	//行数ループ
	for($j = 0;$j < count($seko_id);$j++) {
		//ずらすカウンタ
		$zure = 0;
		echo "<tr>";
		//期間ループ
		echo "<th>{$seko[$j]}</th>";
		
		for($i = 0;$i < $kikan;$i++) {
			
			//boxのずれを計算
			$zure_height = $zure * $height ;
			$zure_height = $zure_height . "px";
			
			echo "<td id='ymd{$date_ar[$i]}-{$seko_id[$j]}' class='drop-area' style='padding-top:{$zure_height}'>";
			
			if($seko_id[$j] == 100) { //JVの場合
				//複数日の現場の場合
				$sql = "SELECT *,
						CASE
							WHEN (to_days(s_end_date) >= to_days('{$st_date}') + {$kikan})
								THEN to_days(s_end_date) - to_days(s_st_date) - (to_days(s_end_date) - (to_days('{$st_date}') + {$kikan}))
							WHEN (to_days(s_end_date) - to_days('{$st_date}')) > (to_days(s_end_date) - to_days(s_st_date))
								THEN (to_days(s_end_date) - to_days(s_st_date)) + 1
							ELSE
								(to_days(s_end_date) - to_days('{$st_date}')) + 1
						END as diffd,
						(to_days(s_end_date) - to_days(s_st_date)) + 1 as realdiff
						FROM `matsushima_slip_jv` 
						LEFT OUTER JOIN matsushima_genba ON matsushima_genba.g_id = matsushima_slip_jv.s_genba_id
						LEFT OUTER JOIN matsushima_kouji_syu_hat ON matsushima_kouji_syu_hat.sy_id = matsushima_slip_jv.s_seko_kubun_id
						LEFT OUTER JOIN matsushima_moto ON matsushima_moto.moto_id = matsushima_genba.g_moto_id
						LEFT OUTER JOIN matsushima_nai_1 ON matsushima_nai_1.nai1_id = matsushima_genba.g_nai1_id
						LEFT OUTER JOIN matsushima_nai_2 ON matsushima_nai_2.nai2_id = matsushima_genba.g_nai2_id
						LEFT OUTER JOIN matsushima_nai_3 ON matsushima_nai_3.nai3_id = matsushima_genba.g_nai3_id
						LEFT OUTER JOIN matsushima_tantou ON matsushima_tantou.t_id = matsushima_genba.g_tantou_id
						WHERE 
						s_st_date <= '".$date_ar[$i]."' 
						AND
						s_end_date >= '".$date_ar[$i]."' 
						AND (to_days(s_end_date) - to_days('{$st_date}')) >= 0
						AND (to_days(s_end_date) - to_days(s_st_date)) > 0
						AND s_st_date is not null AND s_st_date != '0000-00-00'
						AND s_end_date is not null AND s_end_date != '0000-00-00'
						AND
						s_id not in (".preg_replace('/,$/','',$dbljv).")
						";
				$query = mysql_query($sql);
				$num = mysql_num_rows($query);
				while ($row = mysql_fetch_object($query)) {
					//重複配列に追加
					$dbljv .= $row->s_id . ",";
					
					if(is_admin($user)) {
						$atag = "<a href='../../system2/main/?{$row->g_id}' target='_blank'>";
						$atagend = "</a>";
					}
					else {
						$atag = "<a href='javascript:show_genba_info(\"{$row->g_id}\",\"s_id_{$row->s_id}\")'>";
						$atagend = "</a>";
					}
					
					//JVメンバーの取得
					$sql_m = "SELECT * FROM matsushima_slip_jv
								INNER JOIN matsushima_jv_rel ON matsushima_jv_rel.jv_slip_id = matsushima_slip_jv.s_id
								INNER JOIN matsushima_seko ON matsushima_seko.seko_id = matsushima_jv_rel.jv_seko_id
								WHERE s_id = '{$row->s_id}'
								";
					$query_m = mysql_query($sql_m);
					$jv_member = "";
					while ($row_m = mysql_fetch_object($query_m)) {
						$jv_member .= mb_substr($row_m->seko_nik,0,1,"UTF-8") . "、";
					}
					$jv_member = preg_replace('/、$/','',$jv_member);
					if($jv_member != "")
						$jv_member = "(".$jv_member.")";
					
					echo "<div class='box' id='s_id_{$row->s_id}'>".get_icon5($row->s_yotei_id).get_icon1($row->sy_id, $row->sy_name_nik).get_icon2($row->nai1_nik).get_icon3($row->nai2_nik).get_icon4($row->nai3_nik).get_m2($row->g_m2).get_fw($row->g_freeword)."<br />{$atag}{$row->moto_nik} " . mb_strimwidth($row->g_genba, 0, 10, '', 'UTF-8') . "[{$row->g_id}] ".get_addr_nik($row->g_genba_address).who_is($row->g_id, $row->sy_id)." {$jv_member}{$atagend}</div>";
					
					$w = $width * $row->diffd;
					
					echo "<script>$('#s_id_{$row->s_id}').css('width',{$w}).data('kikan','{$row->realdiff}').data('s_id','{$row->s_id}').data('s_is_jv','1');</script>";
					
					if($row->s_id == $hilight)
						echo "<script>$('#s_id_{$row->s_id}').addClass('hilight-box');</script>";
					else if($row->g_moto_id == 31) //東リース
						echo "<script>$('#s_id_{$row->s_id}').addClass('azuma-box');</script>";
					else
						echo "<script>$('#s_id_{$row->s_id}').addClass('tantou-col-{$row->g_tantou_id}');</script>";
	
					//ずれカウンタ
					$zure++;
				}
	
				//単数日の現場の場合
				$sql = "SELECT *,
						(to_days(s_end_date) - to_days(s_st_date)) + 1 as diffd
						FROM `matsushima_slip_jv` 
						LEFT OUTER JOIN matsushima_genba ON matsushima_genba.g_id = matsushima_slip_jv.s_genba_id
						LEFT OUTER JOIN matsushima_kouji_syu_hat ON matsushima_kouji_syu_hat.sy_id = matsushima_slip_jv.s_seko_kubun_id
						LEFT OUTER JOIN matsushima_moto ON matsushima_moto.moto_id = matsushima_genba.g_moto_id
						LEFT OUTER JOIN matsushima_nai_1 ON matsushima_nai_1.nai1_id = matsushima_genba.g_nai1_id
						LEFT OUTER JOIN matsushima_nai_2 ON matsushima_nai_2.nai2_id = matsushima_genba.g_nai2_id
						LEFT OUTER JOIN matsushima_nai_3 ON matsushima_nai_3.nai3_id = matsushima_genba.g_nai3_id
						LEFT OUTER JOIN matsushima_tantou ON matsushima_tantou.t_id = matsushima_genba.g_tantou_id
						WHERE s_st_date = '".$date_ar[$i]."' 
						AND (
						(to_days(s_end_date) - to_days(s_st_date)) <= 0
						OR 
						s_st_date is null 
						OR 
						s_st_date = '0000-00-00'
						OR 
						s_end_date is null 
						OR 
						s_end_date = '0000-00-00'
						)
						";
				$query = mysql_query($sql);
				$num = mysql_num_rows($query);
				while ($row = mysql_fetch_object($query)) {
	
					if(is_admin($user)) {
						$atag = "<a href='../../system2/main/?{$row->g_id}' target='_blank'>";
						$atagend = "</a>";
					}
					else {
						$atag = "<a href='javascript:show_genba_info(\"{$row->g_id}\",\"s_id_{$row->s_id}\")'>";
						$atagend = "</a>";
					}

					//JVメンバーの取得
					$sql_m = "SELECT * FROM matsushima_slip_jv
								INNER JOIN matsushima_jv_rel ON matsushima_jv_rel.jv_slip_id = matsushima_slip_jv.s_id
								INNER JOIN matsushima_seko ON matsushima_seko.seko_id = matsushima_jv_rel.jv_seko_id
								WHERE s_id = '{$row->s_id}'
								";
					$query_m = mysql_query($sql_m);
					$jv_member = "";
					while ($row_m = mysql_fetch_object($query_m)) {
						$jv_member .= mb_substr($row_m->seko_nik,0,1,"UTF-8") . "、";
					}
					$jv_member = preg_replace('/、$/','',$jv_member);
					if($jv_member != "")
						$jv_member = "(".$jv_member.")";
	
					echo "<div class='box' id='s_id_{$row->s_id}'>".get_icon5($row->s_yotei_id).get_icon1($row->sy_id, $row->sy_name_nik).get_icon2($row->nai1_nik).get_icon3($row->nai2_nik).get_icon4($row->nai3_nik).get_m2($row->g_m2).get_fw($row->g_freeword)."<br />{$atag}{$row->moto_nik} " . mb_strimwidth($row->g_genba, 0, 10, '', 'UTF-8') . "[{$row->g_id}] ".get_addr_nik($row->g_genba_address).who_is($row->g_id, $row->sy_id)." $jv_member{$atagend}</div>";

					echo "<script>$('#s_id_{$row->s_id}').data('kikan','{$row->diffd}').data('s_id','{$row->s_id}').data('s_is_jv','1');</script>";
					
					if($row->s_id == $hilight)
						echo "<script>$('#s_id_{$row->s_id}').addClass('hilight-box');</script>";
					else if($row->g_moto_id == 31) //東リース
						echo "<script>$('#s_id_{$row->s_id}').addClass('azuma-box');</script>";
					else
						echo "<script>$('#s_id_{$row->s_id}').addClass('tantou-col-{$row->g_tantou_id}');</script>";
				}
				
			}
			else { //JV以外
				
				//複数日の現場の場合
				$sql = "SELECT *,
						CASE
							WHEN (to_days(s_end_date) >= to_days('{$st_date}') + {$kikan})
								THEN to_days(s_end_date) - to_days(s_st_date) - (to_days(s_end_date) - (to_days('{$st_date}') + {$kikan}))
							WHEN (to_days(s_end_date) - to_days('{$st_date}')) > (to_days(s_end_date) - to_days(s_st_date))
								THEN (to_days(s_end_date) - to_days(s_st_date)) + 1
							ELSE
								(to_days(s_end_date) - to_days('{$st_date}')) + 1
						END as diffd,
						(to_days(s_end_date) - to_days(s_st_date)) + 1 as realdiff
						FROM `matsushima_slip_hat` 
						LEFT OUTER JOIN matsushima_genba ON matsushima_genba.g_id = matsushima_slip_hat.s_genba_id
						LEFT OUTER JOIN matsushima_kouji_syu_hat ON matsushima_kouji_syu_hat.sy_id = matsushima_slip_hat.s_seko_kubun_id
						LEFT OUTER JOIN matsushima_moto ON matsushima_moto.moto_id = matsushima_genba.g_moto_id
						LEFT OUTER JOIN matsushima_nai_1 ON matsushima_nai_1.nai1_id = matsushima_genba.g_nai1_id
						LEFT OUTER JOIN matsushima_nai_2 ON matsushima_nai_2.nai2_id = matsushima_genba.g_nai2_id
						LEFT OUTER JOIN matsushima_nai_3 ON matsushima_nai_3.nai3_id = matsushima_genba.g_nai3_id
						LEFT OUTER JOIN matsushima_tantou ON matsushima_tantou.t_id = matsushima_genba.g_tantou_id
						WHERE 
						s_st_date <= '".$date_ar[$i]."' 
						AND
						s_end_date >= '".$date_ar[$i]."' 
						AND s_seko_id = '$seko_id[$j]'
						AND (to_days(s_end_date) - to_days('{$st_date}')) >= 0
						AND (to_days(s_end_date) - to_days(s_st_date)) > 0
						AND s_st_date is not null AND s_st_date != '0000-00-00'
						AND s_end_date is not null AND s_end_date != '0000-00-00'
						AND
						s_id not in (".preg_replace('/,$/','',$dbl).")
						";
				$query = mysql_query($sql);
				$num = mysql_num_rows($query);
				while ($row = mysql_fetch_object($query)) {

					//重複配列に追加
					$dbl .= $row->s_id . ",";
					
					if(is_admin($user)) {
						$atag = "<a href='../../system2/main/?{$row->g_id}' target='_blank'>";
						$atagend = "</a>";
					}
					else {
						$atag = "<a href='javascript:show_genba_info(\"{$row->g_id}\",\"s_id_{$row->s_id}\")'>";
						$atagend = "</a>";
					}
					
					//締めた現場はDD出来ないフラグ
					if($row->s_hat_id)
						$no_dd = "data-no-dd='1'";
					else
						$no_dd = "";
					
					
					if($row->s_is_jv)
						//echo "<div class='box' id='s_id_{$row->s_id}'>".get_icon1($row->sy_id, $row->sy_name_nik)."JV<br />{$row->g_genba}[{$row->g_id}]<br />"." {$atagend}</div>";
						echo "<div class='box' id='s_id_{$row->s_id}'>".get_icon5($row->s_yotei_id).get_gencho_status($row->s_genba_id,$date_ar[$i], $row->s_seko_kubun_id).get_icon1($row->sy_id, $row->sy_name_nik).get_icon2($row->nai1_nik).get_icon3($row->nai2_nik).get_icon4($row->nai3_nik).get_m2($row->g_m2).get_fw($row->g_freeword)."[JV]<br />{$atag}{$row->moto_nik} " . mb_strimwidth($row->g_genba, 0, 10, '', 'UTF-8') . "[{$row->g_id}] ".get_addr_nik($row->g_genba_address).who_is($row->g_id, $row->sy_id)." {$atagend}</div>";
					else
						echo "<div class='box' id='s_id_{$row->s_id}' {$no_dd}>".get_icon5($row->s_yotei_id).get_gencho_status($row->s_genba_id,$date_ar[$i], $row->s_seko_kubun_id).get_icon1($row->sy_id, $row->sy_name_nik).get_icon2($row->nai1_nik).get_icon3($row->nai2_nik).get_icon4($row->nai3_nik).get_m2($row->g_m2).get_fw($row->g_freeword)."<br />{$atag}{$row->moto_nik} " . mb_strimwidth($row->g_genba, 0, 10, '', 'UTF-8') . "[{$row->g_id}] ".get_addr_nik($row->g_genba_address).who_is($row->g_id, $row->sy_id)." {$atagend}</div>";
					
					$w = $width * $row->diffd;

					echo "<script>$('#s_id_{$row->s_id}').css('width',{$w}).data('kikan','{$row->realdiff}').data('s_id','{$row->s_id}').data('s_is_jv','1');</script>";
					
					if($row->s_id == $hilight)
						echo "<script>$('#s_id_{$row->s_id}').addClass('hilight-box');</script>";
					else if($row->g_moto_id == 31) //東リース
						echo "<script>$('#s_id_{$row->s_id}').addClass('azuma-box');</script>";
					else
						echo "<script>$('#s_id_{$row->s_id}').addClass('tantou-col-{$row->g_tantou_id}');</script>";
	
					//ずれカウンタ
					$zure++;
				}
	
				//単数日の現場の場合
				$sql = "SELECT *,
						(to_days(s_end_date) - to_days(s_st_date)) + 1 as diffd
						FROM `matsushima_slip_hat` 
						LEFT OUTER JOIN matsushima_genba ON matsushima_genba.g_id = matsushima_slip_hat.s_genba_id
						LEFT OUTER JOIN matsushima_kouji_syu_hat ON matsushima_kouji_syu_hat.sy_id = matsushima_slip_hat.s_seko_kubun_id
						LEFT OUTER JOIN matsushima_moto ON matsushima_moto.moto_id = matsushima_genba.g_moto_id
						LEFT OUTER JOIN matsushima_nai_1 ON matsushima_nai_1.nai1_id = matsushima_genba.g_nai1_id
						LEFT OUTER JOIN matsushima_nai_2 ON matsushima_nai_2.nai2_id = matsushima_genba.g_nai2_id
						LEFT OUTER JOIN matsushima_nai_3 ON matsushima_nai_3.nai3_id = matsushima_genba.g_nai3_id
						LEFT OUTER JOIN matsushima_tantou ON matsushima_tantou.t_id = matsushima_genba.g_tantou_id
						WHERE s_st_date = '".$date_ar[$i]."' AND s_seko_id = '$seko_id[$j]'
						AND (
						(to_days(s_end_date) - to_days(s_st_date)) <= 0
						OR 
						s_st_date is null 
						OR 
						s_st_date = '0000-00-00'
						OR 
						s_end_date is null 
						OR 
						s_end_date = '0000-00-00'
						)
						";
				$query = mysql_query($sql);
				$num = mysql_num_rows($query);
				while ($row = mysql_fetch_object($query)) {
	
					if(is_admin($user)) {
						$atag = "<a href='../../system2/main/?{$row->g_id}' target='_blank'>";
						$atagend = "</a>";
					}
					else {
						$atag = "<a href='javascript:show_genba_info(\"{$row->g_id}\",\"s_id_{$row->s_id}\")'>";
						$atagend = "</a>";
					}

					//締めた現場はDD出来ないフラグ
					if($row->s_hat_id)
						$no_dd = "data-no-dd='1'";
					else
						$no_dd = "";
	
					if($row->s_is_jv)
						//echo "<div class='box' id='s_id_{$row->s_id}'>".get_icon1($row->sy_id, $row->sy_name_nik)."JV<br />{$row->g_genba}[{$row->g_id}]<br />"." {$atagend}</div>";
						echo "<div class='box' id='s_id_{$row->s_id}'>".get_icon5($row->s_yotei_id).get_gencho_status($row->s_genba_id,$date_ar[$i], $row->s_seko_kubun_id).get_icon1($row->sy_id, $row->sy_name_nik).get_icon2($row->nai1_nik).get_icon3($row->nai2_nik).get_icon4($row->nai3_nik).get_m2($row->g_m2).get_fw($row->g_freeword)."[JV]<br />{$atag}{$row->moto_nik} " . mb_strimwidth($row->g_genba, 0, 10, '', 'UTF-8') . "[{$row->g_id}] ".get_addr_nik($row->g_genba_address).who_is($row->g_id, $row->sy_id)." {$atagend}</div>";
					else
						echo "<div class='box' id='s_id_{$row->s_id}' {$no_dd}>".get_icon5($row->s_yotei_id).get_gencho_status($row->s_genba_id,$date_ar[$i], $row->s_seko_kubun_id).get_icon1($row->sy_id, $row->sy_name_nik).get_icon2($row->nai1_nik).get_icon3($row->nai2_nik).get_icon4($row->nai3_nik).get_m2($row->g_m2).get_fw($row->g_freeword)."<br />{$atag}{$row->moto_nik} " . mb_strimwidth($row->g_genba, 0, 10, '', 'UTF-8') . "[{$row->g_id}] ".get_addr_nik($row->g_genba_address).who_is($row->g_id, $row->sy_id)." {$atagend}</div>";

					echo "<script>$('#s_id_{$row->s_id}').data('kikan','{$row->diffd}').data('s_id','{$row->s_id}').data('s_is_jv','1');</script>";
					
					if($row->s_id == $hilight)
						echo "<script>$('#s_id_{$row->s_id}').addClass('hilight-box');</script>";
					else if($row->g_moto_id == 31) //東リース
						echo "<script>$('#s_id_{$row->s_id}').addClass('azuma-box');</script>";
					else
						echo "<script>$('#s_id_{$row->s_id}').addClass('tantou-col-{$row->g_tantou_id}');</script>";
				}

				//全員予定
				if($seko_id[$j] != 0) {
					$sql = "SELECT *
							FROM `matsushima_ippan` 
							WHERE 
							ip_date = '".$date_ar[$i]."'
							AND
							ip_date is not null 
							AND 
							ip_date != '0000-00-00'
							";
					$query = mysql_query($sql);
					$num = mysql_num_rows($query);
					while ($row = mysql_fetch_object($query)) {
						
						//各職方があるか
						$sql_sc = "SELECT * FROM matsushima_sche_rel WHERE sc_seko_id = '{$seko_id[$j]}' AND sc_slip_id = '{$row->ip_id}'";
						$query_sc = mysql_query($sql_sc);
						$num_sc = mysql_num_rows($query_sc);
						if($num_sc) {
							if(is_admin_ei($user))
								echo "<div class='boxip3' id='ip_id_{$row->ip_id}'><a href='javascript:edit_ippan(\"{$row->ip_id}\")'>{$row->ip_desc}</a></div>";
							else	
								echo "<div class='boxip3' id='ip_id_{$row->ip_id}'>{$row->ip_desc}</div>";
								
							echo "<script>$('#ip_id_{$row->ip_id}').data('ip_id','{$row->ip_id}');</script>";
						}
					}
				}
			}
			//一般スケジュールの場合
			if($seko_id[$j] == 1000) {
				$sql = "SELECT *,
				
						(SELECT COUNT(*) as cnt FROM matsushima_sche_tantou_rel WHERE matsushima_sche_tantou_rel.sc_slip_id = matsushima_ippan.ip_id) as cnt,
						(SELECT COUNT(*) as cnt2 FROM matsushima_sche_rel WHERE matsushima_sche_rel.sc_slip_id = matsushima_ippan.ip_id) as cnt2
				
						FROM `matsushima_ippan` 
						WHERE 
						ip_date = '".$date_ar[$i]."' AND ip_seko_id = '$seko_id[$j]'
						AND
						ip_date is not null 
						AND 
						ip_date != '0000-00-00'
						";
				$query = mysql_query($sql);
				$num = mysql_num_rows($query);
				while ($row = mysql_fetch_object($query)) {

					//単独予定のみ表示
					if(!$row->cnt && !$row->cnt2) {
		
						if(is_admin_ei($user))
							echo "<div class='boxip3' id='ip_id_{$row->ip_id}'><a href='javascript:edit_ippan(\"{$row->ip_id}\")'>{$row->ip_desc}</a></div>";
						else	
							echo "<div class='boxip3' id='ip_id_{$row->ip_id}'>{$row->ip_desc}</div>";
					}
				}

				//現調表示
				//show_gencho($date_ar[$i],$seko_id[$j],$user);

			}

			//現調エリア
			if($seko_id[$j] > 1000 && $seko_id[$j] < 1100) {

				//担当一般スケジュール
				show_tantou_sche($date_ar[$i],$seko_id[$j]);
				//現調表示
				show_gencho($date_ar[$i],$seko_id[$j],$user);

			}
			
			
			//if($seko_id[$j] < 100) {
			if(1) {
				//職方休日の場合
				$sql = "SELECT *
						FROM `matsushima_holiday`
						LEFT OUTER JOIN matsushima_holiday_type ON matsushima_holiday_type.ht_id =  matsushima_holiday.hl_hour
						WHERE
						hl_date = '".$date_ar[$i]."' AND hl_seko_id = '$seko_id[$j]'
						AND
						hl_date is not null 
						AND 
						hl_date != '0000-00-00'
						";
				$query = mysql_query($sql);
				$num = mysql_num_rows($query);
				while ($row = mysql_fetch_object($query)) {
	
					if($row->hl_desc)
						$desc = "<br />" . $row->hl_desc;
					else
						$desc = "";
	
					echo "<div class='boxh' id='hl_id_{$row->hl_id}'><a href='javascript:edit_holiday($row->hl_id, \"{$row->hl_date}\", \"{$row->hl_seko_id}\")'>{$row->ht_name}{$desc}</a></div>";
					echo "<script>$('#hl_id_{$row->hl_id}').data('hl_id','{$row->hl_id}');</script>";
				}
			}
			
			//追加ボタン
			$tmp = preg_replace('/スケジュール/','',$seko[$j]);
			
			echo "<span class='add-btn'><img src='add.fw.png' style='cursol:pointer' onclick='addSche(\"{$date_ar[$i]}\", \"{$seko_id[$j]}\")'> ".date('d日',strtotime($date_ar[$i]))."(".$tmp.") </span>";
			
			echo "</td>";
			//日付data埋め込み
			echo "<script>$('#ymd{$date_ar[$i]}-{$seko_id[$j]}').data('date','{$date_ar[$i]}').data('seko_id','{$seko_id[$j]}');</script>";
			
			//日曜日に背景反転
			$wk_dt = getdate(strtotime($date_ar[$i]));
			if($wk_dt['wday'] == 0)
				echo "<script>$('#ymd{$date_ar[$i]}-{$seko_id[$j]}').css('background','#ffb8b8');</script>";


		}
		echo "</tr>";

		//中村さんの次が今月　実験
		//担当の最後のIDを取得
		$sql_tan = "SELECT MAX(t_id) as t_id FROM matsushima_tantou";
		$query_tan = mysql_query($sql_tan);
		$row_tan = mysql_fetch_object($query_tan);
		$last_tantou = 1000 + $row_tan->t_id;
		
		//if( $seko_id[$j] == 1013) {
		if( $seko_id[$j] == $last_tantou) {
			
			echo "<tr>";

	//現時点時期取得
	
	$current_year = date("Y", strtotime($st_date));
	$current_month = date("n", strtotime($st_date));
	$current_day = date("j", strtotime($st_date));
	
	$st_s_f_sch_id = $current_month * 3 - 2;
	$end_s_f_sch_id = $current_month * 3;
	
	$f_jyoken_1 = "AND s_f_year = '{$current_year}' AND s_f_sch_id >= '{$st_s_f_sch_id}' AND s_f_sch_id <= '{$end_s_f_sch_id}' ";
	
	if(1) {
		echo "<tr id='mi-area'>";
		echo "<th>今月<br/><span style='color:blue'>({$current_month}月)</span></th>";
		echo "<td colspan='{$kikan}' id='this-month' style='padding-left:10px;padding-top:10px;padding-right:10px;'>";
		
				$sql = "SELECT *
						FROM `matsushima_slip_hat` 
						LEFT OUTER JOIN matsushima_genba ON matsushima_genba.g_id = matsushima_slip_hat.s_genba_id
						LEFT OUTER JOIN matsushima_kouji_syu_hat ON matsushima_kouji_syu_hat.sy_id = matsushima_slip_hat.s_seko_kubun_id
						LEFT OUTER JOIN matsushima_moto ON matsushima_moto.moto_id = matsushima_genba.g_moto_id
						LEFT OUTER JOIN matsushima_nai_1 ON matsushima_nai_1.nai1_id = matsushima_genba.g_nai1_id
						LEFT OUTER JOIN matsushima_nai_2 ON matsushima_nai_2.nai2_id = matsushima_genba.g_nai2_id
						LEFT OUTER JOIN matsushima_nai_3 ON matsushima_nai_3.nai3_id = matsushima_genba.g_nai3_id
						LEFT OUTER JOIN matsushima_tantou ON matsushima_tantou.t_id = matsushima_genba.g_tantou_id
						WHERE 1
						{$f_jyoken_1} 
						AND
						s_is_jv = 0
						
						ORDER BY s_f_sch_id, s_id
						";
				$query = mysql_query($sql);
				$num = mysql_num_rows($query);
				while ($row = mysql_fetch_object($query)) {
					
					
					if(is_admin($user)) {
						$atag = "<a href='../../system2/main/?{$row->g_id}' target='_blank'>";
						$atagend = "</a>";
					}
					else {
						$atag = "<a href='javascript:show_genba_info(\"{$row->g_id}\",\"s_id_{$row->s_id}\")'>";
						$atagend = "</a>";
					}
					
					echo "<div class='box' id='s_id_{$row->s_id}' style='float:left'>".get_icon5($row->s_yotei_id).get_gencho_status($row->s_genba_id,$date_ar[$i], $row->s_seko_kubun_id).get_icon1($row->sy_id, $row->sy_name_nik).get_icon2($row->nai1_nik).get_icon3($row->nai2_nik).get_icon4($row->nai3_nik).get_m2($row->g_m2).get_fw($row->g_freeword)."<br />{$atag}{$row->moto_nik} " . mb_strimwidth($row->g_genba, 0, 10, '', 'UTF-8') . "[{$row->g_id}] ".get_addr_nik($row->g_genba_address).who_is($row->g_id, $row->sy_id)." {$atagend}</div>";
					$w = $width * $row->diffd;

					echo "<script>$('#s_id_{$row->s_id}').data('kikan','{$row->diffd}').data('s_id','{$row->s_id}');</script>";
	
					if($row->s_id == $hilight)
						echo "<script>$('#s_id_{$row->s_id}').addClass('hilight-box');</script>";
					else if($row->g_moto_id == 31) //東リース
						echo "<script>$('#s_id_{$row->s_id}').addClass('azuma-box');</script>";
					else
						echo "<script>$('#s_id_{$row->s_id}').addClass('tantou-col-{$row->g_tantou_id}');</script>";
	
				}
		echo "</td>";
		echo "</tr>";
		//日付data埋め込み
		echo "<script>$('#this-month').data('date','0000-00-00').data('seko_id','0').data('s_f_year','{$current_year}').data('s_f_sch_id','{$end_s_f_sch_id}');</script>";
	}




			echo "</tr>";


		}
	}

	//タイトル
	echo "<tr>";
	echo "<th></th>";
	for($i = 0;$i < $kikan;$i++) {
		//日付加算
		$st_dt = strtotime("+{$i} day", strtotime($st_date));
		$cur_date = date('m月d日',$st_dt);
		$date_ar[$i] = date('Y-m-d',$st_dt);
		//曜日取得
		$wk_dt = getdate($st_dt);
		if($i==0)
			echo "<th><a href=javascript:setCurrent('{$date_ar[$i]}')>{$cur_date}({$week[$wk_dt['wday']]})</a><input type='button' class='buttonf' value='前' onclick='calcDate(-1)'/></th>";
		else if($i == $kikan-1)
			echo "<th><a href=javascript:setCurrent('{$date_ar[$i]}')>{$cur_date}({$week[$wk_dt['wday']]})</a><input type='button' class='buttonf' value='次' onclick='calcDate(1)'/></th>";
		else
			echo "<th><a href=javascript:setCurrent('{$date_ar[$i]}')>{$cur_date}({$week[$wk_dt['wday']]})</a></th>";
	}
	echo "</tr>";

	//あいまい予定エリア
	
	//現時点時期取得
	
	$current_year = date("Y", strtotime($st_date));
	$current_month = date("n", strtotime($st_date));
	$current_day = date("j", strtotime($st_date));
	
	$st_s_f_sch_id = $current_month * 3 - 2;
	$end_s_f_sch_id = $current_month * 3;
	
	$f_jyoken_1 = "AND s_f_year = '{$current_year}' AND s_f_sch_id >= '{$st_s_f_sch_id}' AND s_f_sch_id <= '{$end_s_f_sch_id}' ";

/*	
	//今月
	if(1) {
		echo "<tr id='mi-area'>";
		echo "<th>今月<br/><span style='color:blue'>({$current_month}月)</span></th>";
		echo "<td colspan='{$kikan}' id='this-month' style='padding-left:10px;padding-top:10px;padding-right:10px;'>";
		
				$sql = "SELECT *
						FROM `matsushima_slip_hat` 
						LEFT OUTER JOIN matsushima_genba ON matsushima_genba.g_id = matsushima_slip_hat.s_genba_id
						LEFT OUTER JOIN matsushima_kouji_syu_hat ON matsushima_kouji_syu_hat.sy_id = matsushima_slip_hat.s_seko_kubun_id
						LEFT OUTER JOIN matsushima_moto ON matsushima_moto.moto_id = matsushima_genba.g_moto_id
						LEFT OUTER JOIN matsushima_nai_1 ON matsushima_nai_1.nai1_id = matsushima_genba.g_nai1_id
						LEFT OUTER JOIN matsushima_nai_2 ON matsushima_nai_2.nai2_id = matsushima_genba.g_nai2_id
						LEFT OUTER JOIN matsushima_nai_3 ON matsushima_nai_3.nai3_id = matsushima_genba.g_nai3_id
						LEFT OUTER JOIN matsushima_tantou ON matsushima_tantou.t_id = matsushima_genba.g_tantou_id
						WHERE 1
						{$f_jyoken_1} 
						AND
						s_is_jv = 0
						
						ORDER BY s_f_sch_id, s_id
						";
				$query = mysql_query($sql);
				$num = mysql_num_rows($query);
				while ($row = mysql_fetch_object($query)) {
					
					
					if(is_admin($user)) {
						$atag = "<a href='../../system2/main/?{$row->g_id}' target='_blank'>";
						$atagend = "</a>";
					}
					else {
						$atag = "<a href='javascript:show_genba_info(\"{$row->g_id}\",\"s_id_{$row->s_id}\")'>";
						$atagend = "</a>";
					}
					
					echo "<div class='box' id='s_id_{$row->s_id}' style='float:left'>".get_icon5($row->s_yotei_id).get_gencho_status($row->s_genba_id,$date_ar[$i], $row->s_seko_kubun_id).get_icon1($row->sy_id, $row->sy_name_nik).get_icon2($row->nai1_nik).get_icon3($row->nai2_nik).get_icon4($row->nai3_nik).get_m2($row->g_m2)."<br />{$atag}{$row->moto_nik} " . mb_strimwidth($row->g_genba, 0, 10, '', 'UTF-8') . "[{$row->g_id}] ".get_addr_nik($row->g_genba_address).who_is($row->g_id, $row->sy_id)." {$atagend}</div>";
					$w = $width * $row->diffd;

					echo "<script>$('#s_id_{$row->s_id}').data('kikan','{$row->diffd}').data('s_id','{$row->s_id}');</script>";
	
					if($row->s_id == $hilight)
						echo "<script>$('#s_id_{$row->s_id}').addClass('hilight-box');</script>";
					else if($row->g_moto_id == 31) //東リース
						echo "<script>$('#s_id_{$row->s_id}').addClass('azuma-box');</script>";
					else
						echo "<script>$('#s_id_{$row->s_id}').addClass('tantou-col-{$row->g_tantou_id}');</script>";
	
				}
		echo "</td>";
		echo "</tr>";
		//日付data埋め込み
		echo "<script>$('#this-month').data('date','0000-00-00').data('seko_id','0').data('s_f_year','{$current_year}').data('s_f_sch_id','{$end_s_f_sch_id}');</script>";
	}
*/
	//次月上旬

	if( $current_month == 12 ) {
		$next_year = $current_year + 1;
		$next_month = 1;
	}
	else {
		$next_year = $current_year;
		$next_month = $current_month + 1;
	}

	$st_s_f_sch_id = $next_month * 3 - 2;
	
	$f_jyoken_1 = "AND s_f_year = '{$next_year}' AND s_f_sch_id = '{$st_s_f_sch_id}' ";

	if(1) {
		echo "<tr id='mi-area'>";
		echo "<th>次月 上旬<br /><span style='color:blue'>({$next_month}月)</span></th>";
		echo "<td colspan='{$kikan}' id='first-month' style='padding-left:10px;padding-top:10px;padding-right:10px;'>";
		
				$sql = "SELECT *
						FROM `matsushima_slip_hat` 
						LEFT OUTER JOIN matsushima_genba ON matsushima_genba.g_id = matsushima_slip_hat.s_genba_id
						LEFT OUTER JOIN matsushima_kouji_syu_hat ON matsushima_kouji_syu_hat.sy_id = matsushima_slip_hat.s_seko_kubun_id
						LEFT OUTER JOIN matsushima_moto ON matsushima_moto.moto_id = matsushima_genba.g_moto_id
						LEFT OUTER JOIN matsushima_nai_1 ON matsushima_nai_1.nai1_id = matsushima_genba.g_nai1_id
						LEFT OUTER JOIN matsushima_nai_2 ON matsushima_nai_2.nai2_id = matsushima_genba.g_nai2_id
						LEFT OUTER JOIN matsushima_nai_3 ON matsushima_nai_3.nai3_id = matsushima_genba.g_nai3_id
						LEFT OUTER JOIN matsushima_tantou ON matsushima_tantou.t_id = matsushima_genba.g_tantou_id
						WHERE 1
						{$f_jyoken_1} 
						AND
						s_is_jv = 0
						
						ORDER BY s_f_sch_id, s_id
						";
				$query = mysql_query($sql);
				$num = mysql_num_rows($query);
				while ($row = mysql_fetch_object($query)) {
					
					
					if(is_admin($user)) {
						$atag = "<a href='../../system2/main/?{$row->g_id}' target='_blank'>";
						$atagend = "</a>";
					}
					else {
						$atag = "<a href='javascript:show_genba_info(\"{$row->g_id}\",\"s_id_{$row->s_id}\")'>";
						$atagend = "</a>";
					}
					
					echo "<div class='box' id='s_id_{$row->s_id}' style='float:left'>".get_icon5($row->s_yotei_id).get_gencho_status($row->s_genba_id,$date_ar[$i], $row->s_seko_kubun_id).get_icon1($row->sy_id, $row->sy_name_nik).get_icon2($row->nai1_nik).get_icon3($row->nai2_nik).get_icon4($row->nai3_nik).get_m2($row->g_m2).get_fw($row->g_freeword)."<br />{$atag}{$row->moto_nik} " . mb_strimwidth($row->g_genba, 0, 10, '', 'UTF-8') . "[{$row->g_id}] ".get_addr_nik($row->g_genba_address).who_is($row->g_id, $row->sy_id)." {$atagend}</div>";
					$w = $width * $row->diffd;

					echo "<script>$('#s_id_{$row->s_id}').data('kikan','{$row->diffd}').data('s_id','{$row->s_id}');</script>";
	
					if($row->s_id == $hilight)
						echo "<script>$('#s_id_{$row->s_id}').addClass('hilight-box');</script>";
					else if($row->g_moto_id == 31) //東リース
						echo "<script>$('#s_id_{$row->s_id}').addClass('azuma-box');</script>";
					else
						echo "<script>$('#s_id_{$row->s_id}').addClass('tantou-col-{$row->g_tantou_id}');</script>";
	
				}
		echo "</td>";
		echo "</tr>";
		//日付data埋め込み
		echo "<script>$('#first-month').data('date','0000-00-00').data('seko_id','0').data('s_f_year','{$next_year}').data('s_f_sch_id','{$st_s_f_sch_id}');</script>";
	}

	//次月中旬
	$st_s_f_sch_id = $next_month * 3 - 1;
	
	$f_jyoken_1 = "AND s_f_year = '{$next_year}' AND s_f_sch_id = '{$st_s_f_sch_id}' ";

	if(1) {
		echo "<tr id='mi-area'>";
		echo "<th>次月 中旬<br /><span style='color:blue'>({$next_month}月)</span></th>";
		echo "<td colspan='{$kikan}' id='middle-month' style='padding-left:10px;padding-top:10px;padding-right:10px;'>";
		
				$sql = "SELECT *
						FROM `matsushima_slip_hat` 
						LEFT OUTER JOIN matsushima_genba ON matsushima_genba.g_id = matsushima_slip_hat.s_genba_id
						LEFT OUTER JOIN matsushima_kouji_syu_hat ON matsushima_kouji_syu_hat.sy_id = matsushima_slip_hat.s_seko_kubun_id
						LEFT OUTER JOIN matsushima_moto ON matsushima_moto.moto_id = matsushima_genba.g_moto_id
						LEFT OUTER JOIN matsushima_nai_1 ON matsushima_nai_1.nai1_id = matsushima_genba.g_nai1_id
						LEFT OUTER JOIN matsushima_nai_2 ON matsushima_nai_2.nai2_id = matsushima_genba.g_nai2_id
						LEFT OUTER JOIN matsushima_nai_3 ON matsushima_nai_3.nai3_id = matsushima_genba.g_nai3_id
						LEFT OUTER JOIN matsushima_tantou ON matsushima_tantou.t_id = matsushima_genba.g_tantou_id
						WHERE 1
						{$f_jyoken_1} 
						AND
						s_is_jv = 0
						
						ORDER BY s_f_sch_id, s_id
						";
				$query = mysql_query($sql);
				$num = mysql_num_rows($query);
				while ($row = mysql_fetch_object($query)) {
					
					
					if(is_admin($user)) {
						$atag = "<a href='../../system2/main/?{$row->g_id}' target='_blank'>";
						$atagend = "</a>";
					}
					else {
						$atag = "<a href='javascript:show_genba_info(\"{$row->g_id}\",\"s_id_{$row->s_id}\")'>";
						$atagend = "</a>";
					}
					
					echo "<div class='box' id='s_id_{$row->s_id}' style='float:left'>".get_icon5($row->s_yotei_id).get_gencho_status($row->s_genba_id,$date_ar[$i], $row->s_seko_kubun_id).get_icon1($row->sy_id, $row->sy_name_nik).get_icon2($row->nai1_nik).get_icon3($row->nai2_nik).get_icon4($row->nai3_nik).get_m2($row->g_m2).get_fw($row->g_freeword)."<br />{$atag}{$row->moto_nik} " . mb_strimwidth($row->g_genba, 0, 10, '', 'UTF-8') . "[{$row->g_id}] ".get_addr_nik($row->g_genba_address).who_is($row->g_id, $row->sy_id)." {$atagend}</div>";
					$w = $width * $row->diffd;

					echo "<script>$('#s_id_{$row->s_id}').data('kikan','{$row->diffd}').data('s_id','{$row->s_id}');</script>";
	
					if($row->s_id == $hilight)
						echo "<script>$('#s_id_{$row->s_id}').addClass('hilight-box');</script>";
					else if($row->g_moto_id == 31) //東リース
						echo "<script>$('#s_id_{$row->s_id}').addClass('azuma-box');</script>";
					else
						echo "<script>$('#s_id_{$row->s_id}').addClass('tantou-col-{$row->g_tantou_id}');</script>";
	
				}
		echo "</td>";
		echo "</tr>";
		//日付data埋め込み
		echo "<script>$('#middle-month').data('date','0000-00-00').data('seko_id','0').data('s_f_year','{$next_year}').data('s_f_sch_id','{$st_s_f_sch_id}');</script>";
	}

	//次月下旬
	$st_s_f_sch_id = $next_month * 3;
	
	$f_jyoken_1 = "AND s_f_year = '{$next_year}' AND s_f_sch_id = '{$st_s_f_sch_id}' ";

	if(1) {
		echo "<tr id='mi-area'>";
		echo "<th>次月 下旬<br /><span style='color:blue'>({$next_month}月)</span></th>";
		echo "<td colspan='{$kikan}' id='last-month' style='padding-left:10px;padding-top:10px;padding-right:10px;'>";
		
				$sql = "SELECT *
						FROM `matsushima_slip_hat` 
						LEFT OUTER JOIN matsushima_genba ON matsushima_genba.g_id = matsushima_slip_hat.s_genba_id
						LEFT OUTER JOIN matsushima_kouji_syu_hat ON matsushima_kouji_syu_hat.sy_id = matsushima_slip_hat.s_seko_kubun_id
						LEFT OUTER JOIN matsushima_moto ON matsushima_moto.moto_id = matsushima_genba.g_moto_id
						LEFT OUTER JOIN matsushima_nai_1 ON matsushima_nai_1.nai1_id = matsushima_genba.g_nai1_id
						LEFT OUTER JOIN matsushima_nai_2 ON matsushima_nai_2.nai2_id = matsushima_genba.g_nai2_id
						LEFT OUTER JOIN matsushima_nai_3 ON matsushima_nai_3.nai3_id = matsushima_genba.g_nai3_id
						LEFT OUTER JOIN matsushima_tantou ON matsushima_tantou.t_id = matsushima_genba.g_tantou_id
						WHERE 1
						{$f_jyoken_1} 
						AND
						s_is_jv = 0
						
						ORDER BY s_f_sch_id, s_id
						";
				$query = mysql_query($sql);
				$num = mysql_num_rows($query);
				while ($row = mysql_fetch_object($query)) {
					
					
					if(is_admin($user)) {
						$atag = "<a href='../../system2/main/?{$row->g_id}' target='_blank'>";
						$atagend = "</a>";
					}
					else {
						$atag = "<a href='javascript:show_genba_info(\"{$row->g_id}\",\"s_id_{$row->s_id}\")'>";
						$atagend = "</a>";
					}
					
					echo "<div class='box' id='s_id_{$row->s_id}' style='float:left'>".get_icon5($row->s_yotei_id).get_gencho_status($row->s_genba_id,$date_ar[$i], $row->s_seko_kubun_id).get_icon1($row->sy_id, $row->sy_name_nik).get_icon2($row->nai1_nik).get_icon3($row->nai2_nik).get_icon4($row->nai3_nik).get_m2($row->g_m2).get_fw($row->g_freeword)."<br />{$atag}{$row->moto_nik} " . mb_strimwidth($row->g_genba, 0, 10, '', 'UTF-8') . "[{$row->g_id}] ".get_addr_nik($row->g_genba_address).who_is($row->g_id, $row->sy_id)." {$atagend}</div>";
					$w = $width * $row->diffd;

					echo "<script>$('#s_id_{$row->s_id}').data('kikan','{$row->diffd}').data('s_id','{$row->s_id}');</script>";
	
					if($row->s_id == $hilight)
						echo "<script>$('#s_id_{$row->s_id}').addClass('hilight-box');</script>";
					else if($row->g_moto_id == 31) //東リース
						echo "<script>$('#s_id_{$row->s_id}').addClass('azuma-box');</script>";
					else
						echo "<script>$('#s_id_{$row->s_id}').addClass('tantou-col-{$row->g_tantou_id}');</script>";
	
				}
		echo "</td>";
		echo "</tr>";
		//日付data埋め込み
		echo "<script>$('#last-month').data('date','0000-00-00').data('seko_id','0').data('s_f_year','{$next_year}').data('s_f_sch_id','{$st_s_f_sch_id}');</script>";
	}

	$st_s_f_sch_id = $current_month * 3 - 2;
	$end_s_f_sch_id = $current_month * 3;
	
	$f_jyoken_1 = "!(s_f_year = '{$current_year}' AND s_f_sch_id >= '{$st_s_f_sch_id}' AND s_f_sch_id <= '{$end_s_f_sch_id}') ";

	$st_s_f_sch_id = $next_month * 3 - 2;
	$end_s_f_sch_id = $next_month * 3;
	
	$f_jyoken_2 = "!(s_f_year = '{$current_year}' AND s_f_sch_id >= '{$st_s_f_sch_id}' AND s_f_sch_id <= '{$end_s_f_sch_id}') ";

	//user control
	//if(is_admin_ei($user)) {
	if(1) {
		echo "<tr id='mi-area'>";
		echo "<th>スケジュール<br />未設定</th>";
		echo "<td colspan='{$kikan}' id='nosche' style='padding-left:10px;padding-top:10px;padding-right:10px;'>";
		
				$sql = "SELECT *,
						
						(
						SELECT 
							CASE
								WHEN hat.s_st_date is null OR hat.s_st_date = '0000-00-00'
									THEN null
								ELSE
									EXTRACT(YEAR_MONTH from hat.s_st_date)
							END
						FROM 
							matsushima_slip_hat as hat 
						WHERE
							matsushima_slip_hat.s_genba_id = hat.s_genba_id
							AND
							hat.s_seko_kubun_id <= 2
						ORDER BY hat.s_st_date
						LIMIT 1
						) as kake_order
				
						FROM `matsushima_slip_hat` 
						LEFT OUTER JOIN matsushima_genba ON matsushima_genba.g_id = matsushima_slip_hat.s_genba_id
						LEFT OUTER JOIN matsushima_kouji_syu_hat ON matsushima_kouji_syu_hat.sy_id = matsushima_slip_hat.s_seko_kubun_id
						LEFT OUTER JOIN matsushima_moto ON matsushima_moto.moto_id = matsushima_genba.g_moto_id
						LEFT OUTER JOIN matsushima_nai_1 ON matsushima_nai_1.nai1_id = matsushima_genba.g_nai1_id
						LEFT OUTER JOIN matsushima_nai_2 ON matsushima_nai_2.nai2_id = matsushima_genba.g_nai2_id
						LEFT OUTER JOIN matsushima_nai_3 ON matsushima_nai_3.nai3_id = matsushima_genba.g_nai3_id
						LEFT OUTER JOIN matsushima_tantou ON matsushima_tantou.t_id = matsushima_genba.g_tantou_id
						WHERE 
						(
						s_st_date is null 
						OR 
						s_st_date = '0000-00-00'
						)
						AND
						s_is_jv = 0
						AND
						(
							(
							s_f_year is null
							OR
							s_f_year = 0
							)
							OR
							(
							{$f_jyoken_1}
							AND
							{$f_jyoken_2}
							)
						)
						
						ORDER BY kake_order,s_seko_kubun_id
						
						";


				$m_flag = 0;

				$query = mysql_query($sql);
				$num = mysql_num_rows($query);
				while ($row = mysql_fetch_object($query)) {
					
					
					if(is_admin($user)) {
						$atag = "<a href='../../system2/main/?{$row->g_id}' target='_blank'>";
						$atagend = "</a>";
					}
					else {
						$atag = "<a href='javascript:show_genba_info(\"{$row->g_id}\",\"s_id_{$row->s_id}\")'>";
						$atagend = "</a>";
					}

					if( $m_flag !== $row->kake_order ) {
						if( $m_flag === 0 ) {
							echo "<div style='min-width:100px;max-width:200px;margin-right:10px;float:left'>";
							echo "<p class='tac'>掛開始日未設定</p>";
						}
						else {
							echo "</div><div style='min-width:100px;max-width:200px;margin-right:10px;float:left'>";	
							echo "<p class='tac'>".date('Y年m月', strtotime($row->kake_order."01")) . "</p>";

						}
					
						
						$m_flag = $row->kake_order;
					}
					
					echo "<div class='box' id='s_id_{$row->s_id}' style='float:left'>".get_icon5($row->s_yotei_id).get_gencho_status($row->s_genba_id,$date_ar[$i], $row->s_seko_kubun_id).get_icon1($row->sy_id, $row->sy_name_nik).get_icon2($row->nai1_nik).get_icon3($row->nai2_nik).get_icon4($row->nai3_nik).get_m2($row->g_m2).get_fw($row->g_freeword)."<br />{$atag}{$row->moto_nik} " . mb_strimwidth($row->g_genba, 0, 10, '', 'UTF-8') . "[{$row->g_id}] ".get_addr_nik($row->g_genba_address).who_is($row->g_id, $row->sy_id)." {$atagend}";
					
					echo "</div>";
					$w = $width * $row->diffd;



					echo "<script>$('#s_id_{$row->s_id}').data('kikan','{$row->diffd}').data('s_id','{$row->s_id}');</script>";
	
					if($row->s_id == $hilight)
						echo "<script>$('#s_id_{$row->s_id}').addClass('hilight-box');</script>";
					else if($row->g_moto_id == 31) //東リース
						echo "<script>$('#s_id_{$row->s_id}').addClass('azuma-box');</script>";
					else
						echo "<script>$('#s_id_{$row->s_id}').addClass('tantou-col-{$row->g_tantou_id}');</script>";
	
				}
		echo "</div>";
		echo "</td>";
		echo "</tr>";
		//日付data埋め込み
		echo "<script>$('#nosche').data('date','0000-00-00').data('seko_id','0');</script>";
	}
	
	echo "</table>";
}

else if($flag == "ADD_SCH") {
	
	$date = $parm[1];
	$seko_id = $parm[2];

	echo "<table class='add-sche-table'>";
	//echo "<tr><td><input type='button' class='buttonf' value='新規現場登録' onclick='add_genba_kani(0, \"{$date}\", \"{$seko_id}\")'/></td></tr>";
	//echo "<tr><td><input type='button' class='buttonf' value='既存現場から発注スケジュール追加' onclick='add_genba_kizon(0,\"{$date}\", \"{$seko_id}\")'/></td></tr>";
	echo "<tr><td><input type='button' class='buttonf' value='休日・予定を設定する' onclick='edit_holiday(0, \"{$date}\", \"{$seko_id}\")'/></td></tr>";
	echo "<tr><td><input type='button' class='buttonf' value='スケジュール一覧に戻る' onclick='$(\"#main-cal-area\").html(loading_text).show();show_cal()'/></td></tr>";
	echo "</table>";

}

else if($flag == "UPDATE_SCH") {
	
	$st_date = $parm[1];
	$s_id = $parm[2];
	$kikan = $parm[3];
	$seko_id = $parm[4];
	$s_is_jv = $parm[5];
	$s_f_year = $parm[6];
	$s_f_sch_id = $parm[7];
	
	//一般スケジュールとJVは除外
	//if(($seko_id == 100 && $s_is_jv != 1) || $seko_id == 1000 || ($st_date == '0000-00-00' && ($s_f_year == '' || !$s_f_year))) {
	if(($seko_id == 100 && $s_is_jv != 1) || $seko_id == 1000 ) {
		exit();
	}
	//JVの移動
	else if($seko_id == 100 && $s_is_jv == 1) {
		$sql = "SELECT * FROM `matsushima_slip_jv` WHERE s_id = '{$s_id}'";
		$query = mysql_query($sql);
		$num = mysql_num_rows($query);
		if($num) {
			$row = mysql_fetch_object($query);
			
			//スケジュール未設定の場合
			if($st_date == '0000-00-00' || $st_date == null || $st_date == '') {
				$sql = "UPDATE `matsushima_slip_jv` SET s_st_date = '0000-00-00',s_end_date = '0000-00-00', s_seko_id = '0' WHERE s_id = '{$s_id}'";
				$query = mysql_query($sql);
			}
			//終了日未記載の場合
			else if($row->s_end_date == '0000-00-00' || $row->s_end_date == null) {
				$sql = "UPDATE `matsushima_slip_jv` SET s_st_date = '{$st_date}', s_seko_id = '{$seko_id}' WHERE s_id = '{$s_id}'";
				$query = mysql_query($sql);
				$sql = "UPDATE `matsushima_slip_hat` SET s_st_date = '{$st_date}', s_f_year = null, s_f_sch_id = null WHERE s_jv_rel_id = '{$s_id}'";
				$query = mysql_query($sql);
			}
			//両方記載
			else {
				$dt = strtotime($st_date);
				if($kikan < 0) {
					$end_date = $st_date;
				}
				else {
					$dt = strtotime("+{$kikan} day", $dt);
					$end_date = date('Y-m-d',$dt);
				}
				
				$sql = "UPDATE `matsushima_slip_jv` SET s_st_date = '{$st_date}', s_end_date = '{$end_date}', s_seko_id = '{$seko_id}' WHERE s_id = '{$s_id}'";
				$query = mysql_query($sql);
				$sql = "UPDATE `matsushima_slip_hat` SET s_st_date = '{$st_date}', s_end_date = '{$end_date}', s_f_year = null, s_f_sch_id = null WHERE s_jv_rel_id = '{$s_id}'";
				$query = mysql_query($sql);
			}
		}
	}
	//JV以外の移動
	else {
		$sql = "SELECT * FROM `matsushima_slip_hat` WHERE s_id = '{$s_id}'";
		$query = mysql_query($sql);
		$num = mysql_num_rows($query);
		if($num) {
			$row = mysql_fetch_object($query);
			
			//曖昧スケジュールの場合
			if($s_f_year != '' && $s_f_year && $s_f_sch_id) {
				$sql = "UPDATE `matsushima_slip_hat` SET s_st_date = '0000-00-00',s_end_date = '0000-00-00', s_seko_id = '0', s_f_year = '{$s_f_year}', s_f_sch_id = '{$s_f_sch_id}' WHERE s_id = '{$s_id}'";
			}
			//スケジュール未設定の場合
			else if($st_date == '0000-00-00' || $st_date == null || $st_date == '') {
				$sql = "UPDATE `matsushima_slip_hat` SET s_st_date = '0000-00-00',s_end_date = '0000-00-00', s_seko_id = '0', s_f_year = null, s_f_sch_id = null WHERE s_id = '{$s_id}'";
			}
			//終了日未記載の場合
			else if($row->s_end_date == '0000-00-00' || $row->s_end_date == null) {
				$sql = "UPDATE `matsushima_slip_hat` SET s_st_date = '{$st_date}', s_seko_id = '{$seko_id}', s_f_year = null, s_f_sch_id = null WHERE s_id = '{$s_id}'";
			}
			//両方記載
			else {
				$dt = strtotime($st_date);
				if($kikan < 0) {
					$end_date = $st_date;
				}
				else {
					$dt = strtotime("+{$kikan} day", $dt);
					$end_date = date('Y-m-d',$dt);
				}
				
				$sql = "UPDATE `matsushima_slip_hat` SET s_st_date = '{$st_date}', s_end_date = '{$end_date}', s_seko_id = '{$seko_id}', s_f_year = null, s_f_sch_id = null WHERE s_id = '{$s_id}'";
			}
			$query = mysql_query($sql);
			
/*
			//請求締め日も書き換え
			$sql = "SELECT * FROM matsushima_slip_hat 
							INNER JOIN matsushima_genba ON matsushima_slip_hat.s_genba_id = matsushima_genba.g_id 
							INNER JOIN  matsushima_moto ON matsushima_genba.g_moto_id = matsushima_moto.moto_id
							WHERE s_id = '{$s_id}'
					";
			$query = mysql_query($sql);
			$num = mysql_num_rows($query);
			$row = mysql_fetch_object($query);
			
			if($num) {
				//架時
				if($row->m_pat == 1 && ($row->s_seko_kubun_id == 2 || $row->s_seko_kubun_id == 10)) {
					$sql_c = "SELECT * FROM matsushima_slip WHERE s_genba_id = '{$row->s_genba_id}' AND (s_seko_kubun_id = 1) LIMIT 1";
					$query_c = mysql_query($sql_c);
					$num_c = mysql_num_rows($query_c);
					$row_c = mysql_fetch_object($query_c);
					if($num_c) {
						$sql_exe = "UPDATE matsushima_slip SET s_shime_date	= '{$row->s_st_date}', s_st_date = '{$row->s_st_date}' ,s_end_date = '{$row->s_end_date}' WHERE s_id = '{$row_c->s_id}'";	
						$query_exe = mysql_query($sql_exe);
					}
				}
				//払時
				else if($row->m_pat == 2 && ($row->s_seko_kubun_id == 3 || $row->s_seko_kubun_id == 10)) {
					$sql_c = "SELECT * FROM matsushima_slip WHERE s_genba_id = '{$row->s_genba_id}' AND (s_seko_kubun_id = 1) LIMIT 1";
					$query_c = mysql_query($sql_c);
					$num_c = mysql_num_rows($query_c);
					$row_c = mysql_fetch_object($query_c);
					if($num_c) {
						$sql_exe = "UPDATE matsushima_slip SET  s_shime_date	= '{$row->s_st_date}', s_st_date = '{$row->s_st_date}' ,s_end_date = '{$row->s_end_date}'  WHERE s_id = '{$row_c->s_id}'";	
						$query_exe = mysql_query($sql_exe);
					}
				}
				//分割時
				else if($row->m_pat == 3 && ($row->s_seko_kubun_id == 2 || $row->s_seko_kubun_id == 3 || $row->s_seko_kubun_id == 10)) {
					if($row->s_seko_kubun_id == 2 || $row->s_seko_kubun_id == 3)
						$kid = $row->s_seko_kubun_id;
					else if($row->s_seko_kubun_id == 10)
						$kid = 1;
					
					$sql_c = "SELECT * FROM matsushima_slip WHERE s_genba_id = '{$row->s_genba_id}' AND (s_seko_kubun_id = '{$kid}') LIMIT 1";
					$query_c = mysql_query($sql_c);
					$num_c = mysql_num_rows($query_c);
					$row_c = mysql_fetch_object($query_c);
					if($num_c) {
						$sql_exe = "UPDATE matsushima_slip SET  s_shime_date	= '{$row->s_st_date}', s_st_date = '{$row->s_st_date}' ,s_end_date = '{$row->s_end_date}'  WHERE s_id = '{$row_c->s_id}'";	
						$query_exe = mysql_query($sql_exe);
					}
				}
			}
*/
		}
	}
}
//現調 DD 移動
else if($flag == "UPDATE_GENCHO_SCH") {
	
	$st_date = $parm[1];
	$s_id = $parm[2];
	$kikan = $parm[3];
	$seko_id = $parm[4];
	$s_is_jv = $parm[5];
	$s_f_year = $parm[6];
	$s_f_sch_id = $parm[7];

	if(1) {
		$sql = "SELECT * FROM `matsushima_slip_sche` WHERE s_id = '{$s_id}'";
		$query = mysql_query($sql);
		$num = mysql_num_rows($query);
		if($num) {
			$row = mysql_fetch_object($query);
			
			//1000を引く
			$seko_id -= 1000;
			
			if( $seko_id > 0 ) {
				$sql = "UPDATE `matsushima_slip_sche` SET s_st_date = '{$st_date}', s_tantou_id = '{$seko_id}'  WHERE s_id = '{$s_id}'";
			}
			else {
				$sql = "UPDATE `matsushima_slip_sche` SET s_st_date = '{$st_date}'  WHERE s_id = '{$s_id}'";
			}

			$query = mysql_query($sql);
		}
	}
}

else if($flag == "UPDATE_IPPAN") {
	
	$id = $parm[1];
	$date = $parm[2];

	echo "<table class='ippan-table'>";

	$sql = "SELECT * FROM matsushima_ippan
			WHERE ip_id = '{$id}'";
	$query = mysql_query($sql);
	$num = mysql_num_rows($query);
	if(!$num) {
		$sql = "SELECT 1";
		$query = mysql_query($sql);
	}
	while ($row = mysql_fetch_object($query)) {
		echo "<input type='hidden' id='ip_id' value='{$row->ip_id}' /><input type='hidden' id='ip_seko_id' value='1000' />";
		
		if($num)
			$ip_date = $row->ip_date;
		else
			$ip_date = $date;
		
		echo "<td>日付</td>";
		echo '<td><input name="ip_date" id="ip_date" type="date" data-role="datebox" data-options="{\'mode\':\'calbox\'}" class="tac" value="'.$ip_date.'" size="12" /></td>';
		echo "</tr>";
		echo "<tr>";
		echo "<td>内容</td>";
		echo "<td><textarea id='ip_desc' rows='4' cols='40'>{$row->ip_desc}</textarea></td>";
		echo "</tr>";
	}

	echo "</table>";
		echo "<p><input type='button' value='全てチェックする' onclick='check_ctr(1)'> <input type='button' value='全てチェックを外す' onclick='check_ctr(0)'></p>";
	
	echo "<div style='margin:30px 0'>";

	//現調メンバー
	$sql_seko = "SELECT * FROM matsushima_tantou WHERE t_gencho = 1";
	
	//debug
	/*echo "<script>write_debug(\"{$sql}\");</script>";*/
	
	$query_seko = @mysql_query($sql_seko);
	$num_seko = @mysql_num_rows($query_seko);

	while ($row_seko = mysql_fetch_object($query_seko)) {

		//JVチェックボックスコントロール
		$sql_j = "SELECT * FROM matsushima_sche_tantou_rel WHERE sc_slip_id = '{$id}' AND sc_seko_id = '{$row_seko->t_id}'";
		$query_j = mysql_query($sql_j);
		$num_j = mysql_num_rows($query_j);
		if($num_j)
			$tantou_check = "checked='checked'";
		else
			$tantou_check = "";
		
		echo "<input type='checkbox' value='{$row_seko->t_id}' class='tantou_seko' {$tantou_check}>";
		echo $row_seko->t_tantou_nik . "&nbsp;";
	}

	//職方毎予定
	$sql_seko = "SELECT * FROM matsushima_seko WHERE is_schshow = 1 AND is_seko_show = 1 ORDER BY orderc";
	
	//debug
	/*echo "<script>write_debug(\"{$sql}\");</script>";*/
	
	$query_seko = @mysql_query($sql_seko);
	$num_seko = @mysql_num_rows($query_seko);

	while ($row_seko = mysql_fetch_object($query_seko)) {

		//JVチェックボックスコントロール
		$sql_j = "SELECT * FROM matsushima_sche_rel WHERE sc_slip_id = '{$id}' AND sc_seko_id = '{$row_seko->seko_id}'";
		$query_j = mysql_query($sql_j);
		$num_j = mysql_num_rows($query_j);
		if($num_j)
			$jv_check = "checked='checked'";
		else
			$jv_check = "";
		
		echo "<input type='checkbox' value='{$row_seko->seko_id}' class='jv_seko' {$jv_check}>";
		echo $row_seko->seko . "&nbsp;";
	}
	
	echo "</div>";
	
	
	echo "<input type='button' class='buttonf' value='保存' onclick='ippan_update()'/>";
	echo "<input type='button' class='buttonf' value='スケジュール一覧に戻る' onclick='$(\"#main-cal-area\").html(loading_text).show();show_cal()'/>";
	echo "<input type='button' class='buttonf' value='削除' onclick='ippan_delete()'/>";
}

else if($flag == "UPDATE_HOLIDAY") {
	
	$id = $parm[1];
	$date = $parm[2];
	$seko_id = $parm[3];

	echo "<table class='ippan-table'>";

	$sql = "SELECT * FROM matsushima_holiday
			WHERE
			hl_id = '{$id}'
			";
	$query = mysql_query($sql);
	$num = mysql_num_rows($query);
	if(!$num) {
		$sql = "SELECT 1";
		$query = mysql_query($sql);
	}
	while ($row = mysql_fetch_object($query)) {
		echo "<input type='hidden' id='hl_id' value='{$row->hl_id}' />";
		
		if($num)
			$hl_date = $row->hl_date;
		else
			$hl_date = $date;
		
		echo "<td>日付</td>";
		echo '<td><input name="hl_date" id="hl_date" type="date" data-role="datebox" data-options="{\'mode\':\'calbox\'}" class="tac" value="'.$hl_date.'" size="12" /></td>';
		echo "</tr>";

		echo "<tr>";
		echo "<td>休暇・予定区分</td>";
		echo "<td>";
		
		echo "<select id='hl_hour'>";
		$sql_s = "SELECT * FROM matsushima_holiday_type";
		$query_s = mysql_query($sql_s);
		while ($row_s = mysql_fetch_object($query_s)) {
			if($row_s->ht_id == $row->hl_hour)
				echo "<option value='{$row_s->ht_id}' selected='selected'>{$row_s->ht_name}</option>";
			else
				echo "<option value='{$row_s->ht_id}'>{$row_s->ht_name}</option>";
		}
		echo "</select>";		
		
		echo "</td>";
		echo "</tr>";

		echo "<input type='hidden' id='hl_seko_id' value='{$seko_id}'>";
/*
		echo "<tr>";
		echo "<td>発注先</td>";
		echo "<td>";
		
		echo "<select id='hl_seko_id'>";
		echo "<option value='0'>選択して下さい</option>";
		$sql_s = "SELECT * FROM matsushima_seko";
		$query_s = mysql_query($sql_s);
		while ($row_s = mysql_fetch_object($query_s)) {
			if($row_s->seko_id == $seko_id)
				echo "<option value='{$row_s->seko_id}' selected='selected'>{$row_s->seko_nik}</option>";
			else
				echo "<option value='{$row_s->seko_id}'>{$row_s->seko_nik}</option>";
		}
		echo "</select>";		
		
		echo "</td>";
		echo "</tr>";
*/
		echo "<tr>";
		echo "<td>内容</td>";
		echo "<td><textarea id='hl_desc' rows='4' cols='40'>{$row->hl_desc}</textarea></td>";
		echo "</tr>";
	}

	echo "</table>";
	echo "<input type='button' class='buttonf' value='保存' onclick='holiday_update()'/>";
	echo "<input type='button' class='buttonf' value='スケジュール一覧に戻る' onclick='$(\"#main-cal-area\").html(loading_text).show();show_cal()'/>";
	echo "<input type='button' class='buttonf' value='削除' onclick='holiday_delete()'/>";
}

else if($flag == "NEW_GENBA") {
	
	$id = $parm[1];
	$date = $parm[2];
	$seko_id = $parm[3];

	echo "<table class='ippan-table'>";

	echo "<tr>";
	echo "<td>現場名*</td>";
	echo "<td><input type='text' id='g_genba' size='30' /></td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td>現場住所</td>";
	echo "<td><input type='text' id='g_genba_address' size='40' /></td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td>担当</td>";
	echo "<td>";
	echo "<select id='g_tantou_id'>";
	echo "<option value='0'>選択して下さい</option>";
	$sql_s = "SELECT * FROM matsushima_tantou";
	$query_s = mysql_query($sql_s);
	while ($row_s = mysql_fetch_object($query_s)) {
		echo "<option value='{$row_s->t_id}'>{$row_s->t_tantou}</option>";
	}
	echo "</select>";
	echo "</td>";		
	echo "</tr>";

	echo "<tr>";
	echo "<td>元請*</td>";
	echo "<td>";
	echo "<select id='g_moto_id'>";
	echo "<option value='0'>選択して下さい</option>";
	$sql_s = "SELECT * FROM matsushima_moto";
	$query_s = mysql_query($sql_s);
	while ($row_s = mysql_fetch_object($query_s)) {
		echo "<option value='{$row_s->moto_id}'>{$row_s->moto_nik}</option>";
	}
	echo "</select>";		
	echo "</td>";		
	echo "</tr>";

	echo "<tr>";
	echo "<td>元請担当者</td>";
	echo "<td><input type='text' id='g_moto_tantou' /></td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td>工事内容1</td>";
	echo "<td>";
	echo "<select id='g_nai1_id'>";
	echo "<option value='0'>選択して下さい</option>";
	$sql_s = "SELECT * FROM matsushima_nai_1";
	$query_s = mysql_query($sql_s);
	while ($row_s = mysql_fetch_object($query_s)) {
		echo "<option value='{$row_s->nai1_id}'>{$row_s->nai1}</option>";
	}
	echo "</select>";		
	echo "</td>";		
	echo "</tr>";


	echo "<tr>";
	echo "<td>工事内容2</td>";
	echo "<td>";
	echo "<select id='g_nai2_id'>";
	echo "<option value='0'>選択して下さい</option>";
	$sql_s = "SELECT * FROM matsushima_nai_2";
	$query_s = mysql_query($sql_s);
	while ($row_s = mysql_fetch_object($query_s)) {
		echo "<option value='{$row_s->nai2_id}'>{$row_s->nai2}</option>";
	}
	echo "</select>";		
	echo "</td>";		
	echo "</tr>";


	echo "<tr>";
	echo "<td>工事内容3</td>";
	echo "<td>";
	echo "<select id='g_nai3_id'>";
	echo "<option value='0'>選択して下さい</option>";
	$sql_s = "SELECT * FROM matsushima_nai_3";
	$query_s = mysql_query($sql_s);
	while ($row_s = mysql_fetch_object($query_s)) {
		echo "<option value='{$row_s->nai3_id}'>{$row_s->nai3}</option>";
	}
	echo "</select>";		
	echo "</td>";		
	echo "</tr>";

	echo "<tr>";
	echo "<td>㎡数</td>";
	echo "<td><input type='text' id='g_m2' size='5' /></td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td>現場備考</td>";
	echo "<td><textarea id='g_biko' rows='4' cols='40'></textarea></td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td>発注区分</td>";
	echo "<td>";
	echo "<select id='s_seko_kubun_id'>";
	echo "<option value='0'>選択して下さい</option>";
	$sql_s = "SELECT * FROM matsushima_kouji_syu_hat";
	$query_s = mysql_query($sql_s);
	while ($row_s = mysql_fetch_object($query_s)) {
		echo "<option value='{$row_s->sy_id}'>{$row_s->sy_name_nik}</option>";
	}
	echo "</select>";		
	echo "</td>";		
	echo "</tr>";

	if($seko_id != 100) {
		echo "<tr>";
		echo "<td>発注先</td>";
		echo "<td>";
		echo "<select id='s_seko_id'>";
		echo "<option value='0'>選択して下さい</option>";
		$sql_s = "SELECT * FROM matsushima_seko WHERE is_schshow = 1 ORDER BY orderc";
		$query_s = mysql_query($sql_s);
		while ($row_s = mysql_fetch_object($query_s)) {
			if($seko_id == $row_s->seko_id)
				echo "<option value='{$row_s->seko_id}' selected='selected'>{$row_s->seko_nik}</option>";
			else if($row_s->is_seko_show == 1)	
				echo "<option value='{$row_s->seko_id}'>{$row_s->seko_nik}</option>";
		}
		echo "</select>";		
		echo "</td>";		
		echo "</tr>";
	}
	else {
		echo "<input type='hidden' ID='s_seko_id' value='100'>";	
	}

	echo "<td>開始日</td>";
	echo '<td><input name="s_st_date" id="s_st_date" type="date" data-role="datebox" data-options="{\'mode\':\'calbox\'}" class="tac" value="'.$date.'" size="12" /></td>';
	echo "</tr>";

	echo "<td>終了日</td>";
	echo '<td><input name="s_end_date" id="s_end_date" type="date" data-role="datebox" data-options="{\'mode\':\'calbox\'}" class="tac" value="" size="12" /></td>';
	echo "</tr>";

	echo "<tr>";
	echo "<td>発注額</td>";
	echo "<td><input type='text' id='s_hattyu' size='10' /></td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td>発注備考</td>";
	echo "<td><textarea id='s_biko' rows='4' cols='40'></textarea></td>";
	echo "</tr>";
	
	//隠し領域
	echo "<input type='hidden' id='s_genba_id' value='temp_main_id' />";
	$today = date('Y-m-d');
	echo "<input type='hidden' id='g_input_date' value='{$today}' />";
	echo "<input type='hidden' id='s_date' value='{$today}' />";
	
	

	echo "</table>";
	if($seko_id != 100)
		echo "<input type='button' class='buttonf' value='保存' onclick='new_genba_update(0)'/>";
	else
		echo "<input type='button' class='buttonf' value='保存' onclick='new_genba_update(1)'/>";
	echo "<input type='button' class='buttonf' value='スケジュール一覧に戻る' onclick='$(\"#main-cal-area\").html(loading_text).show();show_cal()'/>";
}

else if($flag == "NEW_HAT") {
	
	$id = $parm[1];
	$date = $parm[2];
	$seko_id = $parm[3];

	echo "<table class='ippan-table'>";

	echo "<tr>";
	echo "<td>既存現場選択</td>";
	echo "<td>";
	echo "<select id='s_genba_id'>";
	echo "<option value='0'>選択して下さい</option>";
	$sql_s = "SELECT * FROM matsushima_genba ORDER BY g_id DESC LIMIT 100";
	$query_s = mysql_query($sql_s);
	while ($row_s = mysql_fetch_object($query_s)) {
		echo "<option value='{$row_s->g_id}'>[{$row_s->g_id}]{$row_s->g_genba}:{$row_s->g_genba_address}</option>";
	}
	echo "</select>";		
	echo "</td>";		
	echo "</tr>";

	echo "<tr>";
	echo "<td>発注区分</td>";
	echo "<td>";
	echo "<select id='s_seko_kubun_id'>";
	echo "<option value='0'>選択して下さい</option>";
	$sql_s = "SELECT * FROM matsushima_kouji_syu_hat";
	$query_s = mysql_query($sql_s);
	while ($row_s = mysql_fetch_object($query_s)) {
		echo "<option value='{$row_s->sy_id}'>{$row_s->sy_name_nik}</option>";
	}
	echo "</select>";		
	echo "</td>";		
	echo "</tr>";

	if($seko_id != 100) {
		echo "<tr>";
		echo "<td>発注先</td>";
		echo "<td>";
		echo "<select id='s_seko_id'>";
		echo "<option value='0'>選択して下さい</option>";
		$sql_s = "SELECT * FROM matsushima_seko WHERE is_schshow = 1 ORDER BY orderc";
		$query_s = mysql_query($sql_s);
		while ($row_s = mysql_fetch_object($query_s)) {
			if($seko_id == $row_s->seko_id)
				echo "<option value='{$row_s->seko_id}' selected='selected'>{$row_s->seko_nik}</option>";
			else if($row_s->is_seko_show == 1)	
				echo "<option value='{$row_s->seko_id}'>{$row_s->seko_nik}</option>";
		}
		echo "</select>";		
		echo "</td>";		
		echo "</tr>";
	}
	else {
		echo "<input type='hidden' ID='s_seko_id' value='100'>";	
	}
	echo "<td>開始日</td>";
	echo '<td><input name="s_st_date" id="s_st_date" type="date" data-role="datebox" data-options="{\'mode\':\'calbox\'}" class="tac" value="'.$date.'" size="12" /></td>';
	echo "</tr>";

	echo "<td>終了日</td>";
	echo '<td><input name="s_end_date" id="s_end_date" type="date" data-role="datebox" data-options="{\'mode\':\'calbox\'}" class="tac" value="" size="12" /></td>';
	echo "</tr>";

	echo "<tr>";
	echo "<td>発注額</td>";
	echo "<td><input type='text' id='s_hattyu' size='10' /></td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td>発注備考</td>";
	echo "<td><textarea id='s_biko' rows='4' cols='40'></textarea></td>";
	echo "</tr>";
	
	//隠し領域
	$today = date('Y-m-d');
	echo "<input type='hidden' id='s_date' value='{$today}' />";
	
	

	echo "</table>";
	if($seko_id != 100)
		echo "<input type='button' class='buttonf' value='保存' onclick='kizon_genba_update(0)'/>";
	else
		echo "<input type='button' class='buttonf' value='保存' onclick='kizon_genba_update(1)'/>";
	echo "<input type='button' class='buttonf' value='スケジュール一覧に戻る' onclick='$(\"#main-cal-area\").html(loading_text).show();show_cal()'/>";
}
else if($flag == "SEARCH_EXEC") {

	$search_wd = $parm[1];
	$search_moto_id = $parm[2];
	$search_tantou_id = $parm[3];
	$search_kubun_id = $parm[4];
	$search_seko_id = $parm[5];
	$search_gid = $parm[6];
	$search_sid = $parm[7];

	$JYOKEN = "";

	if($search_gid)
		$JYOKEN .= " AND s_genba_id = '{$search_gid}'";
	if($search_sid)
		$JYOKEN .= " AND s_id = '{$search_sid}'";
	if($search_moto_id)
		$JYOKEN .= " AND g_moto_id = '{$search_moto_id}'";
	if($search_tantou_id)
		$JYOKEN .= " AND g_tantou_id = '{$search_tantou_id}'";
	if($search_seko_id)
		$JYOKEN .= " AND s_seko_id = '{$search_seko_id}'";
	if($search_kubun_id)
		$JYOKEN .= " AND s_seko_kubun_id = '{$search_kubun_id}'";
	
	$sql = "SELECT * FROM matsushima_slip_hat
			LEFT OUTER JOIN matsushima_genba ON matsushima_slip_hat.s_genba_id = matsushima_genba.g_id
			LEFT OUTER JOIN matsushima_tantou ON matsushima_tantou.t_id = matsushima_genba.g_tantou_id
			LEFT OUTER JOIN matsushima_moto ON matsushima_moto.moto_id = matsushima_genba.g_moto_id
			LEFT OUTER JOIN matsushima_seko ON matsushima_seko.seko_id = matsushima_slip_hat.s_seko_id
			LEFT OUTER JOIN matsushima_kouji_syu_hat ON matsushima_kouji_syu_hat.sy_id = matsushima_slip_hat.s_seko_kubun_id
			WHERE
			(
				g_genba like '%{$search_wd}%'
				OR
				g_genba_address like '%{$search_wd}%'
				OR
				g_biko like '%{$search_wd}%'
				OR
				s_biko like '%{$search_wd}%'
			)
			{$JYOKEN}
			
			ORDER BY s_id DESC
			LIMIT 200
				
			";
	$query = mysql_query($sql);
	$num = mysql_num_rows($query);

	if($num) {
		echo "<h2 style='font-size:18px;padding-bottom:10px;'>検索結果</h2>";
	
		echo "<table class='search-result'>";
		
		echo "<tr>";
			echo "<th colspan='2'>アクション</th>";
			echo "<th>発注ID</th>";
			echo "<th>現場ID</th>";
			echo "<th>区分</th>";
			echo "<th>現場名</th>";
			echo "<th>現場住所</th>";
			echo "<th>元請</th>";
			echo "<th>発注先</th>";
			echo "<th>担当</th>";
			echo "<th>発注日</th>";
			echo "<th>開始日</th>";
			echo "<th>終了日</th>";
			echo "<th>発注締日</th>";
			echo "<th>発注額</th>";
		echo "</tr>";
	
		while ($row = mysql_fetch_object($query)) {
		
			echo "<tr>";
				echo "<td>";
				if($row->s_st_date != '0000-00-00' && $row->s_st_date != '')
					echo "<a href='javascript:show_cal_by_search(\"{$row->s_st_date}\", $row->s_id)'>[表示]</a> ";
				echo "</td>";
				echo "<td>";
				echo "<a href='../../system2/main/?{$row->g_id}' target='_blank'>[編集]</a>";
				echo "</td>";
				echo "<td>{$row->s_id}</td>";
				echo "<td>{$row->g_id}</td>";
				echo "<td>{$row->sy_name_nik}</td>";
				
				//JVの判別
				if($row->s_is_jv)
					$jvstr = "[JV]";
				else
					$jvstr = "";
				
				echo "<td>{$jvstr}{$row->g_genba}</td>";
				echo "<td>{$row->g_genba_address}</td>";
	
				echo "<td>{$row->moto_nik}</td>";
				echo "<td>{$row->seko_nik}</td>";
				echo "<td>{$row->t_tantou}</td>";
	
				$tmp = $row->s_date;
				if($tmp == "0000-00-00")
					$tmp = "";
				else
					$tmp = date('m月d日',strtotime($tmp));
				echo "<td class='tac'>".$tmp."</td>";
	
				$tmp = $row->s_st_date;
				if($tmp == "0000-00-00")
					$tmp = "";
				else
					$tmp = date('m月d日',strtotime($tmp));
				echo "<td class='tac'>".$tmp."</td>";
	
				$tmp = $row->s_end_date;
				if($tmp == "0000-00-00")
					$tmp = "";
				else
					$tmp = date('m月d日',strtotime($tmp));
				echo "<td class='tac'>".$tmp."</td>";
	
				$tmp = $row->s_shime_date;
				if($tmp == "0000-00-00")
					$tmp = "";
				else
					$tmp = date('m月d日',strtotime($tmp));
				echo "<td class='tac'>".$tmp."</td>";
				
				echo "<td class='tar'>".number_format($row->s_hattyu)."</td>";
			echo "</tr>";
		
		}
		echo "</table>";
	}
	else {
		echo "<p style='font-size:16px;color:red;'>検索に合致するスケジュールがありませんでした。</p>";	
		
	}
	echo "<br />";
	echo "<input type='button' class='buttonf' value='スケジュール一覧に戻る' onclick='$(\"#main-cal-area\").html(loading_text).show();show_cal()'/>";

}
else if($flag == "GET_GENBA_INFO") {

	$id = $parm[1];

	$sql = "SELECT * FROM `matsushima_genba` as a 
	
			LEFT OUTER JOIN matsushima_est_syu as b ON b.sy_id = a.g_status
			LEFT OUTER JOIN matsushima_tantou as c ON c.t_id = a.g_tantou_id
			LEFT OUTER JOIN matsushima_moto as d ON d.moto_id = a.g_moto_id
			LEFT OUTER JOIN matsushima_nai_1 as e ON e.nai1_id = a.g_nai1_id
			LEFT OUTER JOIN matsushima_nai_2 as f ON f.nai2_id = a.g_nai2_id
			LEFT OUTER JOIN matsushima_nai_3 as g ON g.nai3_id = a.g_nai3_id
			
			WHERE g_id = '{$id}'";
	$query = mysql_query($sql);
	$num = mysql_num_rows($query);
	echo "<table class='edit-table'>";
	while ($row = mysql_fetch_object($query)) {
//----------------------個別領域 ここから----------------------------

		$clm = "g_id";
		echo "<tr>";
		echo "<th>現場ID</th>";
		echo "<td class='nw'>";
		echo $row->$clm;
		echo "</td>";
		echo "</tr>";


		$clm = "sy_name_nik";
		echo "<tr>";
		echo "<th>ステータス</th>";
		echo "<td class='nw'>";
		echo $row->$clm;
		echo "</td>";
		echo "</tr>";

		$clm = "g_genba";
		echo "<tr>";
		echo "<th>現場名 <span style='color:red'>*</span></th>";
		echo "<td class='nw'>";
		echo $row->$clm;
		echo "</td>";
		echo "</tr>";

		$clm = "g_genba_address";
		echo "<tr>";
		echo "<th>現場住所</th>";
		echo "<td class='nw'>";
		
		$address_encode = urlencode(preg_replace('/　| /','',$row->$clm));
   		$zoom = 15;  //ズームレベル
   		$gmap_url = "http://maps.google.co.jp/maps?q=".$address_encode."&z=".$zoom;		
		echo "<a href='{$gmap_url}' class='tel' target='_blank'>{$row->$clm}</a>";
		echo "</td>";
		echo "</tr>";

		$clm = "t_tantou";
		if($row->t_tel)
			$ttel = " (<a href='tel:". mb_convert_kana($row->t_tel,'a') . "' class='tel'>{$row->t_tel}</a>)";
		else
			$ttel = "";	
		echo "<tr>";
		echo "<th>GRANZ担当</th>";
		echo "<td class='nw'>";
		echo $row->$clm.$ttel;
		echo "</td>";
		echo "</tr>";

		$clm = "moto";
		echo "<tr>";
		echo "<th>元請 <span style='color:red'>*</span></th>";
		echo "<td class='nw'>";
		echo $row->$clm;
		echo "</td>";
		echo "</tr>";

		$clm = "g_moto_tantou";
		echo "<tr>";
		echo "<th>元請担当者</th>";
		echo "<td class='nw'>";
		echo $row->$clm;
		echo "</td>";
		echo "</tr>";

		$clm = "g_moto_tantou_tel";
		if($row->$clm)
			$ttel = "<a href='tel:". mb_convert_kana($row->$clm,'a') . "' class='tel'>{$row->$clm}</a>";
		else
			$ttel = "";	
		echo "<tr>";
		echo "<th>元請担当者連絡先</th>";
		echo "<td class='nw'>";
		echo $ttel;
		echo "</td>";
		echo "</tr>";

		$clm = "nai1";
		echo "<tr>";
		echo "<th>工事内容1</th>";
		echo "<td class='nw'>";
		echo $row->$clm;
		echo "</td>";
		echo "</tr>";

		$clm = "nai2";
		echo "<tr>";
		echo "<th>工事内容2</th>";
		echo "<td class='nw'>";
		echo $row->$clm;
		echo "</td>";
		echo "</tr>";

		$clm = "nai3";
		echo "<tr>";
		echo "<th>工事内容3</th>";
		echo "<td class='nw'>";
		echo $row->$clm;
		echo "</td>";
		echo "</tr>";

		$clm = "g_m2";
		echo "<tr>";
		echo "<th>㎡数</th>";
		echo "<td class='nw'>";
		echo $row->$clm;
		echo " ㎡</td>";
		echo "</tr>";
		
		$clm = "g_freeword";
		echo "<tr>";
		echo "<th>フリーワード</th>";
		echo "<td class='nw'>";
		echo $row->$clm;
		echo "</td>";
		echo "</tr>";
		
		$clm = "g_biko";
		echo "<tr>";
		echo "<th>備考</th>";
		echo "<td class='nw'>";
		echo $row->$clm;
		echo "</td>";
		echo "</tr>";

		echo "<tr>";
		echo "<th>作業指示書</th>";
		echo "<td class='nw'>";
		echo "<a href='#' onClick='print_gencho(".$row->g_id.");return false'>こちらをタップ</a> ";
		echo "</td>";
		echo "</tr>";

	}
	echo "</table>";
}

/**********************************************************************
 *
 * 	
 *
 **********************************************************************/
function get_sch() {

	$sql = "SELECT * FROM `matsushima_slip_hat` WHERE 1";
	$query = mysql_query($sql);
	$num = mysql_num_rows($query);
	$last_id = mysql_insert_id();
	while ($row = mysql_fetch_object($query)) {
		echo $row->s_st_date;
		echo $row->s_end_date;
	}

}

/**********************************************************************
 *
 * 	
 *
 **********************************************************************/
function get_icon1($sy_id, $sy_name_nik) {
	
	switch($sy_id) {
		case 2:
			$icon = "<span class='icon_blue'>{$sy_name_nik}</span>";
			break;	
		case 3:
			$icon = "<span class='icon_red'>{$sy_name_nik}</span>";
			break;
		case 4:
		case 5:
			$icon = "<span class='icon_gray'>{$sy_name_nik}</span>";
			break;
		case 1:
			$icon = "<span class='icon_normal'>{$sy_name_nik}</span>";
			break;
		case 8:
			$icon = "<span class='icon_normal'>追</span>";
			break;
		case 10:
			$icon = "<span class='icon_normal'>他</span>";
			break;
		default:
			$icon = "<span class='icon_normal'>{$sy_name_nik}</span>";
	}
	
	return $icon;
}

/**********************************************************************
 *
 * 	
 *
 **********************************************************************/
function get_icon2($p) {
	if($p)
		$icon = "<span class='icon_normal'>{$p}</span>";
	else
		$icon = "";
		
	return $icon;
}

/**********************************************************************
 *
 * 	
 *
 **********************************************************************/
function get_icon3($p) {
	switch($p) {
		case "平屋":
			$icon = "<span class='icon_normal'>平</span>";
			break;
		case "":
			$icon = "";
			break;
		default:
			$icon = "<span class='icon_normal'>{$p}</span>";
			break;
	}
		
	return $icon;
}

/**********************************************************************
 *
 * 	
 *
 **********************************************************************/
function get_icon4($p) {
	switch($p) {
		case "内部":
			$icon = "<span class='icon_normal'>内</span>";
			break;
		case "屋根":
			$icon = "<span class='icon_normal'>屋</span>";
			break;
		case "":
			$icon = "";
			break;
		default:
			$icon = "<span class='icon_normal'>{$p}</span>";
			break;
	}
		
	return $icon;
}

function get_icon5($y_id) {
	
	switch($y_id) {
		case 1:
			$icon = "<span class='icon_gencho_red'>予</span>";
			break;	
		case 2:
			$icon = "<span class='icon_gencho_red'>交</span>";
			break;
		case 3:
			$icon = "<span class='icon_gencho_red'>決</span>";
			break;
		default:
			$icon = "";
	}
	
	return $icon;
}

/**********************************************************************
 *
 * 	
 *
 **********************************************************************/
function who_is($g_id, $kubun) {

	if($kubun <= 2 || !$g_id)
		return "";

	$desc = "";
	
	$sql = "SELECT * FROM matsushima_slip_hat 
			LEFT OUTER JOIN matsushima_seko ON matsushima_seko.seko_id = matsushima_slip_hat.s_seko_id
			LEFT OUTER JOIN matsushima_kouji_syu_hat ON matsushima_kouji_syu_hat.sy_id = matsushima_slip_hat.s_seko_kubun_id
			WHERE s_genba_id = '{$g_id}'";
	$query = mysql_query($sql);
	$num = mysql_num_rows($query);
	while ($row = mysql_fetch_object($query)) {
		if($row->sy_id != $kubun && $row->seko_nik && $row->sy_id == 2)
			$desc .= $row->sy_name_nik . ":" . mb_substr($row->seko_nik,0,1, 'UTF-8').",";
	}

	if($desc) {
		$desc = preg_replace('/,$/','',$desc);
		return "[".$desc."]";		
	}
	else
		return $desc;		
}

/**********************************************************************
 *
 * 	
 *
 **********************************************************************/
function get_m2($p) {
	if($p)
		$icon = "<span class='icon_normal' style='font-size:10px'>{$p}㎡</span>";
	else
		$icon = "";
		
	return $icon;
}

function get_fw($p) {
	if($p)
		$icon = "<span class='free-icon'>{$p}</span>";
	else
		$icon = "";
		
	return $icon;
}




function get_addr_nik($str) {


preg_match('/^(京都府|.+?[都道府県])(大和郡山市|蒲郡市|小郡市|杵島郡大町町|.+?郡.+?町|.+?郡.+?村|(大阪市|名古屋市|京都市|横浜市|神戸市|北九州市|札幌市|川崎市|福岡市|広島市|仙台市|千葉市|さいたま市).+?区|(千代田|中央|港|新宿|文京|台東|墨田|江東|品川|目黒|大田|世田谷|渋谷|中野|杉並|豊島|北|荒川|板橋|練馬|足立|葛飾|江戸川)区|四日市市|廿日市市|.+?市|.+?町|.+?村)(.*)$/u',$str,$match);

$pref = !empty($match[1]) ? $match[1] : '';
$city = !empty($match[2]) ? $match[2] : '';
$town = !empty($match[5]) ? $match[5] : '';

return preg_replace('/東京都|埼玉県|神奈川県/','',$pref.$city);

/*	
	if(preg_match('/市/',$addr)) {
		
		$str = explode('市',$addr);
		return $str[0] . "市";
	}
	else if(preg_match('/区/',$addr)) {
		
		$str = explode('区',$addr);
		return $str[0] . "区";
	}
	else if(preg_match('/町/',$addr)) {
		
		$str = explode('町',$addr);
		return $str[0] . "町";
	}
	else if(preg_match('/村/',$addr)) {
		
		$str = explode('村',$addr);
		return $str[0] . "村";
	}

	else {
		return mb_strimwidth($addr, 0, 16, '', 'UTF-8');
	}
*/
}


function get_gencho_status($g,$d,$k) {
	if($k >= 3)
		return '';
	
	$sql = "SELECT *
			FROM `matsushima_slip_sche`
			LEFT OUTER JOIN matsushima_sche_syu ON matsushima_sche_syu.id =  matsushima_slip_sche.s_seko_kubun_id
			LEFT OUTER JOIN matsushima_genba ON matsushima_genba.g_id = matsushima_slip_sche.s_genba_id
			LEFT OUTER JOIN matsushima_moto ON matsushima_moto.moto_id = matsushima_genba.g_moto_id
			LEFT OUTER JOIN matsushima_slip_hat ON matsushima_genba.g_id = matsushima_slip_hat.s_genba_id
			WHERE 
			matsushima_slip_hat.s_seko_kubun_id > 0
			AND
			matsushima_slip_hat.s_seko_kubun_id <= 2
			AND
			matsushima_slip_sche.s_seko_kubun_id = 1
			AND
			matsushima_slip_sche.s_genba_id = '{$g}'
			";

	$query = mysql_query($sql);
	$num = mysql_num_rows($query);
	if($num)
		return "<span class='icon_gencho_blue'>済</span>";
	else
		return "<span class='icon_gencho_red'>未</span>";
}

//現場スケジュール
function show_gencho($d, $id,$user) {
	
	if($id == 1000) {
		$jyoken = " AND (s_tantou_id = 0 OR s_tantou_id is null) ";
		
	}
	else if($id > 1000 && $id < 1100) {
		$id = $id - 1000;
	
		$jyoken = " AND (s_tantou_id = '{$id}') ";
		
	}
	
	$sql = "SELECT *
			FROM `matsushima_slip_sche`
			LEFT OUTER JOIN matsushima_sche_syu ON matsushima_sche_syu.id =  matsushima_slip_sche.s_seko_kubun_id
			LEFT OUTER JOIN matsushima_genba ON matsushima_genba.g_id = matsushima_slip_sche.s_genba_id
			LEFT OUTER JOIN matsushima_moto ON matsushima_moto.moto_id = matsushima_genba.g_moto_id
			WHERE 
			s_st_date = '".$d."'
			AND
			s_st_date is not null 
			AND 
			s_st_date != '0000-00-00'
			{$jyoken}
			ORDER BY st_time, s_id
			";
	$query = mysql_query($sql);
	$num = mysql_num_rows($query);

	while ($row = mysql_fetch_object($query)) {

		if(is_admin($user)) {
			$atag = "<a href='../../system2/main/?{$row->g_id}' target='_blank'>";
			$atagend = "</a>";
		}
		else {
			$atag = "";
			$atagend = "";
		}
		
		$time_str = "";
		if($row->st_time != "")
			$time_str .= $row->st_time;
		if($row->end_time != "") {
			if($time_str == "")
				$time_str .= $row->end_time;
			else
				$time_str .= "〜" . $row->end_time;
		}
		if($time_str != "")
			$time_str = "<br />" . $time_str . "<br />";
		
		if($row->name_nik != "")
			$name_nik = "<b>[".$row->name_nik."]" . $row->s_title . "</b> " . $time_str . $row->moto_nik ." ". mb_strimwidth($row->g_genba, 0, 10, '', 'UTF-8') . "[".$row->g_id."] ".get_addr_nik($row->g_genba_address);
		else	
			$name_nik = "<b>" . $row->s_title . "</b> " . $time_str . $row->moto_nik ." ". mb_strimwidth($row->g_genba, 0, 10, '', 'UTF-8') . "[".$row->g_id."] ".get_addr_nik($row->g_genba_address);

		echo "<div class='boxip2' id='sp_id_{$row->s_id}'>{$atag}{$name_nik}{$atagend}</div>";
		echo "<script>$('#sp_id_{$row->s_id}').data('sp_id','{$row->s_id}');</script>";

/*
		echo "<script>$('#s_id_{$row->s_id}').css('width',{$w}).data('kikan','{$row->realdiff}').data('s_id','{$row->s_id}').data('s_is_jv','1');</script>";
*/

	}


}


function show_tantou_sche($d, $t) {

	global $user;
	
	$t = $t - 1000;
	
	$sql = "SELECT *
			FROM `matsushima_ippan` 
			WHERE 
			ip_date = '".$d."'
			AND
			ip_date is not null 
			AND 
			ip_date != '0000-00-00'
			";
	$query = mysql_query($sql);
	$num = mysql_num_rows($query);
	while ($row = mysql_fetch_object($query)) {
		
		//各職方があるか
		$sql_sc = "SELECT * FROM matsushima_sche_tantou_rel WHERE sc_seko_id = '{$t}' AND sc_slip_id = '{$row->ip_id}'";
		$query_sc = mysql_query($sql_sc);
		$num_sc = mysql_num_rows($query_sc);
		if($num_sc) {
			if(is_admin_ei($user))
				echo "<div class='boxip3' id='ip_id_{$row->ip_id}'><a href='javascript:edit_ippan(\"{$row->ip_id}\")'>{$row->ip_desc}</a></div>";
			else	
				echo "<div class='boxip3' id='ip_id_{$row->ip_id}'>{$row->ip_desc}</div>";
				
			echo "<script>$('#ip_id_{$row->ip_id}').data('ip_id','{$row->ip_id}');</script>";
		}
	}
}

function is_admin_ei($user) {
	if($user == 'ei') {
		return true;
	}
	else if(is_admin($user)) {
		return true;
	}
	else {
		return false;
	}
}
function is_admin($user) {
	if($user == 'granz' || $user == 'sp' || $user == 'maki' || $user == 'yuji' || $user == 'sasanuma') {
		return true;
	}
	else {
		return false;
	}
}

?>
