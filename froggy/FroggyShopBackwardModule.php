<?php
/**
 * 2013-2016 Froggy Commerce
 *
 * NOTICE OF LICENSE
 *
 * You should have received a licence with this module.
 * If you didn't buy this module on Froggy-Commerce.com, ThemeForest.net
 * or Addons.PrestaShop.com, please contact us immediately : contact@froggy-commerce.com
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to benefit the updates
 * for newer PrestaShop versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Froggy Commerce <contact@froggy-commerce.com>
 * @copyright 2013-2016 Froggy Commerce / 23Prod
 * @license   Unauthorized copying of this file, via any medium is strictly prohibited
 */

/*
 * Security
 */
defined('_PS_VERSION_') || require dirname(__FILE__).'/index.php';

/**
 * Class Shop for Backward compatibility
 */

class FroggyShopBackwardModule extends Shop
{
    const CONTEXT_ALL = 1;

    public $id = 1;
    public $id_shop_group = 1;
    public $physical_uri = __PS_BASE_URI__;


    public function getContextType()
    {
        return FroggyShopBackwardModule::CONTEXT_ALL;
    }

    public function setContext($var)
    {
        return true;
    }

    /**
     * Simulate shop for 1.3 / 1.4
     */
    public function getID()
    {
        return 1;
    }

    /**
     * Get shop theme name
     *
     * @return string
     */
    public function getTheme()
    {
        return _THEME_NAME_;
    }

    public function isFeatureActive()
    {
        return false;
    }
}
