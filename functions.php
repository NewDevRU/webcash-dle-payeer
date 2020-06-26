<?php
/**
 * http://new-dev.ru/
 * author GoldSoft <newdevexpert@gmail.com>
 * Copyright (c) New-Dev.ru
 */

defined('DATALIFEENGINE') or exit('Access Denied');

function echo_top_html() {
	echo '
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="utf-8">
<title>Платежный шлюз &laquo;Payeer&raquo; для WebCash</title>
<link rel="stylesheet" id="template-css" href="style.css" type="text/css" media="screen"/>
</head>
<body class="page">
<div class="container">
	<div>
		<br>
		<div>
			<div>
				<div class="post">
					<h1>Платежный шлюз &laquo;Payeer&raquo; для WebCash для DataLife Engine</h1>
					<div class="entry">';
}

function echo_bottom_html() {
	echo '
					</div>
				</div>
			</div>
		</div>


		<footer>
			<section>
				<p>&copy; WebCash, '.(date('Y') == 2020 ? '' : '2020 - ').date('Y').' г., Все права защищены. <a href="http://new-dev.ru/" target="_blank">Оффициальный сайт модуля WebCash - New-Dev.ru</a></p>
				<p><strong>Емайл: </strong><a href="mailto:newdevexpert@gmail.com">newdevexpert@gmail.com</a> или <strong>Телеграм: </strong> @NewDevRU</p>
			</section>
		</footer>
	</div>
</div>
<script>
function init() {
	var x = document.getElementsByTagName("textarea");
	var i;
	for (i = 0; i < x.length; i++) {
		x[i].setAttribute("rows", "1");
		x[i].setAttribute("spellcheck", false);
		x[i].setAttribute("readonly", "readonly");
		x[i].style.height = "auto";
		x[i].style.height = x[i].scrollHeight + "px";
		x[i].addEventListener("click", function() {
			this.focus();
			this.select();
		});
	}
	
	x = document.getElementsByTagName("input");
	for (i = 0; i < x.length; i++) {
		x[i].addEventListener("click", function() {
			this.focus();
			this.select();
		});
	}
}

init();
</script>
</body>
</html>';
}


function modify_cms_file($fname, $alias, array $replace_array) {
	if (file_exists($fname)) {
		$buffer = $content = file_get_contents($fname);

		if (strpos($content, $alias) === false) {
			$extension = pathinfo($fname, PATHINFO_EXTENSION);
			$bak_fname = str_replace('.'.$extension, '.'.$alias.'.bak.'.$extension, $fname);
			nd_copy($fname, $bak_fname);
			echo '<li>Создана резервная копия файла &laquo;'.str_replace(ROOT_DIR, '', $bak_fname).'&raquo;</li>';
			

			if (!empty($replace_array['replace'])) {
				$arr = $replace_array;
				$replace_array = array(0 => $arr);
			}
			
			foreach ($replace_array as $value) {
				$replace = "\r\n\r\n".$value['replace']."\r\n\r\n";
				
				if ($value['action'] == 'after') {
					$replace = '$1'.$replace;
				} elseif ($value['action'] == 'before') {
					$replace = $replace.'$1';
				}
				
				
				$storage = array();

				$pattern = preg_replace_callback('#\[SKIP\](.+)\[/SKIP\]#Usi', function($matches) use (&$storage) {
					$index = count($storage);
					$storage[] = $matches[1];
					return '__ND_SKIP__'.$index;
				},
				$value['search']);
				
				$pattern = preg_quote($pattern, '#');
				
				foreach ($storage as $key => $value) {
					$pattern = str_replace('__ND_SKIP__'.$key, $value, $pattern);
				}
				
				
				$content = preg_replace('#('.$pattern.')#', $replace, $content);
			}

			if ($buffer != $content) {
				file_put_contents($fname, $content);

				$fname = str_replace(ROOT_DIR, '', $fname);
				echo '<li>Выполнено обновление файла &laquo;'.$fname.'&raquo;</li>';
				return true;
			}
		}
	}
}

function restore_from_bak_file($fname, $alias) {
	$extension = pathinfo($fname, PATHINFO_EXTENSION);
	$bak_fname = str_replace('.'.$extension, '.'.$alias.'.bak.'.$extension, $fname);
	
	if (file_exists($bak_fname)) {
		rename($bak_fname, $fname);
		if (file_exists($fname)) {
			$fname = str_replace(ROOT_DIR, '', $fname);
			echo '<li>Восстановлен файл &laquo;'.$fname.'&raquo; из резервной копии</li>';
			return true;
		}
	}
}

if (!function_exists('nd_htmlspecialchars')) {
	function nd_htmlspecialchars($text) {
		global $config;
		return htmlspecialchars($text, ENT_QUOTES, $config['charset']);
	}
}

if (!function_exists('nd_copy')) {
	function nd_copy($source, $dest) {// copy(), same modification-time
		if (!copy($source, $dest))
			return false;
		$dt = filemtime($source);
		if ($dt === false)
			return false;
		return touch($dest, $dt);
	}
}

if (!function_exists('nd_rmdir')) {
	function nd_rmdir($dir, $delete_self = true, $exclude = '') {
		if (is_dir($dir)) {
			$entries = scandir($dir);
			
			foreach ($entries as $entry) {
				if ($entry != '.' and $entry != '..') {
					if (is_dir($dir.'/'.$entry)) {
						nd_rmdir($dir.'/'.$entry);
					} else {
						if ($exclude and $exclude == $entry) {
							$delete_self = false;//anyway, empty folder can't delete
						} else {
							nd_unlink($dir.'/'.$entry, false);
						}
					}
				}
			}

			return $delete_self ? rmdir($dir) : true;
		}
	}
}

if (!function_exists('nd_unlink')) {
	function nd_unlink($path, $verify_exists = true) {
		if (!$verify_exists or is_file($path)) {
			if (!unlink($path)) {
				trigger_error('No permission to delete: ['.$path.']', E_USER_ERROR);
			}
			
			return true;
		}
		
		return false;
	}
}