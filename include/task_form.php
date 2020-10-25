<?php

// XoopsFormライブラリを読み込む
require XOOPS_ROOT_PATH . '/class/xoopsformloader.php';

function get_formtextdateselect($name, $value)
{
    $jstime = formatTimestamp(time(), 'F j Y, H:i:s'); // $jstime に今日の日付と時間を代入

    $today = formatTimestamp(time(), 's'); // $today に今日の日付を代入

    return "
		<input type='text' name='$name' id='$name' size='12' maxlength='12' value='$value'>
		<input type='reset' value='" . _MD_TODOMOD_CALENDER . "' onclick='
  
  MonthTbl= new Array(31,28,31,30,31,30,31,31,30,31,30,31); // 各月の最後の日付

  var el = xoopsGetElementById(\"$name\"); // 「el」は、「\$name」のIDをもったHTMLエレメント

  if (el.value == \"\") el.value = \"$today\"; // エレメントの値に今日の日付を代入
  
  deadline = el.value.preg_split(\"-\"); // フォームの値を「-」によって分割し、「deadline」に配列として代入
  
  // parseInt()の為に頭の余分な「0」を取り除く
  if ( !( isNaN(deadline[0]) && isNaN(deadline[1]) && isNaN(deadline[2]) ) ) { // 数値に3分割されていれば、、
  	deadline[0]= deadline[0].substring(deadline[0].search(/[1-9]/)); // 初めの0を取り除いて、、
  	deadline[1]= deadline[1].substring(deadline[1].search(/[1-9]/));
  	deadline[2]= deadline[2].substring(deadline[2].search(/[1-9]/));
  	deadline_tmp = deadline[0]+\"-\"+deadline[1]+\"-\"+deadline[2]; // エレメントの値を再構成し代入
  }

  if (  parseInt(deadline[0]) < 2000 || parseInt(deadline[0]) > 2015 || isNaN(deadline[0]) || // 分割された各値が、
        parseInt(deadline[1]) < 1 || parseInt(deadline[1]) > 12      || isNaN(deadline[1]) || // 不正であれば、、
        parseInt(deadline[2]) < 1 || parseInt(deadline[2]) > MonthTbl[parseInt(deadline[1])-1] || isNaN(deadline[2]) )
	deadline_tmp = \"$today\"; // エレメントの値に今日の日付を代入
  
  // 月、日、が一桁だった場合、「0」を付け加える
  deadline = deadline_tmp.preg_split(\"-\");
  if ( deadline[1].length < 2  ) { deadline[1]=\"0\"+deadline[1];}
  if ( deadline[2].length < 2  ) { deadline[2]=\"0\"+deadline[2];}
  el.value = deadline[0]+\"-\"+deadline[1]+\"-\"+deadline[2];

  if (calendar != null) {
    calendar.hide();
    calendar.parseDate(el.value);
  } else {
    var cal = new Calendar(false, new Date(\"$jstime\"), selected, closeHandler);
    calendar = cal;
    cal.setRange(2000, 2015);
    calendar.create();
    calendar.parseDate(el.value);
  }
  calendar.sel = el;
  calendar.showAtElement(el);
  Calendar.addEvent(document, \"mousedown\", checkCalendar);
  return false;
		'>
	";
}

//タスクの新規投稿フォームを表示。
if ('trashbox' == $mode) {
    $task_form = new XoopsThemeForm(_MD_TODOMOD_NEWTASK, 'todomod_iform', 'task_post.php?mode=trashbox');
} else {
    $task_form = new XoopsThemeForm(_MD_TODOMOD_NEWTASK, 'todomod_iform', 'task_post.php');
}

//'タイトル'
$task_form->addElement(new XoopsFormText(_TITLE, 'task_title', $xoopsModuleConfig['title_width'], 255, $task_title ?? ''), false);

//'期限'
$deadline_tray = new XoopsFormElementTray(_MD_TODOMOD_DEADLINE, '');
$deadline_tray->addElement(new XoopsFormLabel(get_formtextdateselect('task_deadline', $task_deadline ?? '')));
$deadline_tray->addElement(new XoopsFormButton('', 'task_deadline', _MD_TODOMOD_CLEAR, 'reset'));
$task_form->addElement($deadline_tray);

//'優先度'
$task_select_priority = new XoopsFormSelect(_MD_TODOMOD_PRIORITY, 'task_priority', $task_priority);
$task_select_priority->addOption('0', _MD_TODOMOD_PRIORITY_0);
$task_select_priority->addOption('1', _MD_TODOMOD_PRIORITY_1);
$task_select_priority->addOption('2', _MD_TODOMOD_PRIORITY_2);
$task_select_priority->addOption('3', _MD_TODOMOD_PRIORITY_3);
$task_select_priority->addOption('4', _MD_TODOMOD_PRIORITY_4);
$task_form->addElement($task_select_priority);

//'状況'
$task_select_status = new XoopsFormSelect(_MD_TODOMOD_STATUS, 'task_status', $task_status ?? 0);
$task_select_status->addOption('0', _MD_TODOMOD_STATUS_0);
$task_select_status->addOption('1', _MD_TODOMOD_STATUS_1);
$task_select_status->addOption('2', _MD_TODOMOD_STATUS_2);
$task_select_status->addOption('3', _MD_TODOMOD_STATUS_3);
$task_select_status->addOption('4', _MD_TODOMOD_STATUS_4);
$task_form->addElement($task_select_status);

//'詳細'
$task_form->addElement(new XoopsFormTextArea(_MD_TODOMOD_DETAIL, 'task_detail', $task_detail ?? '', $xoopsModuleConfig['detail_length'], $xoopsModuleConfig['detail_width']), false);

//'送信'、'戻る'、ボタンを表示。
$button_tray = new XoopsFormElementTray('', '');
$button_submit = new XoopsFormButton('', 'submitpost', _SUBMIT, 'submit');
$button_submit->setExtra('onSubmit="return FormCheck()"');
$button_tray->addElement($button_submit);
$button_cancel = new XoopsFormButton('', 'submitpost', _BACK, 'button');
$button_cancel->setExtra('onclick="javascript:history.back(-1)"');
$button_tray->addElement($button_cancel);
$task_form->addElement($button_tray);

//task_idを隠して入れておく。
$task_form->addElement(new XoopsFormHidden('task_id', $task_id));

//ポップアップアラート要メッセージをを隠して入れておく。
$task_form->addElement(new XoopsFormHidden('alert_message_0', _MD_TODOMOD_ALERT_0));
$task_form->addElement(new XoopsFormHidden('alert_message_1', _MD_TODOMOD_ALERT_1));
