<?php
/**
 * http://new-dev.ru/
 * author GoldSoft <newdevexpert@gmail.com>
 * Copyright (c) New-Dev.ru
 */

use WebCash\Admin_Panel;

defined('DATALIFEENGINE') or exit('Access Denied');
?>

<h2>Автоматическая установка шлюза</h2>

<?php
if (!file_exists(ENGINE_DIR.'/data/config.webcash.php')) {
	echo '<p class="message information">Ошибка! &laquo;/engine/data/config.webcash.php&raquo; отсутствует. Возможно, модуль не установлен.</p>';
	echo_bottom_html();
	exit;
}

//проверка на права доступа
$arr = array(
	ENGINE_DIR.'/data/',
	ENGINE_DIR.'/modules/webcash/gateways/',
	ROOT_DIR.'/templates/',
	ROOT_DIR.'/templates/'.$config['skin'].'/',
);

$error = false;
clearstatcache();

foreach ($arr as $fname) {
	if (!is_writable($fname)) {
		$error = true;

		if (is_dir($fname))
			echo '<p class="message information">Ошибка! Каталог &laquo;'.$fname.'&raquo; недоступен для записи.</p>';
		elseif (is_file($fname))
			echo '<p class="message information">Ошибка! Файл &laquo;'.$fname.'&raquo; недоступен для записи.</p>';
	}
}

if ($error) {
	echo '<p>Возникли ошибки при установке, рекомендуется устранить проблемы с правами доступа и заново обновить эту страницу для продолжения установки.<br /><br /> Если возникли проблемы на выделенном сервере - скорее всего связано с правами доступа, когда не совпадают владельцы и группы скриптов и фтп. На время тестирования, можно установить у этих папок и файлов права 777 (это временное решение), чтобы убедиться в причине проблемы. Потом все же рекомендуется разобраться, почему возникла такая ситуация. Часто помогает команда <a href="http://ru.wikipedia.org/wiki/Chown" target="_blank" title="Просмотреть информацию по команде, ссылка откроется в новом окне">chown</a>, можно показать эту часть текста хостеру или администратору, обслуживающему ваш выделенный сервер - они сразу поймут в чем дело.<br /><br /> В крайнем случае, вы можете прибегнуть к ручной установке, процесс который входит в инструкцию по модулю. <a href="http://'.$_SERVER['HTTP_HOST'].'/wc_payeer_install/index.php?do=readme">Перейти к чтению инструкции.</a></p>';
	echo_bottom_html();
	exit;
}
//проверка на права доступа


echo '<ul>';

copy_folder('files', './..', false, false);
echo '<li>Скопированы файлы шлюза</li>';


if ($config['skin'] != 'Default') {
	$path = ROOT_DIR.'/wc_payeer_install/files/templates/Default/';
	if (file_exists($path)) {
		if ($folders = glob($path.'*')) {
			foreach ($folders as $folder) {
				$newfolder = str_replace($path, ROOT_DIR.'/templates/'.$config['skin'].'/', $folder);
				copy_folder($folder, $newfolder);
				if (file_exists($newfolder))
					echo '<li>Из папки дефолтного шаблона в текущий скопирована папка &laquo;'.str_replace(ROOT_DIR, '', $newfolder).'&raquo;</li>';
			}
		}
	}
}



$fname = ROOT_DIR.'/wc_payeer_install/sql/install.sql';

if (file_exists($fname)) {
	$sql_query = fread(fopen($fname, 'r'), filesize($fname)) or exit('Error ['.$fname.']');
	$sql_query = remove_remarks($sql_query);
	$sql_query = split_sql_file($sql_query, ';');

	foreach ($sql_query as $sql) {
		$search = array('dle_', '{PREFIX}', '{USERPREFIX}');
		$replace = array(PREFIX.'_', PREFIX, USERPREFIX);
		$sql = str_replace($search, $replace, $sql);
		$sql = __($sql, true, false);
		$db->query($sql);
	}

	$fname = str_replace(ROOT_DIR, '', $fname);
	echo '<li>Выполнены запросы из файла &laquo;'.$fname.'&raquo;</li>';
}



$fname = ROOT_DIR.'/wc_payeer_install/append/config.webcash.php';
if (file_exists($fname)) {
	require $fname;
	
	$only_once_flag = false;
	foreach ($webcash_config as $section_key => $section_value) {
		$arr = $webcash->helper->arrayMergeRecursiveEx($webcash->config->getSection($section_key), $section_value);
		$was_changed = $webcash->config->getSection($section_key) != $arr;
		
		if ($was_changed) {
			if (!$only_once_flag) {
				$only_once_flag = true;
				
				$fname = ENGINE_DIR.'/data/config.webcash.php';
				$extension = pathinfo($fname, PATHINFO_EXTENSION);
				$bak_fname = str_replace('.'.$extension, '.payeer.bak.'.$extension, $fname);
				nd_copy($fname, $bak_fname);
				echo '<li>Создана резервная копия файла &laquo;'.str_replace(ROOT_DIR, '', $bak_fname).'&raquo;</li>';
				echo '<li>В файл &laquo;/engine/data/config.webcash.php&raquo; добавлены соответствующие изменения из файла &laquo;/wc_payeer_install/append/config.webcash.php&raquo;</li>';
			}
			
			$webcash->config->setSection($section_key, $arr);
		}
	}
}
?>
</ul>

<p>Установка успешно завершена, папка &laquo;wc_payeer_install&raquo; может быть удалена. Вы можете <a href="<?php echo $webcash->module_admin_url.'&tab='.Admin_Panel::GATEWAYS_TAB.'&action=addonsett&plg_alias=payeer'; ?>" target="_blank">перейти в админцентр</a>, чтобы настроить установленный шлюз или <a href="/" target="_blank">перейти на Главную страницу</a> сайта.</p>


<?php require_once 'readme.php'; ?>