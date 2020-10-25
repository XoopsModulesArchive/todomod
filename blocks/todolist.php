<?php

// 配列$arryに文字列$strがあれば「true」なければ「false」を返す
function match_arry_strings($array, $str)
{
    foreach ($array as $i) {
        if ($i == $str || 999 == $i) {
            return 1;
        }
    }

    return 0;
}

//ブロック表示関数
function b_todolist_show($options)
{
    global $xoopsUser, $xoopsDB;

    // 「優先度」「状況」の表示文字列を配列としてセット

    $priority = [_MB_TODOMOD_PRIORITY_0, _MB_TODOMOD_PRIORITY_1, _MB_TODOMOD_PRIORITY_2, _MB_TODOMOD_PRIORITY_3, _MB_TODOMOD_PRIORITY_4];

    $status = [_MB_TODOMOD_STATUS_0, _MB_TODOMOD_STATUS_1, _MB_TODOMOD_STATUS_2, _MB_TODOMOD_STATUS_3, _MB_TODOMOD_STATUS_4];

    // モジュール設定確認用文字列を配列としてセット

    $show_strings = ['show_block_title', 'show_block_detail', 'show_block_deadline', 'show_block_priority', 'show_block_status', 'show_block_poster', 'show_block_created'];

    // Todomod のモジュールＩＤを得る

    $moduleHandler = xoops_getHandler('module');

    $module = $moduleHandler->getByDirname('todomod');

    $mid = $module->getVar('mid');

    // ユーザーＩＤを得る

    $uid = !empty($xoopsUser) ? $xoopsUser->getVar('uid') : 0;

    // Todomod モジュールにアクセス可能なグループＩＤを得る

    $modulepermHandler = xoops_getHandler('groupperm');

    $read_allowed = $modulepermHandler->getGroupIds('module_read', $mid);

    // そのユーザーが所属しているグループＩＤを得る

    $memberHandler = xoops_getHandler('member');

    $gids = $memberHandler->getGroupsByUser($uid, false);

    // アクセス可能なグループＩＤのなかに、（現在の）グループＩＤがあるかどうか、

    // またはanonimousユーザのアクセスが許可されているか、を調べる

    foreach ($read_allowed as $i) {
        if (3 == $i) {
            $modperm = 1;
        }

        foreach ($gids as $x) {
            if ($x == $i) {
                $modperm = 1;
            }
        }
    }

    // 表示タスク数をDBから得る

    $result = $xoopsDB->query('SELECT conf_value FROM ' . $xoopsDB->prefix('config') . " WHERE  conf_name = 'list_num_block' AND conf_modid = '$mid'");

    $config_arr = $xoopsDB->fetchRow($result);

    $tasklimit = $config_arr[0];

    // 未登録ユーザの編集、投稿が許可されているかどうかをDBから得る

    $result = $xoopsDB->query('SELECT conf_value FROM ' . $xoopsDB->prefix('config') . " WHERE  conf_name = 'anonpost' AND conf_modid = '$mid'");

    $config_arr = $xoopsDB->fetchRow($result);

    $anonpost = $config_arr[0];

    // 取り出すタスクの「基準」オブジェクトと、TodomodTaskオブジェクトのハンドラーを得る

    $criteria = new criteriaCompo();

    $taskHandler = xoops_getModuleHandler('task', 'todomod');    //xoops_getModuleHandler('クラス名','ディレクトリ名')

    // 表示開始タスクを_GETまたは_SESSIONから得る

    if (isset($_GET['taskstart'])) {
        $taskstart = (int)$_GET['taskstart'];
    } elseif (isset($_SESSION['todomod_taskstart_block'])) {
        $taskstart = (int)$_SESSION['todomod_taskstart_block'];
    } else {
        $taskstart = 0;
    }

    // デフォルトは、task_priority と task_status でソートさせる

    $criteria->setSort('task_priority , task_status');

    //ソートの方向を得てセット。

    if (isset($_GET['dire'])) {
        $_SESSION['dire_block'] = $_GET['dire'];
    }

    switch ($_SESSION['dire_block']) {
        case 'down':
            $criteria->setOrder('DESC');
            $deadline_point = '-1';
            break;
        case 'up':
            $criteria->setOrder('');
            $deadline_point = '1';
            break;
    }

    //種類を得てソートをセット。

    if (isset($_GET[sort])) {
        $_SESSION['sort_block'] = $_GET[sort];
    }

    switch ($_SESSION['sort_block']) {
        case 'title':
            $criteria->setSort('task_title');
            break;
        case 'detail':
            $criteria->setSort('task_detail');
            break;
        case 'deadline':  // 期限の値が「0」の場合、昇順で最初に表示されない為の小細工。
            $criteria->setSort(
                'CASE WHEN task_deadline = \'0\' 
     THEN \'' . $deadline_point . '\'
     ELSE \'0\' END , task_deadline'
            );
            break;
        case 'priority':
            $criteria->setSort('task_priority');
            break;
        case 'status':
            $criteria->setSort('task_status');
            break;
        case 'poster':
            $criteria->setSort('task_poster');
            break;
        case 'created':
            $criteria->setSort('task_created');
            break;
    }

    // 全タスク数を得る

    $total = $taskHandler->getCount($criteria, 'todomod_task');

    //全タスクを検査し、task_idがうまく表示されるような、$taskstartを得る。

    $tasks = &$taskHandler->getObjects($criteria, true, 'todomod_task');

    foreach (array_keys($tasks) as $i) {
        if ($i == $_GET['task_id']) {
            $taskstart = $count - ($count % $tasklimit);
        }

        $count += 1;
    }

    if ($taskstart >= $total) {
        $taskstart -= $tasklimit;
    }

    //表示開始タスクをセッションに保存

    $_SESSION['todomod_taskstart_block'] = $taskstart;

    // 取り出すタスクを設定

    $criteria->setStart($taskstart);

    $criteria->setLimit($tasklimit);

    // TodomodTaskオブジェクト郡を取得

    $tasks = &$taskHandler->getObjects($criteria, true);

    // ブロック本体用配列

    $block = [];

    // モジュールのタイトル

    $block['title'] = _MI_TODOMOD_BLOCK_NAME;

    // 書き込み権限の設定

    $block['contents']['noperm'] = ((!$anonpost && !is_object($xoopsUser)) || !$modperm);

    //テーブルの幅をブロック設定の $options から得る

    $th_count = 0;

    foreach ($show_strings as $i) {
        if (match_arry_strings($options, $i)) {
            $th_count++;
        }
    }

    //タイトル列の定義

    $block['contents']['width'] = $th_count;

    $block['contents']['th'] = _MB_TODOMOD_TASKS;

    //ヘッダ列の定義

    $block['contents']['tr_head'][] = [
'show' => match_arry_strings($options, 'show_block_title'),
'align' => 'left',
'width' => $options[0],
'nbsp' => '&nbsp;',
'string' => _TITLE,
'sortlink_up' => $_SERVER['SCRIPT_NAME'] . '?sort=title&dire=up',
'sortlink_down' => $_SERVER['SCRIPT_NAME'] . '?sort=title&dire=down',
    ];

    $block['contents']['tr_head'][] = [
'show' => match_arry_strings($options, 'show_block_detail'),
'align' => 'left',
'width' => $options[1],
'nbsp' => '&nbsp;',
'string' => _MB_TODOMOD_DETAIL,
'sortlink_up' => $_SERVER['SCRIPT_NAME'] . '?sort=detail&dire=up',
'sortlink_down' => $_SERVER['SCRIPT_NAME'] . '?sort=detail&dire=down',
    ];

    $block['contents']['tr_head'][] = [
'show' => match_arry_strings($options, 'show_block_deadline'),
'align' => 'center',
'width' => $options[2],
'string' => _MB_TODOMOD_DEADLINE,
'sortlink_up' => $_SERVER['SCRIPT_NAME'] . '?sort=deadline&dire=up',
'sortlink_down' => $_SERVER['SCRIPT_NAME'] . '?sort=deadline&dire=down',
    ];

    $block['contents']['tr_head'][] = [
'show' => match_arry_strings($options, 'show_block_priority'),
'align' => 'center',
'width' => $options[3],
'string' => _MB_TODOMOD_PRIORITY,
'sortlink_up' => $_SERVER['SCRIPT_NAME'] . '?sort=priority&dire=up',
'sortlink_down' => $_SERVER['SCRIPT_NAME'] . '?sort=priority&dire=down',
    ];

    $block['contents']['tr_head'][] = [
'show' => match_arry_strings($options, 'show_block_status'),
'align' => 'center',
'width' => $options[4],
'string' => _MB_TODOMOD_STATUS,
'sortlink_up' => $_SERVER['SCRIPT_NAME'] . '?sort=status&dire=up',
'sortlink_down' => $_SERVER['SCRIPT_NAME'] . '?sort=status&dire=down',
    ];

    $block['contents']['tr_head'][] = [
'show' => match_arry_strings($options, 'show_block_poster'),
'align' => 'center',
'width' => $options[5],
'string' => _MB_TODOMOD_POSTER,
'sortlink_up' => $_SERVER['SCRIPT_NAME'] . '?sort=poster&dire=up',
'sortlink_down' => $_SERVER['SCRIPT_NAME'] . '?sort=poster&dire=down',
    ];

    $block['contents']['tr_head'][] = [
'show' => match_arry_strings($options, 'show_block_created'),
'align' => 'center',
'width' => $options[6],
'string' => _MB_TODOMOD_CREATED,
'sortlink_up' => $_SERVER['SCRIPT_NAME'] . '?sort=created&dire=up',
'sortlink_down' => $_SERVER['SCRIPT_NAME'] . '?sort=created&dire=down',
    ];

    //タスク列の定義
    foreach (array_keys($tasks) as $i) {  // タスクの数だけ繰り返す
        $block['contents']['tr_main'][$i][] = ['show' => match_arry_strings($options, 'show_block_title'), 'title' => 1, 'align' => 'left', 'valign' => 'top', 'id' => $i, 'string' => $tasks[$i]->getVar('task_title'), 'string2' => _MB_TODOMOD_GO_TRASH];

        $block['contents']['tr_main'][$i][] = ['show' => match_arry_strings($options, 'show_block_detail'), 'align' => 'left', 'valign' => 'top', 'string' => $tasks[$i]->getVar('task_detail')];

        $block['contents']['tr_main'][$i][] = ['show' => match_arry_strings($options, 'show_block_deadline'), 'align' => 'center', 'valign' => 'middle', 'string' => (0 == $tasks[$i]->getVar('task_deadline')) ? null : formatTimestamp($tasks[$i]->getVar('task_deadline'), 's')];

        $block['contents']['tr_main'][$i][] = ['show' => match_arry_strings($options, 'show_block_priority'), 'align' => 'center', 'valign' => 'middle', 'string' => $priority[$tasks[$i]->getVar('task_priority')]];

        $block['contents']['tr_main'][$i][] = ['show' => match_arry_strings($options, 'show_block_status'), 'align' => 'center', 'valign' => 'middle', 'string' => $status[$tasks[$i]->getVar('task_status')]];

        $block['contents']['tr_main'][$i][] = ['show' => match_arry_strings($options, 'show_block_poster'), 'align' => 'center', 'valign' => 'middle', 'string' => $tasks[$i]->getVar('task_poster')];

        $block['contents']['tr_main'][$i][] = ['show' => match_arry_strings($options, 'show_block_created'), 'align' => 'right', 'valign' => 'middle', 'string' => formatTimestamp($tasks[$i]->getVar('task_created'), 'm')];
    }

    // スレッド数が規定数以上の場合はページ・ナビゲーションを表示

    if ($total > $tasklimit) {
        require_once XOOPS_ROOT_PATH . '/class/pagenav.php';

        $pagenav = new XoopsPageNav($total, $tasklimit, $taskstart, 'taskstart', 'tasklimit=' . $tasklimit);

        $block['contents']['pagenav'] = $pagenav->renderNav();
    } else {
        $block['contents']['pagenav'] = '';
    }

    //「新規」リンクの定義

    $block['contents']['new'] = _MB_TODOMOD_NEW;

    //現在のページが「task_edit.php」、「task_delete.php」でなければ、セッション変数に現在のページを代入する

    if ('/task_edit.php' != $_SERVER['PHP_SELF'] && '/task_delete.php' != $_SERVER['PHP_SELF']) {
        $_SESSION['previous_page'] = $_SERVER['PHP_SELF'];
    }

    return $block;
}

// ブロック編集関数
function b_todolist_edit($options)
{
    // 各項目の表示文字列を配列にセット

    $tr_head = [_TITLE, _MB_TODOMOD_DETAIL, _MB_TODOMOD_DEADLINE, _MB_TODOMOD_PRIORITY, _MB_TODOMOD_STATUS, _MB_TODOMOD_POSTER, _MB_TODOMOD_CREATED];

    // 各表示項目の値を配列としてセット

    $block_strings = [
        'show_block_title',
        'show_block_detail',
        'show_block_deadline',
        'show_block_priority',
        'show_block_status',
        'show_block_poster',
        'show_block_created',
    ];

    //各項目の横幅設定

    $form = '<table><tr><td align="center" colspan="2" >' . _MB_TODOMOD_WIDTH . '</td></tr>';

    foreach ($tr_head as $i => $x) {
        $form .= '<tr>' . '<td align="right" >' . $x . '</td>' . '<td><input type="text" size="3" name="options[]" value="' . $options[$i] . '">&nbsp;' . _MB_TODOMOD_PIXEL . '</td>' . '</tr>';
    }

    //表示項目の設定

    $form .= '<tr><td  align="center" colspan="2" >' . _MB_TODOMOD_ITEM . '<br><select name="options[]" size=7 multiple>';

    foreach ($tr_head as $i => $x) {
        $form .= '<option value="' . $block_strings[$i] . '" ' . (match_arry_strings($options, $block_strings[$i]) ? 'selected' : '') . '>' . $x;
    }

    $form .= '</select><td>' . _MB_TODOMOD_ATTENTION . '</tr></table>';

    return $form;
}
