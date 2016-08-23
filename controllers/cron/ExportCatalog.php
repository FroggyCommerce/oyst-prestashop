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

    public function convertProduct($row)
    {
        // Load product and product categories
        $product = new Product($row['id_product'], $this->context->language->id);
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

        // Build product
        return array(
            'reference' => $row['id_product'],
            'merchant_reference' => $row['reference'],
            'is_active' => $row['active'],
            'is_materialized' => false,
            'title' => $row['name'],
            'condition' => ($row['condition'] == 'used' ? 'reused' : $row['condition']),
            'short_description' => $row['description_short'],
            'tags' => OystProduct::getProductTags($row['id_product'], $this->context->language->id),
            'amount_excluding_taxes' => array(
                'value' => Product::getPriceStatic($row['id_product'], false, null, 2, null, false, false),
                'currency' => $this->context->currency->iso_code,
            ),
            'amount_including_taxes' => array(
                'value' => Product::getPriceStatic($row['id_product'], true, null, 2, null, false, false),
                'currency' => $this->context->currency->iso_code,
            ),
            'sale_amount_excluding_taxes' => array(
                'value' => $row['price'] = Product::getPriceStatic($row['id_product'], false, null, 2),
                'currency' => $this->context->currency->iso_code,
            ),
            'sale_amount_including_taxes' => array(
                'value' => $row['price'] = Product::getPriceStatic($row['id_product'], true, null, 2),
                'currency' => $this->context->currency->iso_code,
            ),
            'meta' => array(
                'title' => $row['meta_title'],
                'description' => $row['meta_description'],
            ),
            'url' => $this->context->link->getProductLink($row['id_product']),
            'categories' => $categories,
            'category' => $main_category,
        );
    }

    public function run()
    {
        // Get products
        $result = OystProduct::getProductsRequest($this->context->language->id);
        while ($row = Db::getInstance()->nextRow($result)) {
            $product = $this->convertProduct($row);
            d($product);
        }

    }
}
