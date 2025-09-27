var current = "2012-12-12";
//var current = "2013/01/05";
var mobile_flag = false;
var ie_flag = false;
var user = "";
var kikan = 14;

var dd_chk_flag = true;

var loading_text = "<img src='loadinfo_net.gif' />";

$(function() {
	//モバイルflag
	var agent = navigator.userAgent;
	if((agent.search(/iPhone/) != -1)||(agent.search(/iPad/) != -1)||(agent.search(/Android/) != -1)){
		mobile_flag = true;
		kikan = 28;
	}
	else if((agent.search(/MSIE/) != -1)){
		ie_flag = true;
	}

	//カレントユーザー
	user = $("#user").val();
	//user = "granz";

	$(document).on("click","#dd_chk_flag",function() {
		if($(this).prop("checked")) {
			//localStorage.setItem("dd_chk_flag", 1);
			//location.reload();
			dd_chk_flag = true;
			show_cal();
		}
		else {
			//localStorage.setItem("dd_chk_flag", 0);
			//location.reload();
			dd_chk_flag = false;
			show_cal();
		}
	});

	//初めて開くときはクリア
	//localStorage.setItem("dd_chk_flag", 1);

	//URLで直接ジャンプ
	if($("#jump_sid").length) {
		var jump_sid = $("#jump_sid").val();	
		var jump_date = $("#jump_date").val();
		if(jump_date == "")
			current = get_today();
		else		
			current = jump_date;

		show_cal(jump_sid);
	}
	else {
		current = get_today();
		show_cal();
	}
});

/**********************************************************************
 *
 * 	スケジュール表示
 *
 **********************************************************************/
function show_cal(_h) {

	if(_h == undefined)
		_h = 0
	
	var flag = "SHOW_CAL";
	
	$("#main-cal-area").html("<img src='../img/icon_loader_f_ww_01_s1.gif' alt='loading'>");

	//DDコントロール
	//dd_chk_flag = $("#dd_chk_flag").prop("checked");
	/*
	var dd_chk_flag = eval(localStorage.getItem("dd_chk_flag"));
    if(localStorage.getItem("dd_chk_flag") === null) {
        dd_chk_flag = 1;
    }
	*/

	$.ajaxPrefilter(function (options, originalOptions, jqXHR) {
		if(originalOptions.type.toLowerCase() == 'post'){        
			options.data = jQuery.param($.extend(originalOptions.data||{}, {
			timeStamp: new Date().getTime()
			}));
		}
	});
	$.ajax({
		async:		true,
		cache:		false,
		url:		"ajax.php",
		data:		{parm:[flag, current, kikan, mobile_flag, user, _h]},
		type:		"post",
		headers: 	{"pragma": "no-cache"},
		success:	function(data, textStatus) {
			$("#main-cal-area").html(data);

			$("#update-cal").click(function() {
				current = $("#current_date").val();
				$("#main-cal-area").html(loading_text).show();
				show_cal();
			});

			//チェックコントロール
			if(dd_chk_flag) {
				$("#dd_chk_flag").prop("checked",true);
			}
			else {
				$("#dd_chk_flag").prop("checked",false);
			}

			/*
			var tmp_dd_chk_flag = eval(localStorage.getItem("dd_chk_flag"));
            if(localStorage.getItem("dd_chk_flag") === null) {
                tmp_dd_chk_flag = 1;
            }
            
			if(tmp_dd_chk_flag) {
				$("#dd_chk_flag").prop("checked",true);
			}
			*/		
			
			//IEの日付ダイアログ
			if(ie_flag) {
				$("#current_date").datepicker({
					inline: true,
					showButtonPanel: true,
					changeMonth: true
				});
			}
			
		},
		complete:	function(data, textStatus) {
			//DDイベントセット
			if(is_admin(user) && !mobile_flag && !dd_chk_flag) {
				set_dd();
			}
		},
		error:		function(data, textStatus) {
		}
	});
}

/**********************************************************************
 *
 * 	本日に戻る
 *
 **********************************************************************/
function setToday() {

	current = get_today();
	$("#main-cal-area").html(loading_text).show();
	show_cal();
	
}

/**********************************************************************
 *
 * 	ドラッグアンドドロップイベントセット
 *
 **********************************************************************/
function set_dd() {

	//$(".box, .box2").draggable({zIndex:100});

	//発注締め済みはDD出来ない
	$(".box, .box2, .boxip2").each(function(i, v) {
		var attr = $(this).attr("data-no-dd");
		
		if (typeof attr !== 'undefined' && attr !== false) {
		}
		else {
			$(this).draggable({zIndex:100});
		}
	});
	
	
	$("table.cal td").droppable(
		{
		activeClass: "td-active",    // Draggable要素がドラッグしているときに適用するクラス
    	hoverClass: "td-hover",  
		tolerance:"pointer",
         drop: function(e, ui) {
			 
            if(mobile_flag)
			 	r = confirm("移動してもよろしいですか？");
			else
			 	r = confirm("移動してもよろしいですか？");

            if(r) {
			 	r = confirm("本当に移動してもよろしいですか？");
                
                if(r) {
					//ドロップしたらDD禁止に戻す
					//localStorage.setItem("dd_chk_flag", 1);
					dd_chk_flag = true;

                    //現調スケジュールDD
                     if( ui.helper.hasClass("boxip2") ) {
                        update_sch_gencho(this, ui.helper);
                     }
                     else {
                        update_sch(this, ui.helper);
                     }
                 }
                else {
                    show_cal();
                }
            }
            else {
                    show_cal();
            }
         }
	});
}

/**********************************************************************
 *
 * 	現調スケジュールの更新
 *
 **********************************************************************/
function update_sch_gencho(_obj, _box) {

	var flag = "UPDATE_GENCHO_SCH";
	
	var _date = $(_obj).data("date");
	var seko_id = $(_obj).data("seko_id");
	var s_id = $(_box).data("sp_id");
	var kikan = $(_box).data("kikan");
	var s_is_jv = $(_box).data("s_is_jv");
	var s_f_year = $(_obj).data("s_f_year");
	var s_f_sch_id = $(_obj).data("s_f_sch_id");
	
	if(s_f_year == undefined || s_f_sch_id == undefined) {
		s_f_year = 0;
		s_f_sch_id = 0;
	}

	$.ajaxPrefilter(function (options, originalOptions, jqXHR) {
		if(originalOptions.type.toLowerCase() == 'post'){        
			options.data = jQuery.param($.extend(originalOptions.data||{}, {
			timeStamp: new Date().getTime()
			}));
		}
	});
	$.ajax({
		async:		true,
		cache:		false,
		url:		"ajax.php",
		data:		{parm:[flag, _date, s_id, kikan-1, seko_id, s_is_jv,s_f_year,s_f_sch_id]},
		type:		"post",
		headers: 	{"pragma": "no-cache"},
		success:	function(data, textStatus) {
			show_cal();
		},
		complete:	function(data, textStatus) {
		},
		error:		function(data, textStatus) {
		}
	});

}


/**********************************************************************
 *
 * 	スケジュールの更新
 *
 **********************************************************************/
function update_sch(_obj, _box) {

	var flag = "UPDATE_SCH";
	
	var _date = $(_obj).data("date");
	var seko_id = $(_obj).data("seko_id");
	var s_id = $(_box).data("s_id");
	var kikan = $(_box).data("kikan");
	var s_is_jv = $(_box).data("s_is_jv");
	var s_f_year = $(_obj).data("s_f_year");
	var s_f_sch_id = $(_obj).data("s_f_sch_id");
	
	if(s_f_year == undefined || s_f_sch_id == undefined) {
		s_f_year = 0;
		s_f_sch_id = 0;
	}

	$.ajaxPrefilter(function (options, originalOptions, jqXHR) {
		if(originalOptions.type.toLowerCase() == 'post'){        
			options.data = jQuery.param($.extend(originalOptions.data||{}, {
			timeStamp: new Date().getTime()
			}));
		}
	});
	$.ajax({
		async:		true,
		cache:		false,
		url:		"ajax.php",
		data:		{parm:[flag, _date, s_id, kikan-1, seko_id, s_is_jv,s_f_year,s_f_sch_id]},
		type:		"post",
		headers: 	{"pragma": "no-cache"},
		success:	function(data, textStatus) {
			show_cal();
		},
		complete:	function(data, textStatus) {
		},
		error:		function(data, textStatus) {
		}
	});

}

/**********************************************************************
 *
 * 	現在日付をセット
 *
 **********************************************************************/
function setCurrent(_p) {
	current = _p;
	$("#main-cal-area").html(loading_text).show();

	show_cal();	
	
}

/**********************************************************************
 *
 * 	現在期間をセット
 *
 **********************************************************************/
function setKikan(_p) {
	
	kikan = _p;
	$("#main-cal-area").html(loading_text).show();
	show_cal();	
}

/**********************************************************************
 *
 * 	スケジュール追加メイン画面表示
 *
 **********************************************************************/
function addSche(_d, _s) {

	if(!is_admin(user)) {
		edit_holiday(0, _d, _s);
	}
	
	else if(_s == "1000") {
		edit_ippan(0,_d);
	}
	else if(_s == "0") {
		var w=window.open();
		w.location.href="../../system2/main/?0&date="+_d;
	}
	else {
		$("#main-cal-area").html(loading_text).show();
	
		var flag = "ADD_SCH";
	
		$.ajaxPrefilter(function (options, originalOptions, jqXHR) {
			if(originalOptions.type.toLowerCase() == 'post'){        
				options.data = jQuery.param($.extend(originalOptions.data||{}, {
				timeStamp: new Date().getTime()
				}));
			}
		});
		$.ajax({
			async:		true,
			cache:		false,
			url:		"ajax.php",
			data:		{parm:[flag, _d, _s]},
			type:		"post",
			headers: 	{"pragma": "no-cache"},
			success:	function(data, textStatus) {
				$("#main-cal-area").html(data);
				jump_top();
			},
			complete:	function(data, textStatus) {
			},
			error:		function(data, textStatus) {
			}
		});
	}
}

/**********************************************************************
 *
 * 	日付加算
 *
 **********************************************************************/
function calcDate(_p) {
	var myDate = new Date(current);
	var dayOfMonth = myDate.getDate();
	myDate.setDate(dayOfMonth + _p * 7);

	var year = myDate.getFullYear();  
	var month = myDate.getMonth() + 1;  
	var day = myDate.getDate();  
	  
	if ( month < 10 ) {  
	　　month = '0' + month;  
	}  
	if ( day < 10 ) {  
	　　day = '0' + day;  
	}  
	  
	var str = year + '-' + month + '-' + day;  	
	current = str;

	$("#main-cal-area").html(loading_text).show();
	show_cal();	

}

/**********************************************************************
 *
 * 	一般スケジュール編集
 *
 **********************************************************************/
function edit_ippan(_p, _d) {
	var flag = "UPDATE_IPPAN";
	$("#main-cal-area").html(loading_text).show();
	
	$.ajaxPrefilter(function (options, originalOptions, jqXHR) {
		if(originalOptions.type.toLowerCase() == 'post'){        
			options.data = jQuery.param($.extend(originalOptions.data||{}, {
			timeStamp: new Date().getTime()
			}));
		}
	});
	$.ajax({
		async:		true,
		cache:		false,
		url:		"ajax.php",
		data:		{parm:[flag, _p, _d]},
		type:		"post",
		headers: 	{"pragma": "no-cache"},
		success:	function(data, textStatus) {
			$("#main-cal-area").html(data);
			jump_top();
			if(ie_flag) {
				$("#ip_date").datepicker({
					inline: true,
					showButtonPanel: true,
					changeMonth: true
				});
			}
			
		},
		complete:	function(data, textStatus) {
		},
		error:		function(data, textStatus) {
		}
	});

}

/**********************************************************************
 *
 * 	一般スケジュール更新
 *
 **********************************************************************/
function ippan_update() {
	var MAIN_TABLE = "matsushima_ippan";
	var MAIN_ID_FIELD = "ip_id";
	var clm = new Array("ip_date","ip_seko_id","ip_desc");
	var id = $("#"+MAIN_ID_FIELD).val();

	var sql = "";

	//新規か更新か
	if(id) { //更新
		sql += "UPDATE "+MAIN_TABLE+" SET ";
		for(var cnt=0;cnt<clm.length;cnt++) {
			
			sql += clm[cnt] + " = '"+ h($("#"+clm[cnt]).val()) +"'";
			sql += ",";
		}
		sql = sql.replace(/,$/,'');
		sql += " WHERE "+MAIN_ID_FIELD+" ="+id+";;";
		
	}
	else {
		//新規
		sql += "INSERT INTO "+MAIN_TABLE+" (";
		for(var cnt=0;cnt<clm.length;cnt++) {
			sql += clm[cnt];
			sql += ",";
		}
		sql = sql.replace(/,$/,'');
		sql += ") VALUES ( ";

		for(var cnt=0;cnt<clm.length;cnt++) {
			sql += "'"+h($("#"+clm[cnt]).val())+"'";
			sql += ",";
		}
		sql = sql.replace(/,$/,'');
		sql += ");;";
	}

	var ip_id = h($("#ip_id").val());
	if(!ip_id)
		ip_id = "temp_main_id";
	else {
		sql += "DELETE FROM matsushima_sche_tantou_rel WHERE sc_slip_id = '"+ip_id+"';;";
		sql += "DELETE FROM matsushima_sche_rel WHERE sc_slip_id = '"+ip_id+"';;";
	}
	//現調担当
	$(".tantou_seko").each(function(i, e) {
		if($(this).attr("checked") == "checked") {
			sql += "INSERT INTO matsushima_sche_tantou_rel (sc_seko_id, sc_slip_id) VALUES (";
			sql += "'"+$(this).val()+"'";
			sql += ",";
			sql += "'"+ip_id+"'";
			sql += ");;";
		}
	});
	//職方
	$(".jv_seko").each(function(i, e) {
		if($(this).attr("checked") == "checked") {
			sql += "INSERT INTO matsushima_sche_rel (sc_seko_id, sc_slip_id) VALUES (";
			sql += "'"+$(this).val()+"'";
			sql += ",";
			sql += "'"+ip_id+"'";
			sql += ");;";
		}
	});
	sql = sql.replace(/;;$/,'');
	//write_debug(sql);

	$.ajaxPrefilter(function (options, originalOptions, jqXHR) {
		if(originalOptions.type.toLowerCase() == 'post'){        
			options.data = jQuery.param($.extend(originalOptions.data||{}, {
			timeStamp: new Date().getTime()
			}));
		}
	});
	$.ajax({
		async:		true,
		cache:		false,
		url:		"sql.php",
		data:		{"parm[]": [sql]},
		type:		"post",
		headers: 	{"pragma": "no-cache"},
		success:	function(data, textStatus) {
			$("#main-cal-area").html(loading_text).show();
			show_cal();
		},
		complete:	function(data, textStatus) {
		},
		error:		function(data, textStatus) {
			alert("保存が失敗しました。");
		}
	});
}

/**********************************************************************
 *
 * 	一般スケジュール削除
 *
 **********************************************************************/
function ippan_delete() {
	var r = confirm("削除してもよろしいですか？");
	if(r) {
		var MAIN_TABLE = "matsushima_ippan";
		var MAIN_ID_FIELD = "ip_id";
		var id = $("#"+MAIN_ID_FIELD).val();
	
		var sql = "";
	
		sql += "DELETE FROM "+MAIN_TABLE;
		sql += " WHERE "+MAIN_ID_FIELD+" ="+id+";;";
	
		sql = sql.replace(/;;$/,'');
		//write_debug(sql);
	
		$.ajaxPrefilter(function (options, originalOptions, jqXHR) {
			if(originalOptions.type.toLowerCase() == 'post'){        
				options.data = jQuery.param($.extend(originalOptions.data||{}, {
				timeStamp: new Date().getTime()
				}));
			}
		});
		$.ajax({
			async:		true,
			cache:		false,
			url:		"sql.php",
			data:		{"parm[]": [sql]},
			type:		"post",
			headers: 	{"pragma": "no-cache"},
			success:	function(data, textStatus) {
				$("#main-cal-area").html(loading_text).show();
				show_cal();
			},
			complete:	function(data, textStatus) {
			},
			error:		function(data, textStatus) {
				alert("削除が失敗しました。");
			}
		});
	}
}

/**********************************************************************
 *
 * 	休日スケジュール編集
 *
 **********************************************************************/
function edit_holiday(_p, _d, _s) {
	var flag = "UPDATE_HOLIDAY";
	$("#main-cal-area").html(loading_text).show();
	
	$.ajaxPrefilter(function (options, originalOptions, jqXHR) {
		if(originalOptions.type.toLowerCase() == 'post'){        
			options.data = jQuery.param($.extend(originalOptions.data||{}, {
			timeStamp: new Date().getTime()
			}));
		}
	});
	$.ajax({
		async:		true,
		cache:		false,
		url:		"ajax.php",
		data:		{parm:[flag, _p, _d, _s]},
		type:		"post",
		headers: 	{"pragma": "no-cache"},
		success:	function(data, textStatus) {
			$("#main-cal-area").html(data);
			jump_top();
			if(ie_flag) {
				$("#hl_date").datepicker({
					inline: true,
					showButtonPanel: true,
					changeMonth: true
				});
			}
		},
		complete:	function(data, textStatus) {
		},
		error:		function(data, textStatus) {
		}
	});

}

/**********************************************************************
 *
 * 	休日スケジュール更新
 *
 **********************************************************************/
function holiday_update() {
	var MAIN_TABLE = "matsushima_holiday";
	var MAIN_ID_FIELD = "hl_id";
	var clm = new Array("hl_date","hl_seko_id","hl_desc", "hl_hour");
	var id = $("#"+MAIN_ID_FIELD).val();

	var sql = "";

	//新規か更新か
	if(id) { //更新
		sql += "UPDATE "+MAIN_TABLE+" SET ";
		for(var cnt=0;cnt<clm.length;cnt++) {
			sql += clm[cnt] + " = '"+ h($("#"+clm[cnt]).val()) +"'";
			sql += ",";
		}
		sql = sql.replace(/,$/,'');
		sql += " WHERE "+MAIN_ID_FIELD+" ="+id+";;";
		
	}
	else {
		//新規
		sql += "INSERT INTO "+MAIN_TABLE+" (";
		for(var cnt=0;cnt<clm.length;cnt++) {
			sql += clm[cnt];
			sql += ",";
		}
		sql = sql.replace(/,$/,'');
		sql += ") VALUES ( ";

		for(var cnt=0;cnt<clm.length;cnt++) {
			sql += "'"+h($("#"+clm[cnt]).val())+"'";
			sql += ",";
		}
		sql = sql.replace(/,$/,'');
		sql += ");;";
	}

	sql = sql.replace(/;;$/,'');
	//write_debug(sql);

	$.ajaxPrefilter(function (options, originalOptions, jqXHR) {
		if(originalOptions.type.toLowerCase() == 'post'){        
			options.data = jQuery.param($.extend(originalOptions.data||{}, {
			timeStamp: new Date().getTime()
			}));
		}
	});
	$.ajax({
		async:		true,
		cache:		false,
		url:		"sql.php",
		data:		{"parm[]": [sql]},
		type:		"post",
		headers: 	{"pragma": "no-cache"},
		success:	function(data, textStatus) {
			$("#main-cal-area").html(loading_text).show();
			show_cal();
		},
		complete:	function(data, textStatus) {
		},
		error:		function(data, textStatus) {
			alert("保存が失敗しました。");
		}
	});
}

/**********************************************************************
 *
 * 	休日スケジュール削除
 *
 **********************************************************************/
function holiday_delete() {
	
	var r = confirm("削除してもよろしいですか？");
	if(r) {
		var MAIN_TABLE = "matsushima_holiday";
		var MAIN_ID_FIELD = "hl_id";
		var id = $("#"+MAIN_ID_FIELD).val();
	
		var sql = "";
	
		sql += "DELETE FROM "+MAIN_TABLE;
		sql += " WHERE "+MAIN_ID_FIELD+" ="+id+";;";
	
		sql = sql.replace(/;;$/,'');
		//write_debug(sql);
	
		$.ajaxPrefilter(function (options, originalOptions, jqXHR) {
			if(originalOptions.type.toLowerCase() == 'post'){        
				options.data = jQuery.param($.extend(originalOptions.data||{}, {
				timeStamp: new Date().getTime()
				}));
			}
		});
		$.ajax({
			async:		true,
			cache:		false,
			url:		"sql.php",
			data:		{"parm[]": [sql]},
			type:		"post",
			headers: 	{"pragma": "no-cache"},
			success:	function(data, textStatus) {
				$("#main-cal-area").html(loading_text).show();
				show_cal();
			},
			complete:	function(data, textStatus) {
			},
			error:		function(data, textStatus) {
				alert("削除が失敗しました。");
			}
		});
	}
}

/**********************************************************************
 *
 * 	新規現場
 *
 **********************************************************************/
function add_genba_kani(_p, _d, _s) {
	var flag = "NEW_GENBA";
	$("#main-cal-area").html(loading_text).show();
	
	$.ajaxPrefilter(function (options, originalOptions, jqXHR) {
		if(originalOptions.type.toLowerCase() == 'post'){        
			options.data = jQuery.param($.extend(originalOptions.data||{}, {
			timeStamp: new Date().getTime()
			}));
		}
	});
	$.ajax({
		async:		true,
		cache:		false,
		url:		"ajax.php",
		data:		{parm:[flag, _p, _d, _s]},
		type:		"post",
		headers: 	{"pragma": "no-cache"},
		success:	function(data, textStatus) {
			$("#main-cal-area").html(data);
			jump_top();
			if(ie_flag) {
				$("#s_st_date").datepicker({
					inline: true,
					showButtonPanel: true,
					changeMonth: true
				});
				$("#s_end_date").datepicker({
					inline: true,
					showButtonPanel: true,
					changeMonth: true
				});
			}
		},
		complete:	function(data, textStatus) {
		},
		error:		function(data, textStatus) {
		}
	});

}
/**********************************************************************
 *
 * 	新規現場作成
 *
 **********************************************************************/
function new_genba_update(_p) {

	var sql = "";

	var MAIN_TABLE = "matsushima_genba";
	var clm = new Array("g_input_date","g_genba","g_genba_address","g_tantou_id","g_moto_id","g_moto_tantou","g_nai1_id","g_nai2_id","g_nai3_id","g_m2","g_biko");

	//新規 現場
	sql += "INSERT INTO "+MAIN_TABLE+" (";
	for(var cnt=0;cnt<clm.length;cnt++) {
		sql += clm[cnt];
		sql += ",";
	}
	sql = sql.replace(/,$/,'');
	sql += ") VALUES ( ";

	for(var cnt=0;cnt<clm.length;cnt++) {
		sql += "'"+h($("#"+clm[cnt]).val())+"'";
		sql += ",";
	}
	sql = sql.replace(/,$/,'');
	sql += ");;";

	var MAIN_TABLE = "matsushima_slip_hat";
	if(_p == 1)
		MAIN_TABLE = "matsushima_slip_jv";
	var clm = new Array("s_genba_id","s_seko_kubun_id","s_seko_id","s_date","s_st_date","s_end_date","s_hattyu","s_biko");
	//temp_main_id

	//新規 発注
	sql += "INSERT INTO "+MAIN_TABLE+" (";
	for(var cnt=0;cnt<clm.length;cnt++) {
		sql += clm[cnt];
		sql += ",";
	}
	sql = sql.replace(/,$/,'');
	sql += ") VALUES ( ";

	for(var cnt=0;cnt<clm.length;cnt++) {
		sql += "'"+h($("#"+clm[cnt]).val())+"'";
		sql += ",";
	}
	sql = sql.replace(/,$/,'');
	sql += ");;";

	sql = sql.replace(/;;$/,'');
	//write_debug(sql);

	$.ajaxPrefilter(function (options, originalOptions, jqXHR) {
		if(originalOptions.type.toLowerCase() == 'post'){        
			options.data = jQuery.param($.extend(originalOptions.data||{}, {
			timeStamp: new Date().getTime()
			}));
		}
	});
	$.ajax({
		async:		true,
		cache:		false,
		url:		"sql.php",
		data:		{"parm[]": [sql]},
		type:		"post",
		headers: 	{"pragma": "no-cache"},
		success:	function(data, textStatus) {
			$("#main-cal-area").html(loading_text).show();
			show_cal();
		},
		complete:	function(data, textStatus) {
		},
		error:		function(data, textStatus) {
			alert("保存が失敗しました。");
		}
	});
}


/********************************************************************************************************************************************/

/**********************************************************************
 *
 * 	既存現場
 *
 **********************************************************************/
function add_genba_kizon(_p, _d, _s) {
	var flag = "NEW_HAT";
	$("#main-cal-area").html(loading_text).show();
	
	$.ajaxPrefilter(function (options, originalOptions, jqXHR) {
		if(originalOptions.type.toLowerCase() == 'post'){        
			options.data = jQuery.param($.extend(originalOptions.data||{}, {
			timeStamp: new Date().getTime()
			}));
		}
	});
	$.ajax({
		async:		true,
		cache:		false,
		url:		"ajax.php",
		data:		{parm:[flag, _p, _d, _s]},
		type:		"post",
		headers: 	{"pragma": "no-cache"},
		success:	function(data, textStatus) {
			$("#main-cal-area").html(data);
			jump_top();
			if(ie_flag) {
				$("#s_st_date").datepicker({
					inline: true,
					showButtonPanel: true,
					changeMonth: true
				});
				$("#s_end_date").datepicker({
					inline: true,
					showButtonPanel: true,
					changeMonth: true
				});
			}
		},
		complete:	function(data, textStatus) {
		},
		error:		function(data, textStatus) {
		}
	});

}
/**********************************************************************
 *
 * 	既存現場作成
 *
 **********************************************************************/
function kizon_genba_update(_p) {

	var sql = "";

	var MAIN_TABLE = "matsushima_slip_hat";
	if(_p == 1)
		MAIN_TABLE = "matsushima_slip_jv";
		
	var clm = new Array("s_genba_id","s_seko_kubun_id","s_seko_id","s_date","s_st_date","s_end_date","s_hattyu","s_biko");
	//temp_main_id

	//新規 発注
	sql += "INSERT INTO "+MAIN_TABLE+" (";
	for(var cnt=0;cnt<clm.length;cnt++) {
		sql += clm[cnt];
		sql += ",";
	}
	sql = sql.replace(/,$/,'');
	sql += ") VALUES ( ";

	for(var cnt=0;cnt<clm.length;cnt++) {
		sql += "'"+h($("#"+clm[cnt]).val())+"'";
		sql += ",";
	}
	sql = sql.replace(/,$/,'');
	sql += ");;";

	sql = sql.replace(/;;$/,'');
	//write_debug(sql);

	$.ajaxPrefilter(function (options, originalOptions, jqXHR) {
		if(originalOptions.type.toLowerCase() == 'post'){        
			options.data = jQuery.param($.extend(originalOptions.data||{}, {
			timeStamp: new Date().getTime()
			}));
		}
	});
	$.ajax({
		async:		true,
		cache:		false,
		url:		"sql.php",
		data:		{"parm[]": [sql]},
		type:		"post",
		headers: 	{"pragma": "no-cache"},
		success:	function(data, textStatus) {
			$("#main-cal-area").html(loading_text).show();
			show_cal();
		},
		complete:	function(data, textStatus) {
		},
		error:		function(data, textStatus) {
			alert("保存が失敗しました。");
		}
	});
}

/**********************************************************************
 *
 * 	職方用現場情報表示
 *
 **********************************************************************/
function show_genba_info(_id, _elm) {

	var flag = "GET_GENBA_INFO";
	
	//テキストボックスの位置を取得
	var pos = $("#"+_elm).offset();
	
	$("#optional-area").css("top",pos.top + 40).css("left",pos.left + 40).slideDown("fast");
	$("#optional-area-inner").html("<p class='tac mt50'>読み込み中です...");

	$.ajaxPrefilter(function (options, originalOptions, jqXHR) {
		if(originalOptions.type.toLowerCase() == 'post'){        
			options.data = jQuery.param($.extend(originalOptions.data||{}, {
			timeStamp: new Date().getTime()
			}));
		}
	});
		
	$.ajax({
		async:		true,
		cache:		false,
		url:		"./ajax.php",
		data:		{parm:[flag, _id]},
		type:		"post",
		headers: 	{"pragma": "no-cache"},
		success:	function(data, textStatus) {
			$("#optional-area-inner").html(data);
		},
		complete:	function(data, textStatus) {
			
			$("#dialog-area").click(function(event) {
				close_optional();
				event.stopPropagation();
			});
			
			$("body").unbind("click");
			$("body").click(function(event) {
				close_optional();
				event.stopPropagation();
			});
			
			//オプショナルエリアでのイベントを抑止
			$("#optional-area").unbind("click");
			$("#optional-area").click(function(event) {
				event.stopPropagation();
			});
			
			//閉じるボタンイベント
			$("#optional-area .closeButton").unbind("click");
			$("#optional-area .closeButton").click(function(event) {
				close_optional();
				event.stopPropagation();
			});
		},
		error:		function(data, textStatus) {
			write_debug(textStatus);
		}
	});
}

/**********************************************************************
 *
 * 	オプショナルダイアログを閉じる関数
 *
 **********************************************************************/
function close_optional() {
	
	//イベント解除
	$("body").unbind("click");
	$("#optional-area").unbind("click");
	$("#optional-area .closeButton").unbind("click");
	//フェードアウト
	$("#optional-area").slideUp("fast");
	
}

/**********************************************************************
 *
 * 	検索画面
 *
 **********************************************************************/
function search_sche() {
	
	var search_wd = $("#search_wd").val();
	var search_moto_id = $("#search_moto_id").val();
	var search_tantou_id = $("#search_tantou_id").val();
	var search_kubun_id = $("#search_kubun_id").val();
	var search_seko_id = $("#search_seko_id").val();
	var search_gid = $("#search_gid").val();
	var search_sid = $("#search_sid").val();

	var flag = "SEARCH_EXEC";
	$("#main-cal-area").html(loading_text).show();
	
	$.ajaxPrefilter(function (options, originalOptions, jqXHR) {
		if(originalOptions.type.toLowerCase() == 'post'){        
			options.data = jQuery.param($.extend(originalOptions.data||{}, {
			timeStamp: new Date().getTime()
			}));
		}
	});
	$.ajax({
		async:		true,
		cache:		false,
		url:		"ajax.php",
		data:		{parm:[flag, search_wd,search_moto_id,search_tantou_id,search_kubun_id,search_seko_id,search_gid,search_sid]},
		type:		"post",
		headers: 	{"pragma": "no-cache"},
		success:	function(data, textStatus) {
			$("#main-cal-area").html(data);
			$(".search-result tr:odd").css("background","#EEE");
			jump_top();
		},
		complete:	function(data, textStatus) {
		},
		error:		function(data, textStatus) {
		}
	});

}


/**********************************************************************
 *
 * 	検索結果のカレンダーを表示
 *
 **********************************************************************/
function show_cal_by_search(_d, _s) {
	current = _d;
	$("#main-cal-area").html(loading_text).show();

	show_cal(_s);	
}


/********************************************************************************************************************************************/

/**********************************************************************
 *
 * 	本日日付文字列を生成する
 *
 **********************************************************************/
function get_today() {
	var date = new Date();  
	var year = date.getFullYear();  
	var month = date.getMonth() + 1;  
	var day = date.getDate();  
	  
	if ( month < 10 ) {  
	　　month = '0' + month;  
	}  
	if ( day < 10 ) {  
	　　day = '0' + day;  
	}  
	  
	var str = year + '-' + month + '-' + day;  	
	return str;
}

/**********************************************************************
 *
 *  js版エスケープ処理
 *
 **********************************************************************/
function h(ch) {
	
	//半角カナ数字変換
	ch = toHankakuNum(ch);
	if(ch == null || ch == '')
		return '';
	 
	//エスケープ処理
    ch = ch.replace(/&/g,"&amp;") ;
    ch = ch.replace(/"/g,"&quot;") ;
    ch = ch.replace(/'/g,"&#039;") ;
    ch = ch.replace(/</g,"&lt;") ;
    ch = ch.replace(/>/g,"&gt;") ;

    //円マーク
	ch = ch.replace(/\\/g,"￥") ;
	//;の二回以上のくり返しを変換 ;;はsqlの区切り
    ch = ch.replace(/([;])\1+/g,"$1") ; 
    return ch ;
}

/**********************************************************************
 *
 *  全角英数字・記号を半角に置換
 *
 **********************************************************************/
function toHankakuNum(src) {
	
  if(src == null || src == "")
  	return '';	

  //トリミング
  src = String(src).trim();
	
  var str = '';
  var len = src.length;
  for (var i = 0; i < len; i++) {
    var c = src.charCodeAt(i);
    if (c >= 65281 && c <= 65374 && c != 65340) {
      str += String.fromCharCode(c - 65248);
    } else if (c == 8217) {
      str += String.fromCharCode(39);
    } else if (c == 8221) {
      str += String.fromCharCode(34);
    } else if (c == 12288) {
      str += String.fromCharCode(32);
    } else if (c == 65507) {
      str += String.fromCharCode(126);
    } else if (c == 65509) {
      str += String.fromCharCode(92);
    } else {
      str += src.charAt(i);
    } 
  }

  //半角カタカナ全角変換 
  str = OneByteCharToFullSize(str);  

  return str;
};

// 全角カナの文字テーブル
var fullSizeCharacter = new Array(
  "。", "「", "」", "、", "・", "ヲ", "ァ", "ィ", "ゥ", "ェ",
  "ォ", "ャ", "ュ", "ョ", "ッ", "ー", "ア", "イ", "ウ", "エ",
  "オ", "カ", "キ", "ク", "ケ", "コ", "サ", "シ", "ス", "セ",
  "ソ", "タ", "チ", "ツ", "テ", "ト", "ナ", "ニ", "ヌ", "ネ",
  "ノ", "ハ", "ヒ", "フ", "ヘ", "ホ", "マ", "ミ", "ム", "メ",
  "モ", "ヤ", "ユ", "ヨ", "ラ", "リ", "ル", "レ", "ロ", "ワ",
  "ン", "゛", "゜"
);

// カタカナ文字であるか判別する
function IsKatakanaCode(c)
{
  return (c >= 65377 && c <= 65439);
}

// カタカナで「カ」～「ト」であるか判別する
function IsCode_KA_TO(c)
{
  return (c >= 65398 && c <= 65412);
}

// カタカナで「ハ」～「ホ」であるか判別する
function IsCode_HA_HO(c)
{
  return (c >= 65418 && c <= 65422);
}

// 半角カタカナを全角カタカナに変換する
function OneByteCharToFullSize(src)
{
  // 引数のチェック
  if(src == null)
    return "";

  var i, code, next;
  var str = new String;
  var len = src.length;
  for(i = 0; i < len; i++)
  {
    var c = src.charCodeAt(i); // 文字をキャラクターコードにする
    if(IsKatakanaCode(c))
    {
      // ==================
      // カタカナ文字の場合
      // ==================
      code = fullSizeCharacter[c - 65377];
      if(i < len - 1)
      {
        next = src.charCodeAt(i+1);
        if(next == 65438 && c == 65395) // "ヴ"の文字を正しく置換する
        {
          code = "ヴ";
          i++;
        }
        else if(next == 65438 && (IsCode_KA_TO(c) || IsCode_HA_HO(c))) // "濁音"の文字を正しく置換する
        {
          code = String.fromCharCode(code.charCodeAt(0)+1);
          i++;
        }
        else if (next == 65439 && IsCode_HA_HO(c)) // "半濁音"の文字を正しく置換する
        {
          code = String.fromCharCode(code.charCodeAt(0)+2);
          i++;
        }
      }
      str += code;
    }
    else
    {
      // ================
      // 通常の文字の場合
      // ================
      str += src.charAt(i);
    }
  }

  return str;
}

//Topへ戻る
function jump_top() {
	var x1 = x2 = x3 = 0;
	var y1 = y2 = y3 = 0;
	if (document.documentElement) {
	x1 = document.documentElement.scrollLeft || 0;
	y1 = document.documentElement.scrollTop || 0;
	}
	if (document.body) {
	x2 = document.body.scrollLeft || 0;
	y2 = document.body.scrollTop || 0;
	}
	x3 = window.scrollX || 0;
	y3 = window.scrollY || 0;
	var x = Math.max(x1, Math.max(x2, x3));
	var y = Math.max(y1, Math.max(y2, y3));
	window.scrollTo(Math.floor(x / 2), Math.floor(y / 2));
	if (x > 0 || y > 0) {
	window.setTimeout("jump_top()", 10);
	}
}

function check_ctr(_p) {
	if(_p)
		$(".jv_seko").attr("checked","checked");
	else
		$(".jv_seko").removeAttr("checked");
}

function print_gencho(_id) {
	if(_id == "" || _id == null || _id == 0) {
		show_fail("現場IDの指定が不正です。","");
	}
	else {
		subwin20=window.open("print_gencho.php?id="+_id+"&mode=prn" ,"sub20", "width=950,height=700, scrollbars=yes, resizable=yes");
		subwin20.focus();
	}
}

function is_admin(u) {
	if(u == "granz" || u == "sp" || u == 'maki' || u == 'yuji' || u == 'sasanuma') {
		return true;
	}
	else {
		return false;
	}
}
