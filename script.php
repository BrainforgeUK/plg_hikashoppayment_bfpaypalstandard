<?php
/**
 * @package   Paypal standard payments plugin
 * @version   0.0.1
 * @author    https://www.brainforge.co.uk
 * @copyright Copyright (C) 2022 Jonathan Brain. All rights reserved.
 * @license   GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

// no direct access
defined('_JEXEC') or die('Restricted Access');

class plghikashoppaymentBfpaypalstandardInstallerScript {
  private function cleanup($parent)
  {
	  // Delete every install / update / delete to make sure old rubbish not left behind
	  $notifyScript = JPATH_ROOT . '/plghikashoppaymentbfpaypalstandard.php';
	  if (file_exists($notifyScript))
	  {
		  unlink($notifyScript);
	  }
  }

  function install($parent)
  {
    $this->cleanup($parent);
  }
  
  function uninstall($parent)
  {
	  $this->cleanup($parent);
  }
  
  function update($parent)
  {
	  $this->cleanup($parent);
  }
  
  function preflight($type, $parent)
  {
  }
  
  function postflight($type, $parent)
  {
  }
}
?>