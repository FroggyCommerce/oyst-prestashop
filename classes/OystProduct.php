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
if (!defined('_PS_VERSION_')) {
    exit;
}

class OystProduct
{
    public $context;
    public $languages;

    /**
     * OystExportCatalogModuleCronController constructor.
     */
    public function __construct()
    {
        // Set context
        $this->context = Context::getContext();
        if (!isset($this->context->currency) || !Validate::isLoadedObject($this->context->currency)) {
            $this->context->currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        }
        $this->context->shop = new Shop($this->context->shop->id);

        // Set languages
        $languages = Language::getLanguages(true);
        foreach ($languages as $language) {
            $this->languages[$language['iso_code']] = $language['id_lang'];
        }
    }

    /**
     * Make products MySQL request
     * @param integer $id_lang
     * @param integer $start
     * @param integer $limit
     * @return mysql ressource
     */
    public function getProductsRequest($start = 0, $limit = 0)
    {
        // Retrieve context
        $context = Context::getContext();

        // Init
        $limitSQL = '';
        if ((int)$start > 0 && (int)$limit > 0)
            $limitSQL = ' LIMIT '.(int)$start.','.(int)$limit;

        $where = '';
        if (Configuration::get('OYST_EXPORT_ALL') == 'no')
        {
            $categories = json_decode(Configuration::get('OYST_EXPORT_CATEGORIES'), true);
            if (empty($categories))
                $categories[] = 0;
            foreach ($categories as $kc => $vc)
                $categories[(int)$kc] = (int)$vc;
            $where = ' AND p.`id_product` IN (SELECT `id_product` FROM `'._DB_PREFIX_.'category_product` WHERE `id_category` IN ('.implode(',', $categories).'))';
        }

        // SQL request
        $sql = 'SELECT p.`id_product`
                FROM `'._DB_PREFIX_.'product` p
                '.Shop::addSqlAssociation('product', 'p').'
                WHERE product_shop.`id_shop` = '.(int)$context->shop->id.'
                AND product_shop.`visibility` IN ("both", "catalog")
                AND (
                    p.`active` = 1 OR
                    p.`date_upd` > \''.pSQL(date('Y-m-d', strtotime('-7 days'))).'\'
                ) '.$where.'
                GROUP BY product_shop.id_product '.$limitSQL;

        // Return query
        return Db::getInstance()->query($sql);
    }




    /**
     * Convert product data for Oyst Webservice
     * @param integer $id_product
     * @return array $product (oyst format)
     */
    public function getProductData($id_product)
    {
        // Load product and associated categories
        $product = new Product($id_product, true, $this->context->language->id);
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
            'tags' => explode(', ', $product->getTags($this->context->language->id)),
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
            'vat' => ($product->tax_rate * 100),
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
    public function getProductShipments($product, $id_product_attribute = null)
    {

    }

    /**
     * Get product images
     * @param ProductCore $product
     * return array $images
     */
    public function getProductImages($product, $id_product_attribute = null)
    {
        // Init variables
        $images = array();

        // If combinations, get combinations images
        if ($id_product_attribute > 0) {
            $tmp = $product->getCombinationImages($this->context->language->id);
            if (isset($tmp[$id_product_attribute])) {
                $product_images = $tmp[$id_product_attribute];
            }

        }

        // If no images retrieved, get images for product
        if (!isset($product_images)) {
            $product_images = $product->getImages($this->context->language->id);
        }

        // Build images array
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
        $skus = array();
        $combinations = array();
        $attribute_combinations = $product->getAttributeCombinations($this->context->language->id);

        foreach ($attribute_combinations as $row) {
            $combinations[$row['id_product_attribute']]['reference'] = $row['reference'];
            $combinations[$row['id_product_attribute']]['attributes_values'][$row['id_attribute_group']] = $row['group_name'].' '.$row['attribute_name'];
            $combinations[$row['id_product_attribute']]['weight'] = $row['weight'];
            $combinations[$row['id_product_attribute']]['ean13'] = $row['ean13'];
            $combinations[$row['id_product_attribute']]['upc'] = $row['upc'];
            $combinations[$row['id_product_attribute']]['quantity'] = $row['quantity'];
            $combinations[$row['id_product_attribute']]['minimal_quantity'] = $row['minimal_quantity'];
        }

        foreach ($combinations as $id_product_attribute => $sku) {
            $skus[] = array(
                'reference' => $sku['reference'],
                'title' => $product->name.' - '.implode(' - ', $sku['attributes_values']),
                'amount_excluding_taxes' => array(
                    'value' => Product::getPriceStatic($product->id, false, $id_product_attribute, 2, null, false, false),
                    'currency' => $this->context->currency->iso_code,
                ),
                'amount_including_taxes' => array(
                    'value' => Product::getPriceStatic($product->id, true, $id_product_attribute, 2, null, false, false),
                    'currency' => $this->context->currency->iso_code,
                ),
                'sale_amount_excluding_taxes' => array(
                    'value' => Product::getPriceStatic($product->id, false, $id_product_attribute, 2),
                    'currency' => $this->context->currency->iso_code,
                ),
                'sale_amount_including_taxes' => array(
                    'value' => Product::getPriceStatic($product->id, true, $id_product_attribute, 2),
                    'currency' => $this->context->currency->iso_code,
                ),
                'vat' => ($product->tax_rate * 100),
                'available_quantity' => $sku['quantity'],
                'weight' => $sku['weight'],
                'minimum_orderable_quantity' => $sku['minimal_quantity'],
                'shipments' => $this->getProductShipments($product, $id_product_attribute),
                'images' => $this->getProductImages($product, $id_product_attribute),
                'ean' => $sku['ean13'],
                'upc' => $sku['upc'],
            );
        }

        return $skus;
    }
}