<?php

require dirname(__DIR__, 2) . '/mainfile.php';

// スレッドを編集できるユーザを限定
if (empty($xoopsModuleConfig['anonpost']) && !is_object($xoopsUser)) {
    isset($_SESSION['previous_page']) ? redirect_header($_SESSION['previous_page'], 0, _NOPERM) : redirect_header('index.php', 0, _NOPERM);

    exit();
}

// モードを_GETから得る
isset($_GET['mode']) ? $mode = $_GET['mode'] : $mode = '';

if ('trashbox' == $mode) {
    $table = 'todomod_trash_task';
} else {
    $table = 'todomod_task';
}

//タスクIDのセット
$task_id = isset($_GET['task_id']) ? (int)$_GET['task_id'] : 0;

// TodomodTask オブジェクトを取得
$taskHandler = xoops_getModuleHandler('task');
$task        = $taskHandler->get($task_id, $table);

// ヘッダーの読み込み
require XOOPS_ROOT_PATH . '/header.php';

// タスクIDが指定されていれば、フォームの値になる各変数にDBからの数字を入力
if (0 != $task_id) {
    $task_title = $task->getVar('task_title', 'e'); //「題名」
    $task_detail = $task->getVar('task_detail', 'e'); //「詳細」
    //「期限」
    if (0 != $task->getVar('task_deadline')) {  // DBの「期限」が「0」でなければ、
        $task_deadline = formatTimestamp($task->getVar('task_deadline'), 's'); //「期限」の値を整形してセット
        // 月、日、が一桁だった場合、「0」を付け加える
        $task_deadline_array = preg_split('-', $task_deadline);

        if (1 == mb_strlen($task_deadline_array[1])) {
            $task_deadline_array[1] = '0' . $task_deadline_array[1];
        }

        if (1 == mb_strlen($task_deadline_array[2])) {
            $task_deadline_array[2] = '0' . $task_deadline_array[2];
        }

        $task_deadline = $task_deadline_array[0] . '-' . $task_deadline_array[1] . '-' . $task_deadline_array[2];
    } else {
        $task_deadline = null;
    }

    $task_priority = $task->getVar('task_priority'); //「優先度」
    $task_status = $task->getVar('task_status'); //「状況」
} else {
    $task_priority = 2;
} // タスクIDが指定されていない場合、「優先度」を「普通」に設定

// 実際に表示する
require __DIR__ . '/include/task_form.php';
$task_form->display();

//　フッターの読み込み
require_once XOOPS_ROOT_PATH . '/footer.php';

//「期限」の設定に使うカレンダー表示に必要なファイルの読み込み
require_once XOOPS_ROOT_PATH . '/include/calendarjs.php';

//フォームチェック用JavaScriptファイルの読み込み
require_once __DIR__ . '/include/FormValidate_js.php';


