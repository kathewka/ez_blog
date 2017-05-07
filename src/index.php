<?php
require_once dirname(__FILE__) . '/classes/Entry.php';

// エントリを読み込み
$entries = Entry::getEntries();
// テンプレートに反映
chdir(dirname(__FILE__) . '/template');
require_once dirname(__FILE__) . '/template/template.php';


echo '<pre>';
print_r($entries);
echo '</pre>';
