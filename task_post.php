<?php

// スレッドを編集できるユーザを限定
require dirname(__DIR__, 2) . '/mainfile.php';
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

$action = '';

if (isset($_POST)) {
    // POSTメッソッドで渡されたフォームの各項目を各変数に代入

    foreach ($_POST as $k => $v) {
        ${$k} = $v;
    }

    if (isset($submitpost)) {
        if (!empty($_POST['task_id']) && (int)$_POST['task_id'] > 0) {
            $action = 'update';
        } else {
            $action = 'insert';
        }
    }
}

switch ($action) {
    // スレッドの新規投稿
    case 'insert':

        // TodomodTask オブジェクトの新規作成
        $taskHandler = xoops_getModuleHandler('task');
        $task = $taskHandler->create();

        // フォームの各項目を各テーブルにセット
        $task->setVar('task_title', $task_title);
        $task->setVar('task_detail', $task_detail);
        $task->setVar('task_deadline', ('' != $task_deadline) ? strtotime($task_deadline) : 0);
        $task->setVar('task_priority', $task_priority);
        $task->setVar('task_status', $task_status);
        if (is_object($xoopsUser)) {                       // ログインしていれば「投稿者」に、
            $task->setVar('task_poster', $xoopsUser->getVar('uname'));
        } // 現在のユーザー名をセット、
        else {
            $task->setVar('task_poster', $xoopsConfig['anonymous']);
        }  // していなければ、「ゲスト」をセット

        // データベース・テーブルに登録
        if (false !== $taskHandler->insert($task, false, $table)) {
            // セッション「previous_page」があった場合、そこに、

            // なければ、「index.php」にリダイレクトする

            if (isset($_SESSION['previous_page'])) {
                if (mb_strpos($_SESSION['previous_page'], '?')) {
                    $str = '&task_id=';
                } else {
                    $str = '?task_id=';
                }

                redirect_header($_SESSION['previous_page'] . $str . $task_id, 2, _MD_TODOMOD_THANKSPOST);
            } else {
                redirect_header('index.php?task_id=' . $task_id, 2, _MD_TODOMOD_THANKSPOST);
            }
        } else {
            require XOOPS_ROOT_PATH . '/header.php';

            xoops_error($task->getHtmlErrors());

            require XOOPS_ROOT_PATH . '/footer.php';
        }
        break;
    // スレッドデータの更新
    case 'update':

        // TodomodTaskオブジェクトを取得
        $taskHandler = xoops_getModuleHandler('task');
        $task        = $taskHandler->get($task_id, $table);

        // フォームの各項目を各テーブルにセット
        $task->setVar('task_title', $task_title);
        $task->setVar('task_detail', $task_detail);
        $task->setVar('task_deadline', ('' != $task_deadline) ? strtotime($task_deadline) : 0);
        $task->setVar('task_priority', $task_priority);
        $task->setVar('task_status', $task_status);

        // データベーステーブルのデータを更新
        if (false !== $taskHandler->insert($task, false, $table)) { // INSERTが成功した場合、、
            // セッション「previous_page」があった場合、そこに、
            // なければ、「index.php」にリダイレクトする
            if (isset($_SESSION['previous_page'])) {
                if (mb_strpos($_SESSION['previous_page'], '?')) {
                    $str = '&task_id=';
                } else {
                    $str = '?task_id=';
                }

                redirect_header($_SESSION['previous_page'] . $str . $task_id, 2, _MD_TODOMOD_THANKSPOST);
            } else {
                redirect_header('index.php?task_id=' . $task_id, 2, _MD_TODOMOD_THANKSPOST);
            }
        } else {
            require XOOPS_ROOT_PATH . '/header.php';

            xoops_error($task->getHtmlErrors());

            require XOOPS_ROOT_PATH . '/footer.php';
        }

        break;
    default:
        break;
}
