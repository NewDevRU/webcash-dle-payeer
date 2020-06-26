<?php
/**
 * http://new-dev.ru/
 * author GoldSoft <newdevexpert@gmail.com>
 * Copyright (c) New-Dev.ru
 */

defined('DATALIFEENGINE') or exit('Access Denied');
?>

<h2>Инструкция по подключению</h2>

<p>
	С помощью этого шлюза пользователь сайта может осуществлять платежи через систему Payeer на вашем сайте. Шаблон платежной формы шлюза находится в папке &laquo;/modules/webcash/gateways/freekassa/&raquo;.
</p>


<p class="message information">Внимание! Ознакомиться с другими нашими модулями и плагинами можно на сайте <a href="http://new-dev.ru/" target="_blank">New-Dev.ru</a></p>

<h2>Контакты для связи</h2>
<p>В случае возникновения технических вопросов или предложений по улучшению модуля связаться можно <strong>по почте: </strong><a href="mailto:newdevexpert@gmail.com">newdevexpert@gmail.com</a> или <strong>телеграму: </strong> @NewDevRU.</p>

<br>
<h3>Ручная установка</h3>
<p>При возникновении проблем с автоматической установкой, можно завершить процесс вручную.</p>
<ul onload="init();">
	<li>1) Скопировать содержимое папки &laquo;/wc_freekassa_install/files/&raquo; в корень сайта<br /><br /></li>
	
	<li>
		2) 
		<?php
		$fname = ROOT_DIR.'/wc_freekassa_install/append/config.webcash.php';
		if (file_exists($fname)) {
			require $fname;
			
			echo 'Внести изменения в файл &laquo;/engine/data/config.webcash.php&raquo;:<br>';
			
			foreach ($webcash_config as $section_key => $section_value) {
				$arr = $webcash->helper->arrayMergeRecursiveEx($webcash->config->getSection($section_key), $section_value);
				$was_changed = $webcash->helper->arrayDiffRecursive($webcash->config->getSection('MAIN'), $arr);
				
				if ($was_changed) {
					foreach ($section_value as $key => $value) {
						$note = in_array($section_key, array('GATEWAYS', 'PLUGINS')) ? ' (изменив порядковый индекс)' : '';
						
						if ($section_key == 'CLASSES')
							$value = str_replace('WebCash\\', 'WebCash\\\\', $value);
						
						echo 'в секцию &laquo;'.$section_key.'&raquo; добавить строку <b>\''.$key.'\' => \''.$value.'\'</b>'.$note.'<br>';
					}
				}
			}
		}
		?>

		
	</li>
</ul>


<br>
<h3>FAQ (часто задаваемые вопросы)</h3>
<ul class="list2">
	<li><b>Вопрос:</b> Как удалить плагин или при необходимости снова прочитать инструкцию по подключению без запуска установки</li>
	<li><b>Ответ:</b> Удалить плагин можно по ссылке <a href="/wc_freekassa_install/index.php?mode=uninstall" target="_blank">/wc_freekassa_install/index.php?mode=uninstall</a>, а прочитать инструкцию без запуска установки платежного шлюза по ссылке <a href="/wc_freekassa_install/index.php?mode=readme" target="_blank">/wc_freekassa_install/index.php?mode=readme</a>.</li>
</ul>