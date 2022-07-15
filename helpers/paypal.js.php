<?php
/**
 * @package   Paypal standard payments plugin
 * @version   0.0.1
 * @author    https://www.brainforge.co.uk
 * @copyright Copyright (C) 2022 Jonathan Brain. All rights reserved.
 * @license   GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');

/** @var plgHikashoppaymentBfpaypalstandardHelper $paypalHelper */

$jsArgs = [];
$jsArgs[] = 'client-id=' . $paypalHelper->plugin_params->client_id;
$jsArgs[] = 'currency='  . $paypalHelper->order->order_currency_info->currency_code;
$jsArgs[] = 'intent=capture';

// Facilitate payments on behalf of other merchants
//$jsArgs[] = 'merchant=other_merchant_client_id';

// Show only funding sources that you can vault or use to create a billing agreement, subscription, or recurring payment.
//$jsArgs[] = 'vault=true|false';

if (empty($paypalHelper->plugin_params->funding))
{
	$jsArgs[] = 'disable-funding=' . implode(',', array_keys($paypalHelper::fundingTypes()));
}
else
{
	if (!empty($paypalHelper->plugin_params->funding_types))
	{
		$jsArgs[] = 'enable-funding=' . implode(',', $paypalHelper->plugin_params->funding_types);

		$otherFundingTypes = array_diff(
                array_keys($paypalHelper::fundingTypes()), $paypalHelper->plugin_params->funding_types);
		if (!empty($otherFundingTypes))
		{
			$jsArgs[] = 'disable-funding=' . implode(',', $otherFundingTypes);
		}
	}
}

$shippingAddressInfo = $paypalHelper->getAddressInfo('shipping');

?>
<div id="smart-button-container">
    <div style="text-align: center;">
        <div id="paypal-button-container"></div>
    </div>
</div>

<script src="https://www.paypal.com/sdk/js?<?php echo implode('&', $jsArgs);?>"
        data-client-token="<?php echo $paypalHelper->paypal_params->clientToken; ?>"
></script>

<script>
    function initPayPalButtons() {
        paypal.Buttons({
            style: {
                shape: 'pill',
            },

            createOrder: function(data, actions) {
                return actions.order.create({
                    application_context: {
                        brand_name: "<?php echo $paypalHelper->getBrandName(); ?>",
                        shipping_preference: "<?php echo empty($shippingAddressInfo) ? 'NO_SHIPPING' : 'SET_PROVIDED_ADDRESS'; ?>",
                    },
                    intent: "CAPTURE",
                    purchase_units: [{
                        invoice_id:"<?php echo $paypalHelper->order->order_number;?>",
                        amount:{
                            currency_code:"<?php echo $paypalHelper->order->order_currency_info->currency_code;?>",
                            value:<?php echo $paypalHelper->order->order_full_price;?>
                        },
                        <?php
                        if (!empty($shippingAddressInfo))
						{
                            ?>
                            shipping: { address: { <?php echo implode(',', $shippingAddressInfo) ?> } },
                            <?php
                        }
                        ?>
                                     }],
                });
            },

            onApprove: function(data, actions) {
                return actions.order.capture().then(function(orderData) {

                    <?php echo $paypalHelper->consoleLog(array(
                                            "'Capture result'", 'orderData', 'JSON.stringify(orderData, null, 2)')); ?>

                    <?php echo $paypalHelper->onOrderCaptureMessage(); ?>

                    fetch('<?php echo $paypalHelper->getNotifyUrl('onApprove'); ?>' +
                                                    "&result=" + JSON.stringify(orderData)
                    ).then(function(res) {
                        return res.json();
                    }).then(function (result) {
                        switch(result.status)
                        {
                            case '1':
                                document.getElementById('bfpaypalstandard-end').innerHTML = result.message;
                                break;
                            case '2':
                                window.location.href = result.url;
                                break;
                            default:
                                alert(this.responseText);
                                break;
                        }
                    }).catch(function (err) {
						<?php echo $paypalHelper->consoleLog('err', 'PLG_BFPAYPALSTANDARD_PAYMENTERROR'); ?>
                    })
                    ;
                });
            },

            onError: function(err) {
				<?php echo $paypalHelper->consoleLog('err', 'PLG_BFPAYPALSTANDARD_PAYMENTERROR'); ?>
            }
        }).render('#paypal-button-container');
    }

    initPayPalButtons();
</script>
