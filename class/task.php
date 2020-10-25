<?php

class TodomodTask extends XoopsObject
{
    public function __construct()
    {
        $this->XoopsObject();

        // XoopsObjectクラスにて宣言されている$varsプロパティの各パラメータを初期化する

        $this->initVar('task_id', XOBJ_DTYPE_INT, null, false);

        $this->initVar('task_deadline', XOBJ_DTYPE_TXTBOX, null, false, 10 . false);

        $this->initVar('task_created', XOBJ_DTYPE_INT, null, false);

        $this->initVar('task_poster', XOBJ_DTYPE_TXTBOX, null, false, 255, true);

        $this->initVar('task_title', XOBJ_DTYPE_TXTBOX, null, true, 255, true);

        $this->initVar('task_detail', XOBJ_DTYPE_TXTAREA, null, false, null, true);

        $this->initVar('task_priority', XOBJ_DTYPE_INT, 0, true);

        $this->initVar('task_status', XOBJ_DTYPE_INT, 0, true);
    }
}

class TodomodTaskHandler extends XoopsObjectHandler
{
    // 新規にTodomodTaskオブジェクトを生成

    public function &create($isNew = true)
    {
        $task = new TodomodTask();

        if ($isNew) {
            $task->setNew();
        }

        return $task;
    }

    // TodomodTaskオブジェクトを取得

    public function get($id, $table = 'todomod_task')
    {
        $id = (int)$id;

        if ($id > 0) {
            $sql = 'SELECT * FROM ' . $this->db->prefix($table) . ' WHERE task_id=' . $id;

            if (!$result = $this->db->query($sql)) {
                return false;
            }

            $numrows = $this->db->getRowsNum($result);

            if (1 == $numrows) {
                $task = new TodomodTask();

                $task->assignVars($this->db->fetchArray($result));

                return $task;
            }
        }

        return false;
    }

    // TodomodTaskオブジェクトをテーブルに登録または更新

    public function insert(XoopsObject $task, $force, $table, $time)
    {
        if (!isset($time)) {
            $time = time();
        }

        if ('todomodtask' != get_class($task)) {
            return false;
        }

        if (!$task->isDirty()) {
            return true;
        }

        if (!$task->cleanVars()) {
            return false;
        }

        foreach ($task->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        if ($task->isNew()) {
            global $task_id;

            $task_id = $this->db->genId($table . '_task_id_seq');

            $sql = sprintf(
                'INSERT INTO %s ( task_title, task_deadline, task_detail, task_poster , task_priority , task_status , task_created ) VALUES ( %s, %s, %s ,%s ,%u ,%u ,%u)',
                $this->db->prefix($table),
                $this->db->quoteString($task_title),
                $this->db->quoteString($task_deadline),
                $this->db->quoteString($task_detail),
                $this->db->quoteString($task_poster),
                $task_priority,
                $task_status,
                $time
            );
        } else {
            $sql = sprintf(
                'UPDATE %s SET task_title = %s, task_deadline = %s, task_detail = %s, task_priority = %u, task_status = %u  WHERE task_id = %u',
                $this->db->prefix($table),
                $this->db->quoteString($task_title),
                $this->db->quoteString($task_deadline),
                $this->db->quoteString($task_detail),
                $task_priority,
                $task_status,
                $task_id
            );
        }

        if (!$result = $this->db->queryF($sql)) {
            return false;
        }

        if (empty($task_id)) {
            $task_id = $this->db->getInsertId();
        }

        return true;
    }

    // TodomodTaskオブジェクトを削除

    public function delete(XoopsObject $task, $table = 'todomod_trash_task')
    {
        if ('todomodtask' != get_class($task)) {
            return false;
        }

        $sql = sprintf('DELETE FROM %s WHERE task_id = %u', $this->db->prefix($table), $task->getVar('task_id'));

        if (!$result = $this->db->queryF($sql)) {
            return false;
        }

        return true;
    }

    // $criteriaにマッチする複数のTodomodTaskオブジェクトを取得

    public function &getObjects($criteria = null, $id_as_key = false, $table = 'todomod_task')
    {
        $ret = [];

        $limit = $start = 0;

        $sql = 'SELECT * FROM ' . $this->db->prefix($table);

        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' ' . $criteria->renderWhere();

            $sort = ('' != $criteria->getSort()) ? $criteria->getSort() : 'task_id';

            $sql .= ' ORDER BY ' . $sort . ' ' . $criteria->getOrder();

            $limit = $criteria->getLimit();

            $start = $criteria->getStart();
        }

        $result = $this->db->query($sql, $limit, $start);

        if (!$result) {
            return $ret;
        }

        while (false !== ($myrow = $this->db->fetchArray($result))) {
            $task = new Todomodtask();

            $task->assignVars($myrow);

            if (!$id_as_key) {
                $ret[] = &$task;
            } else {
                $ret[$myrow['task_id']] = &$task;
            }

            unset($task);
        }

        return $ret;
    }

    // $criteriaにマッチするTodomodTaskオブジェクトの数をカウントする

    public function getCount($criteria = null, $table = 'todomod_task')
    {
        $sql = 'SELECT COUNT(*) FROM ' . $this->db->prefix($table);

        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' ' . $criteria->renderWhere();
        }

        if (!$result = $this->db->query($sql)) {
            return 0;
        }

        [$count] = $this->db->fetchRow($result);

        return $count;
    }
}
