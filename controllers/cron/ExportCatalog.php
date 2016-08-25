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

class OystExportCatalogModuleCronController
{
    private $_module;
    public $context;

    /**
     * OystExportCatalogModuleCronController constructor.
     * @param Oyst $module
     */
    public function __construct($module)
    {
        $this->_module = $module;
    }

    /**
     * Run method
     */
    public function run()
    {
        // Get products
        $count = 1;
        $products = array();
        $oyst_product = new OystProduct();
        $result = $oyst_product->getProductsRequest();
        while ($row = Db::getInstance()->nextRow($result)) {
            $products[] = $oyst_product->getProductData($row['id_product']);

            $oyst_api = new OystSDK();
            $oyst_api->setApiPaymentEndpoint(Configuration::get('FC_OYST_API_EXPORT_ENDPOINT'));
            $oyst_api->setApiKey(Configuration::get('FC_OYST_API_KEY'));
            $result = $oyst_api->productPostRequest($products);
            d($result);

            echo ($count++)." product(s)     \r";
        }

        echo count($products)." products exported\n";
    }
}
