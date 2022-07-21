<?php
/**
 * @package   Paypal standard payments plugin
 * @version   0.0.1
 * @author    https://www.brainforge.co.uk
 * @copyright Copyright (C) 2022 Jonathan Brain. All rights reserved.
 * @license   GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');

class plgHikashoppaymentBfpaypalstandardHelper
{
	protected $plugin;

	public $plugin_params;
	public $order;

	/*
	 */
	protected function __construct($plugin)
	{
		if (empty($plugin))
		{
			return;
		}

		$this->plugin 			= $plugin;
		$this->plugin_params 	= $plugin->plugin_params;
		$this->order 			= $plugin->order;
	}

	/*
	 */
	public static function buttons($plugin)
	{
		try
		{
			$paypalHelper = new plgHikashoppaymentBfpaypalstandardHelper($plugin);

			$paypalHelper->paypal_params = $plugin->app->getUserState('plghikashoppayment.bfpaypalstandard.paypal_params');

			if (empty($paypalHelper->paypal_params->status))
			{
				$paypalHelper->paypal_params = new stdClass();
				$paypalHelper->paypal_params->userpwd    = $plugin->plugin_params->client_id . ':' . $plugin->plugin_params->client_secret;
				$paypalHelper->paypal_params->bnCode     = 'BrainforgeUK';
				$paypalHelper->paypal_params->customerId = "ORDER_USER_ID-" . $paypalHelper->order->order_user_id;

				$paypalHelper->paypal_params->accessToken = $paypalHelper->createToken();
				$paypalHelper->paypal_params->clientToken = $paypalHelper->createClientToken();

				$paypalHelper->paypal_params->status = true;

				$plugin->app->setUserState('plghikashoppayment.bfpaypalstandard.paypal_params', $paypalHelper->paypal_params);
			}
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
			return false;
		}

        include __DIR__ . '/paypal.js.php';

		return $paypalHelper;
	}

	/*
	 */
	protected function createToken()
	{
		$url = 'https://' . $this->getPayPalApiUrl() . '/v1/oauth2/token';
		$ch = $this->curlInit($url);

		//Set the required headers
		$headers = array(
			'Accept: application/json',
			'Content-Type: application/x-www-form-urlencoded',
			'Accept-Language: en_US',
			'PayPal-Partner-Attribution-Id: ' . $this->paypal_params->bnCode,
		);
		curl_setopt($ch,CURLOPT_HTTPHEADER, $headers);

		//Specify that we want access credentials returned
		$vars['grant_type'] = 'client_credentials';

		//build and set the request
		$req = http_build_query($vars);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $req);

		//get the response back
		$response = curl_exec($ch);
		if (empty($response))
		{
			throw new Exception(Text::sprintf('PLG_BFPAYPALSTANDARD_ERROR', '001'));
		}

		//parse json into php array
		$outArray = json_decode($response, true);

		//Close the connection
		curl_close($ch);

        if (empty($outArray['access_token']))
		{
            echo '<pre>';
			print_r($outArray);
			echo '</pre>';
			throw new Exception(Text::sprintf('PLG_BFPAYPALSTANDARD_ERROR', '002'));
		}

		//Extract the access token from the response so it can be used in the createOrder file
		return $outArray['access_token'];
	}

    /*
     */
    protected function createClientToken()
	{
		if (empty($this->paypal_params->accessToken))
		{
			throw new Exception(Text::sprintf('PLG_BFPAYPALSTANDARD_ERROR', '011'));
		}

		$url = 'https://' . $this->getPayPalApiUrl() . '/v1/identity/generate-token';
		$ch = $this->curlInit($url);

		//Set the required headers
		$headers = array(
			'Accept: application/json',
			'Content-Type: application/json',
			'Accept-Language: en_US',
			'Authorization: Bearer ' . $this->paypal_params->accessToken,
			'PayPal-Partner-Attribution-Id: ' . $this->paypal_params->bnCode,
		);
		curl_setopt($ch,CURLOPT_HTTPHEADER, $headers);

		$vars = [];
		$vars['customer_id'] = $this->paypal_params->customerId;
		$json = json_encode($vars);

		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);

		//get the response back
		$response = curl_exec($ch);
		if (empty($response))
		{
			throw new Exception(Text::sprintf('PLG_BFPAYPALSTANDARD_ERROR', '012'));
		}

        //parse json into php array
		$outArray = json_decode($response, true);

        //Close the connection
		curl_close($ch);

        if (empty($outArray['client_token']))
		{
			echo '<pre>';
			print_r($outArray);
			echo '</pre>';
			return false;
		}

		//Extract the access token from the response so it can be used in the createOrder file
		return $outArray['client_token'];
	}

	/*
	 */
	protected function curlInit($url)
	{
		//Initiate CURL and set the url endpoint
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);

		if ($_SERVER['REMOTE_ADDR'] == $_SERVER['SERVER_ADDR'])
		{
			// Assume development environment
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		}

		//Set the user and password
		curl_setopt($ch, CURLOPT_USERPWD, $this->paypal_params->userpwd);

		//Keep connection open until we get data back
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		return $ch;
	}

	/*
	 */
	protected function getPayPalApiUrl()
    {
        return $this->plugin_params->sandbox ? 'api.sandbox.paypal.com' : 'api.paypal.com';
	}

	/*
	 */
	public static function fundingTypes()
	{
		return array(
			'card'			=> 	'Credit or debit cards',
			'credit'		=>	'PayPal Credit (US, UK)',
			'paylater'		=>	'Pay Later (US, UK), ' .
                                'Pay in 4 (AU), ' .
                                '4X PayPal (France), ' .
                                'Später Bezahlen (Germany)',
			'bancontact'	=>	'Bancontact',
			'blik'			=>	'BLIK',
			'eps'			=>	'eps',
			'giropay'		=>	'giropay',
			'ideal'			=>	'iDEAL',
			'mercadopago'	=>	'Mercado Pago',
			'mybank'		=>	'MyBank',
			'p24'			=>	'Przelewy24',
			'sepa'			=>	'SEPA-Lastschrift',
			'sofort'		=>	'Sofort',
			'venmo'			=>	'Venmo',
		);
	}

	/*
	 */
	public function getAddressInfo($type='shipping')
	{
		$fields = array(
			'address_line_1' => 'address_street',
			'address_line_2' => 'address_street2',
			'admin_area_2'   => 'address_city',
			'admin_area_1'   => 'address_state',
			'postal_code'    => 'address_post_code',
			'country_code'   => 'address_country',
						);

		switch($type)
		{
			case 'shipping':
				if (!$this->plugin_params->shipping ||
					empty($this->order->order_shipping_id) ||
					empty($this->order->order_shipping_address_id) ||
					empty($this->order->cart->shipping_address) ||
					empty($fields)) return false;
				$cartField = 'shipping_address';
				break;
			default:
				return false;
		}

		$zoneClass = hikashop_get('class.zone');

		$result = [];
		foreach($fields as $paypalField=>$addressField)
		{
			switch($paypalField)
			{
				case 'address_line_1':
				case 'admin_area_2':
				case 'postal_code':
					$addressField = trim(@$this->order->cart->$cartField->$addressField);
					if (empty($addressField)) return false;
					break;
				case 'country_code':
					$addressField = @$this->order->cart->$cartField->$addressField;
					if (empty($addressField)) return false;
					$addressField = $zoneClass->getZones(array($addressField[0]), 'zone_code_2', 'zone_namekey', true)[0];
					if (empty($addressField)) return false;
					break;
				case 'admin_area_1':
					$addressField = @$this->order->cart->$cartField->$addressField;
					if (empty($addressField)) continue 2;
					$addressField = $zoneClass->getZones(array($addressField[0]), 'zone_name', 'zone_namekey', true)[0];
					break;
			}

			$result[] = $paypalField . ': "' . $addressField . '"';
		}

		return $result;
	}

	/*
	 */
	public function getNotifyUrl($action)
	{
		return $this->plugin->getNotifyUrl($action);
	}

	/*
	 */
	public function getBrandName()
	{
		return $this->plugin->app->get('sitename');
	}
}
