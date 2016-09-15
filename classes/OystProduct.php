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

class OystProduct
{
    public $context;
    public $languages;
    public $countries;
    public $carriers;
    public $customer;
    public $address;
    public $tax_rates;

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

        // Load countries
        $this->countries = array(
            new Country(Country::getByIso('FR'), $this->context->language->id),
        );

        // Load tax rates
        $tax_rules_groups = TaxRulesGroup::getTaxRulesGroups();
        $this->tax_rates = array();
        foreach ($tax_rules_groups as $tax_rules_group) {
            $tax_rules = TaxRule::getTaxRulesByGroupId($this->context->language->id, $tax_rules_group['id_tax_rules_group']);
            if (!empty($tax_rules)) {
                foreach ($tax_rules as $tax_rule) {
                    $tax_rule['rate'] = $this->ceilValue($tax_rule['rate']);
                    $this->tax_rates[$tax_rules_group['id_tax_rules_group']][$tax_rule['id_country']] = $tax_rule;
                }
            }
        }

        // Load customer
        $this->context->customer = new Customer(Configuration::get('FC_OYST_CUSTOMER_CONFIG'));
        if (!Validate::isLoadedObject($this->context->customer)) {
            $this->context->customer = new Customer();
            $this->context->customer->active = 1;
            $this->context->customer->firstname = 'Oyst';
            $this->context->customer->lastname = 'Oyst';
            $this->context->customer->email = Configuration::get('PS_SHOP_EMAIL');
            $this->context->customer->passwd = md5('Oyst');
            $this->context->customer->add();
            Configuration::updateValue('FC_OYST_CUSTOMER_CONFIG', $this->context->customer->id);
        }

        // Load address
        $this->address = new Address(Configuration::get('FC_OYST_ADDRESS_CONFIG'));
        if (!Validate::isLoadedObject($this->address)) {
            $this->address = new Address();
            $this->address->id_customer = $this->context->customer->id;
            $this->address->id_country = Country::getByIso('FR');
            $this->address->firstname = 'Oyst';
            $this->address->lastname = 'Oyst';
            $this->address->alias = 'Oyst';
            $this->address->address1 = 'Oyst';
            $this->address->city = 'Oyst';
            $this->address->add();
            Configuration::updateValue('FC_OYST_ADDRESS_CONFIG', $this->address->id);
        }

        // Load cart
        $this->context->cart = new Cart(Configuration::get('FC_OYST_CART_CONFIG'));
        if (!Validate::isLoadedObject($this->context->cart)) {
            $this->context->cart = new Cart();
            $this->context->cart->id_currency = $this->context->currency->id;
            $this->context->cart->id_customer = $this->context->customer->id;
            $this->context->cart->add();
            Configuration::updateValue('FC_OYST_CART_CONFIG', $this->context->cart->id);
        }
    }


    /**
     * Send catalog to Oyst
     * @param integer $start
     * @param integer $limit
     * @return array API result
     */
    public function sendCatalog($start = 0, $limit = 0)
    {
        // Init
        $count = 1;
        $products = array();

        // Get products
        $result = $this->getProductsRequest(false, $start, $limit);
        while ($row = Db::getInstance()->nextRow($result)) {
            $products[] = $this->getProductData($row['id_product']);
            if (php_sapi_name() == "cli") {
                echo ($count++)." product(s)     \r";
            }
        }

        // Export products
        $oyst_api = new OystSDK();
        $oyst_api->setApiPaymentEndpoint(Configuration::get('FC_OYST_API_EXPORT_ENDPOINT'));
        $oyst_api->setApiKey(Configuration::get('FC_OYST_API_KEY'));
        return $oyst_api->productPostRequest($products);
    }

    /**
     * Make products MySQL request
     * @param boolean $count
     * @param integer $start
     * @param integer $limit
     * @return mysql ressource
     */
    public function getProductsRequest($count = false, $start = 0, $limit = 0)
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
        $sql = 'SELECT '.($count ? 'COUNT(DISTINCT(' : '').'p.`id_product`'.($count ? '))' : '').'
                FROM `'._DB_PREFIX_.'product` p
                '.Shop::addSqlAssociation('product', 'p').'
                WHERE product_shop.`id_shop` = '.(int)$context->shop->id.'
                AND product_shop.`visibility` IN ("both", "catalog")
                AND (
                    p.`active` = 1 OR
                    p.`date_upd` > \''.pSQL(date('Y-m-d', strtotime('-7 days'))).'\'
                ) '.$where.'
                '.(!$count ? 'GROUP BY product_shop.id_product' : '').'
                '.$limitSQL;

        // Return query
        if ($count) {
            return Db::getInstance()->getValue($sql);
        }
        return Db::getInstance()->query($sql);
    }




    /**
     * Convert product data for Oyst Webservice
     * @param integer $id_product
     * @return array $product (oyst format)
     */
    public function getProductData($id_product)
    {
        // Load product, skus and associated categories
        $product = new Product($id_product, true, $this->context->language->id);
        $skus = $this->getProductSkus($product);
        list($main_category, $categories) = $this->getProductCategories($product);
        $tags = $product->getTags($this->context->language->id);
        if (!empty($tags)) {
            $tags = explode(', ', $tags);
        }

        // Build product
        $product = array(
            'reference' => 'ID '.$product->id,
            'merchant_reference' => $product->reference,
            'is_active' => ($product->active == 1 ? true : false),
            'is_materialized' => ($product->is_virtual == 1 ? true : false),
            'title' => $product->name,
            'condition' => ($product->condition == 'used' ? 'reused' : $product->condition),
            'short_description' => $product->description_short,
            'description' => $product->description,
            'tags' => $tags,
            'amount_excluding_taxes' => array(
                'value' => $this->ceilValue(Product::getPriceStatic($product->id, false, null, 2, null, false, false)),
                'currency' => $this->context->currency->iso_code,
            ),
            'amount_including_taxes' => array(
                'value' => $this->ceilValue(Product::getPriceStatic($product->id, true, null, 2, null, false, false)),
                'currency' => $this->context->currency->iso_code,
            ),
            'sale_amount_excluding_taxes' => array(
                'value' => $this->ceilValue(Product::getPriceStatic($product->id, false, null, 2)),
                'currency' => $this->context->currency->iso_code,
            ),
            'sale_amount_including_taxes' => array(
                'value' => $this->ceilValue(Product::getPriceStatic($product->id, true, null, 2)),
                'currency' => $this->context->currency->iso_code,
            ),
            'vat' => $this->ceilValue($product->tax_rate),
            'meta' => array(
                'title' => $product->meta_title,
                'description' => $product->meta_description,
            ),
            'url' => $this->context->link->getProductLink($product->id),
            'categories' => $categories,
            'category' => $main_category,
            'manufacturer' => $product->manufacturer_name,
            'shipments' => (empty($skus) ? $this->getProductShipments($product) : array()),
            'available_quantity' => $product->quantity,
            'minimum_orderable_quantity' => $product->minimal_quantity,
            'outstock_message' => $product->available_later,
            'instock_message' => $product->available_now,
            //'promotional_message' => '',
            'is_orderable_outstock' => ($product->out_of_stock == 1 || ($product->out_of_stock == 2 && Configuration::get('PS_ORDER_OUT_OF_STOCK') == 1) ? true : false),
            //'cpa' => 0,
            'images' => $this->getProductImages($product),
            'informations' => $this->getProductInformations($product),
            'skus' => $skus,
        );

        return $this->cleanData($product);
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
        // Init
        $shipments = array();

        // Empty cart
        foreach ($this->context->cart->getProducts() as $p) {
            $this->context->cart->updateQty(- $p['cart_quantity'], $p['id_product'], $p['id_product_attribute']);
            $this->context->cart->update();

        }

        // Loop on quantity
        for ($i = 1; $i <= 10; $i++) {

            // Loop on countries
            foreach ($this->countries as $country) {

                // Update address
                if ($this->address->id_country != $country->id) {
                    $this->address->id_country = $country->id;
                    $this->address->update();
                }

                // Update country and quantity
                $this->context->cart->id_customer = $this->context->customer->id;
                $this->context->cart->id_lang = $this->context->language->id;
                $this->context->cart->id_currency = $this->context->currency->id;
                $this->context->cart->id_address_delivery = $this->address->id;
                $this->context->cart->id_address_invoice = $this->address->id;
                $this->context->cart->updateQty(1, $product->id, $id_product_attribute);
                $this->context->cart->update();

                // Get shipping cost for each carrier
                $delivery_options = $this->context->cart->getDeliveryOptionList();
                foreach ($delivery_options[$this->address->id] as $delivery_option) {
                    foreach ($delivery_option['carrier_list'] as $id_carrier => $carrier) {

                        // Get id tax rules group
                        $id_tax_rules_group = $carrier['instance']->id_tax_rules_group;

                        // Get shipping rate
                        $shipping_tax_rate = 0;
                        if (isset($this->tax_rates[$id_tax_rules_group][$this->address->id_country])) {
                            $shipping_tax_rate = $this->tax_rates[$id_tax_rules_group][$this->address->id_country];
                        }

                        // Build shipments
                        $shipments[] = array(
                            'area' => $country->name,
                            'carrier' => $carrier['instance']->name,
                            'delay' => (int)$carrier['instance']->grade,
                            'method' => $carrier['instance']->name,
                            'quantity' => $i,
                            'shipment_amount' => array(
                                'value' => $this->ceilValue($carrier['price_with_tax']),
                                'currency' => $this->context->currency->iso_code,
                            ),
                            'vat' => $shipping_tax_rate['rate'],
                        );
                    }
                }
            }
        }

        return $shipments;
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
            $image_link = $this->context->link->getImageLink('product', $product_image['id_image'], 'thickbox_default');
            $image_link = str_replace(__PS_BASE_URI__, $this->context->shop->physical_uri, $image_link);
            $images[] = array('url' => $image_link);
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
        if (empty($attribute_combinations)) {
            return array();
        }

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
            $skus[] = $this->cleanData(array(
                'reference' => (!empty($sku['reference']) ? $sku['reference'] : 'IDPA '.$id_product_attribute),
                'title' => $product->name.' - '.implode(' - ', $sku['attributes_values']),
                'amount_excluding_taxes' => array(
                    'value' => $this->ceilValue(Product::getPriceStatic($product->id, false, $id_product_attribute, 2, null, false, false)),
                    'currency' => $this->context->currency->iso_code,
                ),
                'amount_including_taxes' => array(
                    'value' => $this->ceilValue(Product::getPriceStatic($product->id, true, $id_product_attribute, 2, null, false, false)),
                    'currency' => $this->context->currency->iso_code,
                ),
                'sale_amount_excluding_taxes' => array(
                    'value' => $this->ceilValue(Product::getPriceStatic($product->id, false, $id_product_attribute, 2)),
                    'currency' => $this->context->currency->iso_code,
                ),
                'sale_amount_including_taxes' => array(
                    'value' => $this->ceilValue(Product::getPriceStatic($product->id, true, $id_product_attribute, 2)),
                    'currency' => $this->context->currency->iso_code,
                ),
                'vat' => $this->ceilValue($product->tax_rate),
                'available_quantity' => $sku['quantity'],
                'weight' => $sku['weight'],
                'minimum_orderable_quantity' => $sku['minimal_quantity'],
                'shipments' => $this->getProductShipments($product, $id_product_attribute),
                'images' => $this->getProductImages($product, $id_product_attribute),
                'ean' => $sku['ean13'],
                'upc' => $sku['upc'],
            ));
        }

        return $skus;
    }

    public function ceilValue($value)
    {
        return ceil($value * 100);
    }

    public function cleanData($data)
    {
        // Unset empty value
        foreach ($data as $field => $value) {
            if (!is_array($value) && !is_integer($value)) {
                if (empty($value) || !$value) {
                    unset($data[$field]);
                }
            }
            if (is_array($value) && empty($value)) {
                unset($data[$field]);
            }
        }

        return $data;
    }
}