<?php
/**
 * http://new-dev.ru/
 * author GoldSoft <newdevexpert@gmail.com>
 * Copyright (c) New-Dev.ru
 */

namespace WebCash;

defined('DATALIFEENGINE') or exit('Access Denied');

if (!$this->readSettingsFromFile()) {
	$this->setCfgValue('display_name', 'Платежный шлюз Payeer');
	$this->setCfgValue('description', 'С помощью этого шлюза можно организовать прием платежей через платежную систему Payeer.');
	$this->setCfgValue('merchant_id', WebCash::DEBUG_SETTINGS ? '1061399787' : '');
	$this->setCfgValue('secret_key', WebCash::DEBUG_SETTINGS ? '1iQvkS7uXVLsdziz' : '');
	$this->setCfgValue('secret_key2', WebCash::DEBUG_SETTINGS ? 'WqNtWqNtxJIHxJoIxJoIrAR3' : '');
	$this->setCfgValue('server_ip', WebCash::DEBUG_SETTINGS ? '' : "185.71.65.92\n185.71.65.189\n149.202.17.210");
	$this->setCfgValue('currency', 'RUB');
	$this->setCfgValue('site_url', 'https://payeer.com');
	$this->setCfgValue('access_allowed_usergroups', array());
	$this->setNoneFormControlCfgValue('public_cfg_fields', array(
		'merchant_id',
		'currency',
	));
	
	
	$result_url = $this->checkout->getGatewayProcessingUrl($this->alias);
	$success_url = $this->checkout->successCheckoutUrl();
	$fail_url = $this->checkout->failCheckoutUrl();
	
	$this->addHint(__FILE__.'1', sprintf(__('Внимание, перед началом работы необходимо зарегистрироваться на сайте <a href="https://payeer.com/" target="_blank">https://payeer.com/</a>, и создав магазин, скопировать секретные ключи в настройки плагина. В настройках магазина, кроме других обязательных полей, необходимо указать URL возврата в случае успеха: <b>%s</b>, URL возврата в случае неудачи: <b>%s</b>, URL оповещения: <a href="%s" target="_blank"><code>%s</code></a>.'), $success_url, $fail_url, $result_url, $result_url), false);
	
	$this->setFieldsItem('merchant_id', array(
		'title' => 'Идентификатор магазина',
		'hint' => 'Идентификатор магазина, узнать его можно в <a href="https://payeer.com/ru/account/api/" target="_blank">личном кабинете Payeer</a>',
		'type' => 'text',
		'required' => true,
	));
	
	$this->setFieldsItem('secret_key', array(
		'title' => 'Секретный ключ',
		'hint' => 'Первый секретный ключ, получить его можно в <a href="https://payeer.com/ru/account/api/" target="_blank">личном кабинете Payeer</a>',
		'type' => 'text',
		'required' => true,
	));
	
	$this->setFieldsItem('secret_key2', array(
		'title' => 'Ключ для шифрования дополнительных параметров',
		'hint' => 'Второй секретный ключ, получить его можно в <a href="https://payeer.com/ru/account/api/" target="_blank">личном кабинете Payeer</a>',
		'type' => 'text',
		'required' => true,
	));
	
	$arr = $this->wc_currency->getCurrenciesList();
	$arr = filter_allowed_keys($arr, array('RUB', 'USD', 'EUR'));
	
	$this->setFieldsItem('currency', array(
		'title' => 'Валюта аккаунта',
		'hint' => 'Валюта которая используется в аккаунте Payeer',
		'type' => 'select',
		'value' => array_keys($arr),
		'label' => array_values($arr),
		'required' => true,
	));

	$this->setFieldsItem('server_ip', array(
		'title' => 'Доверенные IP-адреса Payeer',
		'hint' => 'Если указать IP-адреса шлюза (по одному в каждой строке), то извещения будут приниматься только с этих адресов. Если надо убрать данную проверку - поле должно быть пустым.',
		'type' => 'textarea',
		'tag_part' => 'style="height:70px;"',
	));
		
	$this->setFieldsItem('site_url', array(
		'title' => 'Сайт провайдера',
		'hint' => 'Данный адрес используется в списке шлюзов только для удобства работы',
		'type' => 'text',
	));
	
	
	$this->convertDefaultValues();
	$this->writeSettingsInFile(true);
}