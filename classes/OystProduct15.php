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
    /**
     * Make products MySQL request
     * @param integer $id_lang
     * @param integer $start
     * @param integer $limit
     * @return mysql ressource
     */
    public static function getProductsRequest($id_lang, $start = 0, $limit = 0)
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
     * Get product declinations
     * @param integer $id_product
     * @param integer $id_lang
     * @return boolean|array
     */
    public static function getProductDeclinations($id_product, $id_lang)
    {
        if (!Combination::isFeatureActive())
            return false;

        $sql = 'SELECT ag.`id_attribute_group`, ag.`is_color_group`, agl.`name` AS group_name, agl.`public_name` AS public_group_name,
                    a.`id_attribute`, al.`name` AS attribute_name, a.`color` AS attribute_color, pa.`id_product_attribute`,
                    IFNULL(stock.quantity, 0) as quantity, product_attribute_shop.`price`, product_attribute_shop.`ecotax`, pa.`weight`,
                    product_attribute_shop.`default_on`, pa.`reference`, pa.`ean13`, pa.`upc`, product_attribute_shop.`unit_price_impact`,
                    product_attribute_shop.`wholesale_price`,
                    pa.`minimal_quantity`, pa.`available_date`, ag.`group_type`, pa.`location`
                FROM `'._DB_PREFIX_.'product_attribute` pa
                '.Shop::addSqlAssociation('product_attribute', 'pa').'
                '.Product::sqlStock('pa', 'pa').'
                LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON pac.`id_product_attribute` = pa.`id_product_attribute`
                LEFT JOIN `'._DB_PREFIX_.'attribute` a ON a.`id_attribute` = pac.`id_attribute`
                LEFT JOIN `'._DB_PREFIX_.'attribute_group` ag ON ag.`id_attribute_group` = a.`id_attribute_group`
                LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON a.`id_attribute` = al.`id_attribute`
                LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON ag.`id_attribute_group` = agl.`id_attribute_group`
                '.Shop::addSqlAssociation('attribute', 'a').'
                WHERE pa.`id_product` = '.(int)$id_product.'
                    AND al.`id_lang` = '.(int)$id_lang.'
                    AND agl.`id_lang` = '.(int)$id_lang.'
                GROUP BY id_attribute_group, id_product_attribute
                ORDER BY ag.`position` ASC, a.`position` ASC, agl.`name` ASC';
        $attributes_groups = Db::getInstance()->executeS($sql);

        $combinations = false;
        if (is_array($attributes_groups) && $attributes_groups)
        {
            // Retrieve context
            $context = Context::getContext();
            if (!isset($context->link))
                $context->link = new Link();

            // Retrieve images corresponding to each declination
            $ids = array();
            $images = array();
            foreach ($attributes_groups as $pa)
                $ids[] = (int)$pa['id_product_attribute'];
            $result = Db::getInstance()->executeS('
            SELECT pai.`id_image`, pai.`id_product_attribute`
            FROM `'._DB_PREFIX_.'product_attribute_image` pai
            LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_image` = pai.`id_image`)
            WHERE pai.`id_product_attribute` IN ('.implode(', ', $ids).') ORDER by i.`position`');
            foreach ($result as $row)
                if ($row['id_image'] > 0)
                    $images[$row['id_product_attribute']][] = $context->link->getImageLink('product', $row['id_image'], 'thickbox_default');

            // Retrieve infos for each declination
            foreach ($attributes_groups as $k => $row)
            {
                $combinations[$row['id_product_attribute']]['attributes_values'][$row['id_attribute_group']] = $row['attribute_name'];
                $combinations[$row['id_product_attribute']]['price'] = (float)$row['price'];
                $combinations[$row['id_product_attribute']]['ecotax'] = (float)$row['ecotax'];
                $combinations[$row['id_product_attribute']]['weight'] = (float)$row['weight'];
                $combinations[$row['id_product_attribute']]['quantity'] = (int)$row['quantity'];
                $combinations[$row['id_product_attribute']]['reference'] = $row['reference'];
                $combinations[$row['id_product_attribute']]['ean13'] = $row['ean13'];
                $combinations[$row['id_product_attribute']]['upc'] = $row['upc'];
                $combinations[$row['id_product_attribute']]['unit_impact'] = $row['unit_price_impact'];
                $combinations[$row['id_product_attribute']]['wholesale_price'] = $row['wholesale_price'];
                $combinations[$row['id_product_attribute']]['location'] = $row['location'];
                if (isset($images[$row['id_product_attribute']]))
                    $combinations[$row['id_product_attribute']]['images'] = $images[$row['id_product_attribute']];

                if (empty($combinations[$row['id_product_attribute']]['location'])) {
                    $combinations[$row['id_product_attribute']]['location'] = Db::getInstance()->getValue('
                        SELECT `location`
                        FROM `'._DB_PREFIX_.'warehouse_product_location`
                        WHERE `id_product` = '.(int)$id_product.'
                        AND `id_product_attribute` = '.(int)$row['id_product_attribute'].'
                        AND `location` != \'\'
                    ');
                }
            }
        }
        return $combinations;
    }

    /**
     * Get product tags
     * @param integer $id_product
     * @param integer $id_lang
     * @return string $tags
     */
    public static function getProductTags($id_product, $id_lang)
    {
        $sql = 'SELECT t.`name` FROM `'._DB_PREFIX_.'product_tag` pt
                LEFT JOIN `'._DB_PREFIX_.'tag` t ON (t.`id_tag` = pt.`id_tag` AND t.`id_lang` = '.(int)$id_lang.')
                WHERE pt.`id_product` = '.(int)$id_product;
        $tags_list = Db::getInstance()->executeS($sql);

        $tags = array();
        foreach ($tags_list as $t) {
            if (!empty($t['name']))
                $tags[] = $t['name'];
        }

        return $tags;
    }


}

