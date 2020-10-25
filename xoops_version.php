<?php

// モジュール基本情報
$modversion['name'] = _MI_TODOMOD_NAME; // モジュール名称
$modversion['version'] = 1.3; // バージョン
$modversion['description'] = _MI_TODOMOD_DESC; // モジュールの説明
$modversion['credits'] = 'Keiichi Maeda (keiichi@cameo.plala.or.jp)'; // モジュールの作成者
$modversion['help'] = 'todomod.html'; // ヘルプファイル（未使用）
$modversion['license'] = 'GPL see LICENSE'; // ライセンス
$modversion['official'] = 0; // 公式モジュール（0＝非公式　1＝公式）
$modversion['image'] = 'images/todomod_icon.png'; // 画像アイコン
$modversion['dirname'] = 'todomod'; // 使用ディレクトリ名

// SQL定義ファイル
$modversion['sqlfile']['mysql'] = 'sql/mysql.sql';
// テーブル名
$modversion['tables'][0] = 'todomod_task';
$modversion['tables'][1] = 'todomod_trash_task';
// モジュール専用管理画面（あり＝1　なし＝0）
$modversion['hasAdmin'] = 1;

// テンプレートファイル
$modversion['templates'][1]['file'] = 'todomod_index.html';
$modversion['templates'][1]['description'] = '';

// メインコンテンツ（あり＝1　なし＝0）
$modversion['hasMain'] = 1;

$modversion['sub'][1]['name'] = _MI_TODOMOD_VIEWTRASHBOX;
$modversion['sub'][1]['url'] = 'index.php?mode=trashbox';

$modversion['adminindex'] = 'admin/index.php';

// 検索機能（あり＝1　なし＝0）
$modversion['hasSearch'] = 0;

// モジュール専用設定オプション

//タスク一覧に表示するタスクの数
$modversion['config'][1]['name'] = 'list_num_default'; // パラメータ名
$modversion['config'][1]['title'] = '_MI_TODOMOD_LISTNUM_DEFAULT'; // 名称
$modversion['config'][1]['description'] = ''; // 説明
$modversion['config'][1]['formtype'] = 'select'; // フォーム形式
$modversion['config'][1]['valuetype'] = 'int'; // データ型
$modversion['config'][1]['default'] = 10; // デフォルト値
$modversion['config'][1]['options'] = ['3' => 3, '5' => 5, '8' => 8, '10' => 10, '15' => 15, '20' => 20, '30' => 30]; // 選択可能オプション

//タスク一覧(ごみ箱)に表示するタスクの数
$modversion['config'][2]['name'] = 'list_num_trashbox'; // パラメータ名
$modversion['config'][2]['title'] = '_MI_TODOMOD_LISTNUM_TRASHBOX'; // 名称
$modversion['config'][2]['description'] = ''; // 説明
$modversion['config'][2]['formtype'] = 'select'; // フォーム形式
$modversion['config'][2]['valuetype'] = 'int'; // データ型
$modversion['config'][2]['default'] = 10; // デフォルト値
$modversion['config'][2]['options'] = ['3' => 3, '5' => 5, '8' => 8, '10' => 10, '15' => 15, '20' => 20, '30' => 30]; // 選択可能オプション

//タスク一覧(ごみ箱)に表示するタスクの数
$modversion['config'][3]['name'] = 'list_num_block'; // パラメータ名
$modversion['config'][3]['title'] = '_MI_TODOMOD_LISTNUM_BLOCK'; // 名称
$modversion['config'][3]['description'] = ''; // 説明
$modversion['config'][3]['formtype'] = 'select'; // フォーム形式
$modversion['config'][3]['valuetype'] = 'int'; // データ型
$modversion['config'][3]['default'] = 10; // デフォルト値
$modversion['config'][3]['options'] = ['3' => 3, '5' => 5, '8' => 8, '10' => 10, '15' => 15, '20' => 20, '30' => 30]; // 選択可能オプション

//未登録ユーザによる投稿、編集、削除を許可する
$modversion['config'][4]['name'] = 'anonpost'; // パラメータ名
$modversion['config'][4]['title'] = '_MI_TODOMOD_ANONPOST'; // 名称
$modversion['config'][4]['description'] = ''; // 説明
$modversion['config'][4]['formtype'] = 'yesno'; // フォーム形式
$modversion['config'][4]['valuetype'] = 'int'; // データ型
$modversion['config'][4]['default'] = 0; // デフォルト値

//タイトルフォームの横幅
$modversion['config'][5]['name'] = 'title_width'; // パラメータ名
$modversion['config'][5]['title'] = '_MI_TODOMOD_TITLE_WIDTH'; // 名称
$modversion['config'][5]['description'] = ''; // 説明
$modversion['config'][5]['formtype'] = 'textbox'; // フォーム形式
$modversion['config'][5]['valuetype'] = 'int'; // データ型
$modversion['config'][5]['default'] = 78; // デフォルト値

//詳細フォームの横幅
$modversion['config'][6]['name'] = 'detail_width'; // パラメータ名
$modversion['config'][6]['title'] = '_MI_TODOMOD_DETAIL_WIDTH'; // 名称
$modversion['config'][6]['description'] = ''; // 説明
$modversion['config'][6]['formtype'] = 'textbox'; // フォーム形式
$modversion['config'][6]['valuetype'] = 'int'; // データ型
$modversion['config'][6]['default'] = 67; // デフォルト値

//詳細フォームの縦幅
$modversion['config'][7]['name'] = 'detail_length'; // パラメータ名
$modversion['config'][7]['title'] = '_MI_TODOMOD_DETAIL_LENGTH'; // 名称
$modversion['config'][7]['description'] = ''; // 説明
$modversion['config'][7]['formtype'] = 'textbox'; // フォーム形式
$modversion['config'][7]['valuetype'] = 'int'; // データ型
$modversion['config'][7]['default'] = 10; // デフォルト値

//ブロックの定義
$modversion['blocks'][8]['file'] = 'todolist.php';
$modversion['blocks'][8]['name'] = _MI_TODOMOD_BLOCK_NAME;
$modversion['blocks'][8]['description'] = _MI_TODOMOD_BLOCK_DESC;
$modversion['blocks'][8]['show_func'] = 'b_todolist_show';
$modversion['blocks'][8]['edit_func'] = 'b_todolist_edit';
$modversion['blocks'][8]['options'] = '|||||||show_block_title|show_block_deadline|show_block_priority|show_block_status';
$modversion['blocks'][8]['template'] = 'todomod_block_index.html';
