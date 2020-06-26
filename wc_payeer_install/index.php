<?php
/**
 * http://new-dev.ru/
 * author GoldSoft <newdevexpert@gmail.com>
 * Copyright (c) New-Dev.ru
 */

header('Content-Type: text/html; charset=utf-8');

define('ROOT_DIR', dirname(dirname(__FILE__)));
define('ENGINE_DIR', ROOT_DIR.'/engine');
define('DATALIFEENGINE', true);


require_once ENGINE_DIR.'/modules/webcash/site/includes/init_dle.php';
require_once ROOT_DIR.'/wc_payeer_install/functions.php';
require_once ENGINE_DIR.'/modules/webcash/init.php';
require_once ENGINE_DIR.'/modules/webcash/admin/lib/sql_parse.php';
require_once ENGINE_DIR.'/modules/webcash/admin/lib/xcopy/xcopy.php';

echo_top_html();

$mode = empty($_GET['mode']) ? 'install' : $_GET['mode'];

if (!in_array($mode, array('install', 'uninstall', 'readme'))) exit('error mode');

require_once ROOT_DIR.'/wc_payeer_install/mode/'.$mode.'.php';

echo_bottom_html();