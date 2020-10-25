<?php

require dirname(__DIR__, 2) . '/mainfile.php';

// 「優先度」「状況」の表示文字列を配列としてセット
$priority = [_MD_TODOMOD_PRIORITY_0, _MD_TODOMOD_PRIORITY_1, _MD_TODOMOD_PRIORITY_2, _MD_TODOMOD_PRIORITY_3, _MD_TODOMOD_PRIORITY_4];
$status = [_MD_TODOMOD_STATUS_0, _MD_TODOMOD_STATUS_1, _MD_TODOMOD_STATUS_2, _MD_TODOMOD_STATUS_3, _MD_TODOMOD_STATUS_4];

// セッション previous_page に現在のファイル名を保存する
$_SESSION['previous_page'] = $_SERVER['PHP_SELF'];

// モードをGETから得る
isset($_GET['mode']) ? $mode = $_GET['mode'] : $mode = 'default';

// 表示タスク数をモジュール設定から得る
$tasklimit = isset($xoopsModuleConfig['list_num_' . $mode]) ? (int)$xoopsModuleConfig['list_num_' . $mode] : 10;

// 表示開始タスクを_GETまたは_SESSIONから得る
if (isset($_GET['taskstart'])) {
    $taskstart = (int)$_GET['taskstart'];
} elseif (isset($_SESSION['todomod_taskstart_' . $mode])) {
    $taskstart = (int)$_SESSION['todomod_taskstart_' . $mode];
} else {
    $taskstart = 0;
}

if ('trashbox' == $mode) {                         //モードが「ごみ箱」だったら、
    $table = 'todomod_trash_task';

    $_SESSION['previous_page'] .= '?mode=trashbox';

    $lang_tasks = _MD_TODOMOD_TRASHS;

    $deletestr = _DELETE;

    $modestr = 'mode=trashbox&';
} else {                                            //モードが「通常」だったら、
    $table = 'todomod_task';

    $lang_tasks = _MD_TODOMOD_TASKS;

    $deletestr = _MD_TODOMOD_GO_TRASH;
}

// ヘッダーの読み込み
require_once XOOPS_ROOT_PATH . '/header.php';

// 取り出すタスクの「基準」オブジェクトと、TodomodTaskオブジェクトのハンドラーを得る
$criteria = new criteriaCompo();
$taskHandler = xoops_getModuleHandler('task');

// デフォルトのソートを設定。
if ('trashbox' == $mode) {
    $criteria->setSort('task_created');
} else {
    $criteria->setSort('task_priority , task_status');
}

//ソートの方向を得てセット。
if (isset($_GET['dire'])) {
    $_SESSION['dire_' . $mode] = $_GET['dire'];
}
switch ($_SESSION['dire_' . $mode]) {
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
    $_SESSION['sort_' . $mode] = $_GET[sort];
}
switch ($_SESSION['sort_' . $mode]) {
    case 'title':
        $criteria->setSort('task_title');
        break;
    case 'detail':
        $criteria->setSort('task_detail');
        break;
    case 'deadline':
        $criteria->setSort(
            'CASE WHEN task_deadline = \'0\'　// 期限の値が「0」の場合、
    THEN \'' . $deadline_point . '\'                       // 昇順で最初に表示されない為の
    ELSE \'0\' END , task_deadline'
        );                  // 小細工。
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
$total = $taskHandler->getCount($criteria, $table);

//全タスクを検査し、task_idがうまく表示されるような、$taskstartを得る。
$tasks = &$taskHandler->getObjects($criteria, true, $table);
foreach (array_keys($tasks) as $i) {
    if ($i == $_GET['task_id']) {
        $taskstart = $count - ($count % $tasklimit);
    }

    $count += 1;
}
if ($taskstart >= $total) {
    $taskstart -= $tasklimit;
}

// 表示開始タスクをセッションに保存
$_SESSION['todomod_taskstart_' . $mode] = $taskstart;

// 取り出すタスクの範囲を設定
$criteria->setStart($taskstart);
$criteria->setLimit($tasklimit);

// TodomodTaskオブジェクト郡を取得
$tasks = &$taskHandler->getObjects($criteria, true, $table);

// Todomod のモジュールＩＤを得る
$moduleHandler = xoops_getHandler('module');
$module = $moduleHandler->getByDirname('todomod');
$mid = $module->getVar('mid');

// Todomod モジュールにアクセス可能なグループＩＤを得る
$modulepermHandler = xoops_getHandler('groupperm');
$read_allowed      = $modulepermHandler->getGroupIds('module_read', $mid);

// ユーザーＩＤを得る
$uid = !empty($xoopsUser) ? $xoopsUser->getVar('uid') : 0;

// そのユーザーが所属しているグループＩＤを得る
$memberHandler = xoops_getHandler('member');
$gids = $memberHandler->getGroupsByUser($uid, false);

// アクセス可能なグループＩＤのなかに、（現在の）グループＩＤがあるか、
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

// 使用するテンプレートファイルを指定
$GLOBALS['xoopsOption']['template_main'] = 'todomod_index.html';

//書き込み権限の設定
$xoopsTpl->append('noperm', ((!$xoopsModuleConfig['anonpost'] && !is_object($xoopsUser)) || !$modperm));

foreach (array_keys($tasks) as $i) {  // タスクの数だけ繰り返す
    if ('trashbox' == $mode) {
        $returnlinkstr = '[<a href="task_delete.php?mode=return&' . 'task_id=' . $i . '">' . _MD_TODOMOD_RETURN . '</a>]&nbsp';
    } else {
        $returnlinkstr = '';
    }

    // スレッドの内容をテンプレートのタグに割り当てる

    $xoopsTpl->append(
        'tasks',
        [
            'title' => $tasks[$i]->getVar('task_title'),
'link' => 'task_edit.php?' . $modestr . 'task_id=' . $i,
'returnlink' => $returnlinkstr,
'deletelink' => '[<a href="task_delete.php?' . $modestr . 'task_id=' . $i . '">' . $deletestr . '</a>]',
'detail' => $tasks[$i]->getVar('task_detail'),
'deadline' => 0 == $tasks[$i]->getVar('task_deadline') ? null : formatTimestamp($tasks[$i]->getVar('task_deadline'), 's'),
'poster' => $tasks[$i]->getVar('task_poster'),
'priority' => $priority[$tasks[$i]->getVar('task_priority')],
'status' => $status[$tasks[$i]->getVar('task_status')],
'created' => formatTimestamp($tasks[$i]->getVar('task_created'), 'm'),
        ]
    );
}

// スレッド数が規定数以上の場合はページ・ナビゲーションを表示
if ($total > $tasklimit) {
    require_once XOOPS_ROOT_PATH . '/class/pagenav.php';

    $pagenav = new XoopsPageNav($total, $tasklimit, $taskstart, 'taskstart', $modestr . 'tasklimit=' . $tasklimit);

    $xoopsTpl->assign('pagenav', $pagenav->renderNav());
} else {
    $xoopsTpl->assign('pagenav', '');
}

// 言語文字列をテンプレートのタグに割り当てる
$xoopsTpl->assign(
    [
        'lang_tasks' => $lang_tasks,
'lang_title' => _TITLE,
'lang_detail' => _MD_TODOMOD_DETAIL,
'lang_deadline' => _MD_TODOMOD_DEADLINE,
'lang_poster' => _MD_TODOMOD_POSTER,
'lang_priority' => _MD_TODOMOD_PRIORITY,
'lang_status' => _MD_TODOMOD_STATUS,
'lang_created' => _MD_TODOMOD_CREATED,

        'sortlink_title_down' => 'index.php?' . $modestr . 'sort=title&dire=down',
'sortlink_title_up' => 'index.php?' . $modestr . 'sort=title&dire=up',
'sortlink_detail_down' => 'index.php?' . $modestr . 'sort=detail&dire=down',
'sortlink_detail_up' => 'index.php?' . $modestr . 'sort=detail&dire=up',
'sortlink_deadline_down' => 'index.php?' . $modestr . 'sort=deadline&dire=down',
'sortlink_deadline_up' => 'index.php?' . $modestr . 'sort=deadline&dire=up',
'sortlink_priority_down' => 'index.php?' . $modestr . 'sort=priority&dire=down',
'sortlink_priority_up' => 'index.php?' . $modestr . 'sort=priority&dire=up',
'sortlink_status_down' => 'index.php?' . $modestr . 'sort=status&dire=down',
'sortlink_status_up' => 'index.php?' . $modestr . 'sort=status&dire=up',
'sortlink_poster_down' => 'index.php?' . $modestr . 'sort=poster&dire=down',
'sortlink_poster_up' => 'index.php?' . $modestr . 'sort=poster&dire=up',
'sortlink_created_down' => 'index.php?' . $modestr . 'sort=created&dire=down',
'sortlink_created_up' => 'index.php?' . $modestr . 'sort=created&dire=up',
    ]
);

//スレッド投稿用フォームの表示
if ($xoopsModuleConfig['anonpost'] || is_object($xoopsUser)) {    // ログインしているか、
    // 未登録ユーザの投稿を許可している場合
    $task_id = 0;

    $task_title = '';

    $task_detail = '';

    $task_priority = 2;

    $task_status = 0;

    // スレッド投稿用フォームを読み込んでテンプレートのタグに割り当てる

    require __DIR__ . '/include/task_form.php';

    $xoopsTpl->assign('task_form', $task_form->render());
}

//　フッターの読み込み
require_once XOOPS_ROOT_PATH . '/footer.php';

//「期限」の設定に使うカレンダー表示に必要なファイルの読み込み。
require_once XOOPS_ROOT_PATH . '/include/calendarjs.php';

//フォームチェック用JavaScriptファイルの読み込み。
require_once __DIR__ . '/include/FormValidate_js.php';
