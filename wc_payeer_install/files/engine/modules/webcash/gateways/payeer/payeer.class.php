<?php
/**
 * http://new-dev.ru/
 * author GoldSoft <newdevexpert@gmail.com>
 * Copyright (c) New-Dev.ru
 */

namespace WebCash;

defined('DATALIFEENGINE') or exit('Access Denied');

class Gateways_Payeer extends AddonSettings
{
	protected $alias = 'payeer';
    
	public function renderPaymentForm($invoice_id, $cs_key, $gw_item_id) {
		if ($str = $this->verifyGatewayEnable())
			return $str;
		
		if (!$checkout_store = $this->checkout->getDataStore($cs_key))
			return '';
		
		$service_info = $this->getServiceInfo();
		
		if (!$gw_item = safe_array_access($service_info, 'items', $gw_item_id))
			trigger_error(basename(__FILE__).', line: '.__LINE__, E_USER_ERROR);
		
		$amount = $this->wc_currency->convertMainCurrencyTo($checkout_store['amount'], $this->currency);
		$amount = number_format($amount, 2, '.', '');
		$payment_desc = base64_encode($this->webcash->site_url.' - '.to_utf8(safe_array_access($checkout_store, 'checkout_header')));
		
        $signature = $this->signData(array(
			$this->merchant_id,
			$invoice_id,
			$amount,
			$this->currency,
			$payment_desc,
			$this->secret_key
        ));
		
		$tpl = $this->webcash->getTplInstance();
		$tpl->assign('merchant_id', $this->merchant_id);
		$tpl->assign('amount', $amount);
		$tpl->assign('invoice_id', $invoice_id);
		$tpl->assign('currency', $this->currency);
		$tpl->assign('description', $payment_desc);
		$tpl->assign('email', $this->helper->htmlspecialchars(safe_array_access($checkout_store, 'email')));
		$tpl->assign('signature', $signature);
		
		$tpl->assign('gw_item_id', $gw_item_id);
		$tpl->assign('gw_item', $gw_item);
		$tpl->assign('gateway_header', $service_info['name']);
		$tpl->assign('gw_alias', $this->alias);
		$tpl->assign('gateway_cfg', $this->getCfgPublicParams());
		$tpl->assign('addon_settings_link', $this->renderAddonSettingsLink());
		$tpl->assign('user_hash', $this->user->nonce);
		$tpl->load_template('/modules/webcash/gateways/payeer/checkout.tpl');
		
		$tpl->compile('content');
		
		return $tpl->result['content'];
	}
	
	public function signData($params) {
		return strtoupper(hash('sha256', implode(':', $params)));
	}
	
	public function processing() {
		// Отклоняем запросы с IP-адресов, которые не принадлежат Payeer
		if ($this->server_ip) {
			$allowed = explode("\n", $this->server_ip);
			$allowed = array_map('trim', $allowed);
			
			$ip_checked = false;
			
			foreach(array(
				'HTTP_X_CLUSTER_CLIENT_IP',
				'HTTP_X_FORWARDED_FOR',
				'HTTP_X_FORWARDED',
				'HTTP_FORWARDED_FOR',
				'HTTP_FORWARDED',
				'HTTP_CLIENT_IP',
				'REMOTE_ADDR'
			) as $param) {
				foreach ($allowed as $ip) {
					if (safe_array_access($_SERVER, $param) === $ip) {
						$ip_checked = true;
						break;
					}
				}
			}
			
			if (!$ip_checked) {
				$this->printError('Неверный IP-адрес');
			}
		}
		
		
		$params = array();
		// проверка на наличие обязательных полей
		foreach(array(
			'm_sign',
			'm_operation_id',
			'm_operation_ps',
			'm_operation_date',
			'm_operation_pay_date',
			'm_shop',
			'm_orderid',
			'm_amount',
			'm_curr',
			'm_desc',
			'm_status',
		) as $key => $field) {
			if (!$str = POST($field)) {
				$this->printError('Не указаны обязательные данные');
			}
			
			if ($key) {
				$params[] = $str;
			}
		}
		fs_log($_POST);
		fs_log($params);
		print2($params);
		
		// нормализация данных
		$merchant_id = (int)$_POST['m_shop'];
		$amount = only_float($_POST['m_amount']);
		$invoice_id = (int)$_POST['m_orderid'];
		$signature = $_POST['m_sign'];
		$email = $_POST['P_EMAIL'];
		
		// проверка валюты
		if ($_POST['m_curr'] != $this->currency) {
			$this->printError('Неверная валюта '.$_POST['m_curr']);
		}
		
		// проверка ID магазина
		if ($merchant_id != $this->merchant_id) {
			$this->printError('Неверный ID магазина '.$merchant_id);
		}
		
		if ($_POST['m_status'] !== 'success') {
			$this->printError('Неверный статус '.$_POST['status']);
		}
		
		// проверка значения сигнатуры (используется ваш второй секретный ключ)
		$params[] = $this->secret_key2;
		$signature_calc = $this->signData($params);
		
		if ($signature_calc !== $signature) {
			$this->printError('Неверная подпись '.$signature);
		}
		
		
		$this->readSettingsFromFile();
		
		if (!$invoice_row = $this->webcash->getRowById($this->webcash->gateway_invoices_table, $invoice_id)) {
			$this->printError('Нет такого инвойса');
		}
		
		if ($invoice_row['state']) {
			$this->printError('Инвойс уже оплачен');
		}

		if ($invoice_row['gateway'] != $this->alias) {
			$this->printError('Инвойс не той платежной системы');
		}
		
		if ($invoice_row['amount'] > $amount) {
			$this->printError('Неверная сумма: '.$amount);
		}
		
		
		if (!$sender = $email) {//LASTSUPER
			if (!$sender = $phone) {
				$sender = $intid;
			}
		}
		
		$payment_id = $this->processAfterPayment($invoice_id, $sender);
		
		
		if ($payment_row = $this->webcash->getRowById($this->webcash->gateway_payments_table, $payment_id)) {
			$this->checkout->gatewaySuccessPayment($invoice_row, $payment_row);

			//fs_log("Инвойс #" . $invoice_id . " оплачен!");
			exit($_POST['m_orderid'].'|success');// успешный ответ для платежного шлюза и завершение скрипта
		}
		
		echo $_POST['m_orderid'].'|error';
		
		$this->printError('Error '.__LINE__);
	}
	
	public function printError($text) {
		$text = 'Ошибка! '.$text;
		//fs_log('Merchant error ('.$this->alias.'): '.$text);
		if (WebCash::DEBUG_SETTINGS)
			echo $text;
		exit;
	}
	
	public function processAfterPayment($invoice_id, $sender) {
		$gateway_details_arr = POST();
		$gateway_details_arr = array_map(array($this->helper, 'htmlspecialchars'), $gateway_details_arr);
		
		return $this->checkout->addPaymentToDb($invoice_id, $sender, $gateway_details_arr, false);
	}
	
	public function getServiceInfo() {
		$result = array(
			'name' => __('Payeer'),
			'alias' => $this->alias,
			'items' => array(
				1 => array(
					'title' => __('Перейти к оплате'),
					'image' => 'payeer.png',
				),
			),
		);
		
		return $result;
	}

}