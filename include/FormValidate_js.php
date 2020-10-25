<script type="text/javascript">
    <!--
    function xoopsFormValidate_todomod_iform() {  // フォームチェック用関数

// ポップアップアラート用メッセージの為の準備
        var alert_message_0 = xoopsGetElementById("alert_message_0"); //「el」は、「alert_message_0」のIDをもったHTMLエレメント
        var alert_message_1 = xoopsGetElementById("alert_message_1"); //「el」は、「alert_message_1」のIDをもったHTMLエレメント

        if (window.document.todomod_iform.task_title.value == "") // 題名が入力さてれていなかったら...
        {
            alert(alert_message_0.value);
            window.document.todomod_iform.task_title.focus();
            return false;
        } //ポップアップアラート発生。

        var el = xoopsGetElementById("task_deadline"); //「el」は、「task_deadline」のIDをもったHTMLエレメント
        str = el.value;
        deadline = str.preg_split("-"); // 期限の入力値を年、月、日に分ける。
        MonthTbl = new Array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31); // 各月の最後の日付

// うるう年だったら...
        if (((parseInt(deadline[0]) % 4) == 0 && (parseInt(deadline[0]) % 100) != 0) || (parseInt(deadline[0]) % 400) == 0)
            MonthTbl[1] = 29; // 2月を29日にする。

        if (str == "") {
            return true;
        } // 期限が入力されていない場合はＯＫ！

// parseInt()の為に頭の余分な「0」を取り除く
        if (!(isNaN(deadline[0]) && isNaN(deadline[1]) && isNaN(deadline[2]))) { // 数値に3分割されていれば、、
            deadline[0] = deadline[0].substring(deadline[0].search(/[1-9]/)); // 初めの0を取り除いて、、
            deadline[1] = deadline[1].substring(deadline[1].search(/[1-9]/));
            deadline[2] = deadline[2].substring(deadline[2].search(/[1-9]/));
            deadline_tmp = deadline[0] + "-" + deadline[1] + "-" + deadline[2]; // エレメントの値を再構成し代入
        }

        if (parseInt(deadline[0]) < 2000 || parseInt(deadline[0]) > 2015 || isNaN(deadline[0]) ||  // 期限の入力値が不正だったら...
            parseInt(deadline[1]) < 1 || parseInt(deadline[1]) > 12 || isNaN(deadline[1]) ||
            parseInt(deadline[2]) < 1 || parseInt(deadline[2]) > MonthTbl[parseInt(deadline[1]) - 1] || isNaN(deadline[2])) {
            alert(alert_message_1.value);
            window.document.todomod_iform.task_title.focus();
            return false;
        } //ポップアップアラート発生。

// 月、日、が一桁だった場合、「0」を付け加える
        deadline = deadline_tmp.preg_split("-");
        if (deadline[1].length < 2) {
            deadline[1] = "0" + deadline[1];
        }
        if (deadline[2].length < 2) {
            deadline[2] = "0" + deadline[2];
        }
        el.value = deadline[0] + "-" + deadline[1] + "-" + deadline[2];

    }

    //-->
</script>
