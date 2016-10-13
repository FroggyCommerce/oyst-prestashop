<?php
/**
 * 2013-2016 Froggy Commerce
 *
 * NOTICE OF LICENSE
 *
 * You should have received a licence with this module.
 * If you didn't download this module on Froggy-Commerce.com, ThemeForest.net,
 * Addons.PrestaShop.com, or Oyst.com, please contact us immediately : contact@froggy-commerce.com
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to benefit the updates
 * for newer PrestaShop versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Froggy Commerce <contact@froggy-commerce.com>
 * @copyright 2013-2016 Froggy Commerce / 23Prod / Oyst
 * @license   GNU GENERAL PUBLIC LICENSE
 */

// Construct path
$config_path = dirname(__FILE__).'/../../config/config.inc.php';
$module_path = dirname(__FILE__).'/oyst.php';

// Set _PS_ADMIN_DIR_ define
define('_PS_ADMIN_DIR_', getcwd());

// Keep going if config script is found
if (file_exists($config_path)) {
    include($config_path);
    include($module_path);
    if (OystIsPHPCLI()) {
        $oyst = new Oyst();
        $oyst->exportCatalog();
    } else {
        die('Should be called in command line');
    }
} else {
    die('ERROR');
}

// Function IsPHPCLI
function OystIsPHPCLI()
{
    return (defined('STDIN') || (Tools::strtolower(php_sapi_name()) == 'cli' && (!isset($_SERVER['REMOTE_ADDR']) || empty($_SERVER['REMOTE_ADDR']))));
}