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
    require_once _PS_MODULE_DIR_.'/oyst/classes/OystProduct15.php';
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
        $this->context = Context::getContext();
        if (!isset($this->context->currency) || !Validate::isLoadedObject($this->context->currency)) {
            $this->context->currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        }
        $this->context->shop = new Shop($this->context->shop->id);
    }

    /**
     * Run method
     */
    public function run()
    {
        // Get products
        $oyst_product = new OystProduct();
        $result = $oyst_product->getProductsRequest($this->context->language->id);
        while ($row = Db::getInstance()->nextRow($result)) {
            $product = $oyst_product->getProductData($row['id_product']);
            d($product);
        }
    }
}
