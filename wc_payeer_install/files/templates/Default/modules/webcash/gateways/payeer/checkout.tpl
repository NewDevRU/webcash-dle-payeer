{{ addon_settings_link|raw }}

<h5>{{ gateway_header }}</h5>

<div class="sepH_b">
	__('Инвойс №') {{ invoice_id }}
</div>

<div class="sepH_b">
	{% if email %} <b>__('Е-майл: ')</b> {{ email }} {% endif %}
</div>

<form id="pay" name="pay" method="GET" action="//payeer.com/api/merchant/m.php" accept-charset="UTF-8">
	<input type="hidden" name="m_shop" value="{{ merchant_id }}" />
	<input type="hidden" name="m_orderid" value="{{ invoice_id }}" />
	<input type="hidden" name="m_amount" value="{{ amount }}" /><!-- сумма платежа -->
	<input type="hidden" name="m_curr" value="{{ currency }}" />
	<input type="hidden" name="m_desc" value="{{ description }}" />
	<input type="hidden" name="m_sign" value="{{ signature }}" />
	
	<button type="submit" name="m_process">__('Далее')</button>
</form>