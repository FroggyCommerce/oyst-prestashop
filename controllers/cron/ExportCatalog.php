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
    public $context;
    public $languages;
    private $_module;

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
        $languages = Language::getLanguages(true);
        foreach ($languages as $language) {
            $this->languages[$language['iso_code']] = $language['id_lang'];
        }
    }

    /**
     * Run method
     */
    public function run()
    {
        // Get products
        $result = OystProduct::getProductsRequest($this->context->language->id);
        while ($row = Db::getInstance()->nextRow($result)) {
            $product = $this->convertProduct($row);
            d($product);
        }
    }

    /**
     * Convert product data for Oyst Webservice
     * @param array $row product
     * @return array $product (oyst format)
     */
    public function convertProduct($row)
    {
        // Load product and associated categories
        $product = new ProductCore($row['id_product'], true, $this->context->language->id);
        list($main_category, $categories) = $this->getProductCategories($product);

        // Build product
        return array(
            'reference' => $product->id,
            'merchant_reference' => $product->reference,
            'is_active' => ($product->active == 1 ? true : false),
            'is_materialized' => ($product->is_virtual == 1 ? true : false),
            'title' => $product->name,
            'condition' => ($product->condition == 'used' ? 'reused' : $product->condition),
            'short_description' => $product->description_short,
            'description' => $product->description,
            'tags' => OystProduct::getProductTags($product->id, $this->context->language->id),
            'amount_excluding_taxes' => array(
                'value' => Product::getPriceStatic($product->id, false, null, 2, null, false, false),
                'currency' => $this->context->currency->iso_code,
            ),
            'amount_including_taxes' => array(
                'value' => Product::getPriceStatic($product->id, true, null, 2, null, false, false),
                'currency' => $this->context->currency->iso_code,
            ),
            'sale_amount_excluding_taxes' => array(
                'value' => Product::getPriceStatic($product->id, false, null, 2),
                'currency' => $this->context->currency->iso_code,
            ),
            'sale_amount_including_taxes' => array(
                'value' => Product::getPriceStatic($product->id, true, null, 2),
                'currency' => $this->context->currency->iso_code,
            ),
            'meta' => array(
                'title' => $product->meta_title,
                'description' => $product->meta_description,
            ),
            'url' => $this->context->link->getProductLink($product->id),
            'categories' => $categories,
            'category' => $main_category,
            'manufacturer' => $product->manufacturer_name,
            'shipments' => $this->getProductShipments($product),
            'available_quantity' => $product->quantity,
            'minimum_orderable_quantity' => $product->minimal_quantity,
            'outstock_message' => $product->available_later,
            'instock_message' => $product->available_now,
            //'promotional_message' => '',
            'is_orderable_outstock' => ($product->out_of_stock == 1 || ($product->out_of_stock == 2 && Configuration::get('PS_ORDER_OUT_OF_STOCK') == 1) ? true : false),
            //'cpa' => 0,
            'images' => $this->getProductImages($product),
            'informations' => $this->getProductInformations($product),
            'skus' => $this->getProductSkus($product),
        );
    }

    /**
     * Get categories and main category
     * @param ProductCore $product
     * @return array
     */
    public function getProductCategories($product)
    {
        // Get product categories ID
        $product_categories = $product->getCategories();

        // Build categories
        $main_category = array();
        $categories = array();
        foreach ($product_categories as $id_category) {

            // Retrieve names for category
            $category = new Category($id_category);
            $c = array('titles' => array(), 'reference' => $id_category);
            foreach ($this->languages as $iso_code => $id_lang) {
                if (isset($category->name[$id_lang])) {
                    $c['titles'][] = array(
                        'name' => $category->name[$id_lang],
                        'language' => $iso_code,
                    );
                }
            }

            // Merge result and set main category
            $categories[] = $c;
            if ($product->id_category_default == $category->id) {
                $main_category = $c;
            }
        }

        return array($main_category, $categories);
    }

    /**
     * Get available shipments for products (up to 10 quantity)
     * @param ProductCore $product
     * return array $shipments
     */
    public function getProductShipments($product)
    {

    }

    /**
     * Get product images
     * @param ProductCore $product
     * return array $images
     */
    public function getProductImages($product)
    {
        $images = array();
        $product_images = $product->getImages($this->context->language->id);

        foreach ($product_images as $product_image) {
            $images[] = $this->context->link->getImageLink('product', $product_image['id_image'], 'thickbox_default');
        }

        return $images;
    }

    /**
     * Get product images
     * @param ProductCore $product
     * return array $informations
     */
    public function getProductInformations($product)
    {
        $informations = array();
        $product_informations = $product->getFeatures();

        foreach ($product_informations as $product_information) {
            $feature = new Feature($product_information['id_feature'], $this->context->language->id);
            $feature_value = new FeatureValue($product_information['id_feature_value'], $this->context->language->id);
            $informations[$feature->name] = $feature_value->value;
        }

        return $informations;
    }


    /**
     * Get product images
     * @param ProductCore $product
     * return array $skus
     */
    public function getProductSkus($product)
    {
    }
}
