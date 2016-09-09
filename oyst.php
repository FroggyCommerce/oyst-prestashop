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
 * @license GNU GENERAL PUBLIC LICENSE
 */

/*
 * Security
 */
defined('_PS_VERSION_') || require dirname(__FILE__) . '/index.php';

/*
 * Include Froggy Library
 */
if (!class_exists('FroggyModule', false)) {
    require_once _PS_MODULE_DIR_.'/oyst/froggy/FroggyModule.php';
}
if (!class_exists('FroggyPaymentModule', false)) {
    require_once _PS_MODULE_DIR_.'/oyst/froggy/FroggyPaymentModule.php';
}

/*
 * Include Oyst SDK
 */
if (!class_exists('OystSDK', false)) {
    require_once _PS_MODULE_DIR_.'/oyst/classes/OystSDK.php';
}

/*
 * Include Oyst Product Class
 */
if (!class_exists('OystProduct', false)) {
    require_once _PS_MODULE_DIR_.'/oyst/classes/OystProduct.php';
}

define('_PS_OYST_DEBUG_', 1);

/**
 * Class Oyst
 */
class Oyst extends FroggyPaymentModule
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->name = 'oyst';
        $this->version = '0.2.0';
        $this->author = 'Froggy Commerce / 23Prod';
        $this->tab = 'payments_gateways';

        parent::__construct();

        $this->displayName = $this->l('Oyst');
        $this->description = $this->l('Oyst provides 1 click shopping advertising technology and creates a new ecosystem at the crossroads of customised advertising and online payment.');
        $this->module_key = '';
    }

    public function install()
    {
        $result = parent::install();

        // Clear cache
        CacheCore::clean('Module::getModuleIdByName_oyst');

        // Set Oyst in first position
        $id_hook = Hook::getIdByName('displayPayment');
        $id_module = Module::getModuleIdByName('oyst');
        $module = Module::getInstanceById($id_module);
        if (Validate::isLoadedObject($module)) {
            Db::getInstance()->execute('
            UPDATE `'._DB_PREFIX_.'hook_module` SET `position`= position + 1
            WHERE `id_hook` = '.(int)$id_hook);
            Db::getInstance()->execute('
            UPDATE `'._DB_PREFIX_.'hook_module` SET `position`= 1
            WHERE `id_hook` = '.(int)$id_hook.' AND `id_module` = '.$id_module);
            echo 'yeah';
        }

        return $result;
    }


    /**
     * Configuration method
     * @return string $html
     */
    public function getContent()
    {
        return $this->hookGetContent();
    }

    /**
     * Export catalog method
     */
    public function exportCatalog()
    {
        require_once _PS_MODULE_DIR_.'/oyst/controllers/cron/ExportCatalog.php';
        $controller = new OystExportCatalogModuleCronController($this);
        $controller->run();
    }


    /**
     * Logging methods
     */

    public function logNotification($name = '', $debug) {
        $data = "<!---- Start notification '.$name.' -->\n";
        $data .= "Response:\n".var_export(file_get_contents('php://input'), true)."/n";
        $data .= "Debug:\n".var_export($debug, true)."/n";
        $data .= "<!---- End notification -->\n";
        $this->log($data);
    }

    public function log($data) {
        if (_PS_OYST_DEBUG_ != 1) {
            return '';
        }
        if (is_array($data)) {
            $data = var_export($data, true);
        }
        file_put_contents(dirname(__FILE__).'/logs/log-notification.txt', '['.date('Y-m-d H:i:s').'] '.$data."\n", FILE_APPEND);
    }

}
