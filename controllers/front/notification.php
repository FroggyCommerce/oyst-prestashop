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

/*
 * Security
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class OystNotificationModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function initContent()
    {
        // Check secure key
        if (Tools::getValue('key') != Configuration::get('FC_OYST_HASH_KEY')) {
            die('Secure key is invalid');
        }

        // Log notifications
        $this->module->log('General notification received');
        $this->module->logNotification('General', $_GET);

        // Decode data
        $event_data = trim(str_replace("'", '', Tools::file_get_contents('php://input')));
        $event_data = Tools::jsonDecode($event_data, true);

        // If products import event or new order
        if (isset($event_data['event']) && $event_data['event'] == 'products.import') {
            $this->eventProductsExport($event_data);
        } else if (isset($event_data['event']) && $event_data['event'] == 'notification.newOrder') {
            $this->eventOrderImport($event_data);
        }

        die('OK!');
    }

    /**
     * @param $event_data
     */
    public function eventProductsExport($event_data)
    {
        // Get import ID
        $import_id = $event_data['data']['import_id'];

        // Get number of products
        $oyst_product = new OystProduct();
        $nb_products = $oyst_product->getProductsRequest(true);

        // Send catalog
        $result = $oyst_product->sendCatalog();

        // Log result
        $this->module->log('Catalog sent');
        $this->module->log($result);

        // Return result
        $return = array('importId' => $import_id, 'totalCount' => $nb_products, 'remaining' => 0);
        die(Tools::jsonEncode($return));
    }

    public function eventOrderImport($event_data)
    {
        // Get order ID
        $order_id = $event_data['data']['order_id'];
    }
}
