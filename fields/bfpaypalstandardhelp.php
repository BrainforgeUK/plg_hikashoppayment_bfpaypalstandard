<?php
/**
 * @package   Paypal standard payments plugin
 * @version   0.0.1
 * @author    https://www.brainforge.co.uk
 * @copyright Copyright (C) 2022 Jonathan Brain. All rights reserved.
 * @license   GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;

?><?php

class JFormFieldBfpaypalstandardhelp extends FormField
{
	protected $type = 'Bfpaypalstandardhelp';

	public static function configHelp($element=null)
	{
		$lang = Factory::getLanguage();
		$locale = $lang->getLocale()[2];
		$base = dirname(JURI::base());
		$text = [];

		foreach (array(
							 'bfpaypalstandard.pdf',
						 ) as $help) {
			$file = $locale . '.' . $help;
			if (!file_exists(__DIR__ . $file)) $file = 'en-GB.' . $help;
			$text[] = '<div style="height:3em;">';
			$text[] = '<a id="' . $help . '" href="' . $base . '/plugins/hikashoppayment/bfpaypalstandard/help/' . $file . '" ' . 'target="bfpaypalstandard-help" style="cursor:help;">';
			$text[] = '<button onclick="document.getElementById(\'' . $help . '\').click();return false;">' . $help . '</button>';
			$text[] = '</a>';
			$text[] = '</div>';
		}

		return implode("\n", $text);
	}

	protected function getInput()
	{
		return self::configHelp();
	}
}

