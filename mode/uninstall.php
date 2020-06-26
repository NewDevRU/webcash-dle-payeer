<?php
/**
 * http://new-dev.ru/
 * author GoldSoft <newdevexpert@gmail.com>
 * Copyright (c) New-Dev.ru
 */

defined('DATALIFEENGINE') or exit('Access Denied');
?>

<h2>Удаление платежного шлюза</h2>

<?php
if (!file_exists(ENGINE_DIR.'/data/config.webcash.php')) {
	echo '<p class="message information">Ошибка! &laquo;/engine/data/config.webcash.php&raquo; отсутствует. Возможно, модуль &laquo;WebCash&raquo; не установлен.</p>';
	echo_bottom_html();
	exit;
}



//проверка на права доступа
$arr = array(
	ENGINE_DIR.'/data/',
	ENGINE_DIR.'/modules/webcash/',
	ROOT_DIR.'/templates/',
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


$fname = ROOT_DIR.'/wc_payeer_install/sql/uninstall.sql';

if (file_exists($fname))
{
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


restore_from_bak_file(ENGINE_DIR.'/data/config.webcash.php', 'payeer');
nd_unlink(ENGINE_DIR.'/data/config.webcash.cache.php');//сброс кэша конфиг-файла



if (nd_rmdir(ENGINE_DIR.'/modules/webcash/gateways/payeer/'))
	echo '<li>Удалены файлы шлюза</li>';

?>
</ul>

<p>Удаление шлюза успешно завершено, папка &laquo;wc_payeer_install&raquo; может быть удалена. Вы можете <a href="/<?php echo $config['admin_path']; ?>" target="_blank">перейти в админцентр</a> или <a href="/" target="_blank">на Главную страницу</a> сайта.</p>