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

if (strpos($this->plugin_params->return_url, 'PLG_BFPAYPALSTANDARD_RETURNURL') === 0)
{
	$this->plugin_params->return_url = Text::sprintf($this->plugin_params->return_url, $this->order->order_id);
}

?>
<div id="bfpaypalstandard-end">
    <h3>
        <?php
		$this->currencyHelper = hikashop_get('class.currency');
        echo Text::sprintf('PLG_BFPAYPALSTANDARD_PAYMENTDUE',
                        $this->order->order_number,
			            $this->currencyHelper->format($this->order->order_full_price, $this->order->order_currency_id),
			            $this->order->order_currency_info->currency_code);
        ?>
    </h3>
    <?php
    plgHikashoppaymentBfpaypalstandardHelper::buttons($this);

    $cancelUrl = $this->getNotifyUrl('cancel');
    ?>
    <div id="bfpaypalstandard-cancel">
        <a class="hikabtn hikacart"
           href="<?php echo $cancelUrl; ?>"
        >
            <?php echo Text::_('PLG_BFPAYPALSTANDARD_CANCEL_ORDER'); ?>
        </a>
    </div>

    <?php
    if ($this->plugin_params->sandbox)
	{
        ?>
        <hr/>
        <?php echo Text::_('PLG_BFPAYPALSTANDARD_SANDBOXNOTES'); ?>
        <pre><?php echo $this->plugin_params->notes; ?></pre>
        <br/>
        <?php
    }
    ?>
</div>
