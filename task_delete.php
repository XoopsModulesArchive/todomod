<?php

require dirname(__DIR__, 2) . '/mainfile.php';

// モジュールの管理権限がない場合はリダイレクト
if (empty($xoopsModuleConfig['anonpost']) && !is_object($xoopsUser)) {
    isset($_SESSION['previous_page']) ? redirect_header($_SESSION['previous_page'], 0, _NOPERM) : redirect_header('index.php', 0, _NOPERM);

    exit();
}

// モードを_GETから得る
isset($_GET['mode']) ? $mode = $_GET['mode'] : $mode = '';

switch ($mode) {
    case 'trashbox':
        $table = 'todomod_trash_task';
        $modestr = 'mode=trashbox&';
        break;
    case 'return':
        $table = 'todomod_trash_task';
        break;
    default:
        $table = 'todomod_task';
}

$action = 'delete';
if (isset($_POST['task_id'])) {
    $task_id = (int)$_POST['task_id'];

    $action = $_POST['op'];
} elseif (isset($_GET['task_id'])) {
    $task_id = (int)$_GET['task_id'];
}

if ($task_id > 0) {
    // TodomodThreadオブジェクトを取得

    $taskHandler = xoops_getModuleHandler('task');

    $task = $taskHandler->get($task_id, $table);

    if (is_object($task)) {
        switch ($action) {
            // スレッドの削除確認を求めるフォームを表示
            case 'delete':

                require XOOPS_ROOT_PATH . '/header.php';

                switch ($mode) {
                    case 'trashbox':
                        xoops_confirm(
                            ['task_id' => $task_id, 'op' => 'delete_go'],
                            'task_delete.php?' . $modestr . 'task_id=' . $task_id,
                            _MD_TODOMOD_RUSUREDELETE . '<br><br>' . $task->getVar('task_title'),
                            _YES
                        );
                        break;
                    case 'return':
                        move_task($task, 'todomod_trash_task', 'todomod_task', _MD_TODOMOD_RETURN_OK, _MD_TODOMOD_RETURN_NG);
                        break;
                    default:
                        move_task($task, 'todomod_task', 'todomod_trash_task', _MD_TODOMOD_GO_TRASH_OK, _MD_TODOMOD_GO_TRASH_NG);
                        break;
                }

                require XOOPS_ROOT_PATH . '/footer.php';

                break;
            // スレッドを削除する
            case 'delete_go':

                $taskHandler->delete($task) ? $msg = _MD_TODOMOD_DELETEOK : $msg = _MD_TODOMOD_DELETENG;
                isset($_SESSION['previous_page']) ? redirect_header($_SESSION['previous_page'], 0, $msg) : redirect_header('index.php', 0, $msg);

                break;
            default:
                break;
        }
    }
}

function move_task($task, $table_now, $table_new, $msg_OK, $msg_NG)
{
    $taskHandler = xoops_getModuleHandler('task');

    $task_new = $taskHandler->create();

    $task_new->setVar('task_title', $task->getVar('task_title'));

    $task_new->setVar('task_detail', $task->getVar('task_detail'));

    $task_new->setVar('task_deadline', $task->getVar('task_deadline'));

    $task_new->setVar('task_priority', $task->getVar('task_priority'));

    $task_new->setVar('task_status', $task->getVar('task_status'));

    $task_new->setVar('task_poster', $task->getVar('task_poster'));

    if (false !== $taskHandler->insert($task_new, true, $table_new, $task->getVar('task_created')) and $taskHandler->delete($task, $table_now)) {
        $msg = $msg_OK;
    } else {
        $msg = $msg_NG;
    }

    // セッション「previous_page」があった場合、そこに、

    // なければ、「index.php」にリダイレクトする

    isset($_SESSION['previous_page']) ? redirect_header($_SESSION['previous_page'], 2, $msg) : redirect_header('index.php', 2, $msg);
}



