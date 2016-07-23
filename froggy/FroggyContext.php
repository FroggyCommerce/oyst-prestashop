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

require_once(dirname(__FILE__).'/FroggyControllerBackwardModule.php');
require_once(dirname(__FILE__).'/FroggyCustomerBackwardModule.php');
require_once(dirname(__FILE__).'/FroggyShopBackwardModule.php');

class FroggyContext
{
    /**
     * @var Context
     */
    protected static $instance;

    /**
     * @var Cart
     */
    public $cart;

    /**
     * @var Customer
     */
    public $customer;

    /**
     * @var Cookie
     */
    public $cookie;

    /**
     * @var Link
     */
    public $link;

    /**
     * @var Country
     */
    public $country;

    /**
     * @var Employee
     */
    public $employee;

    /**
     * @var Controller
     */
    public $controller;

    /**
     * @var Language
     */
    public $language;

    /**
     * @var Currency
     */
    public $currency;

    /**
     * @var AdminTab
     */
    public $tab;

    /**
     * @var Shop
     */
    public $shop;

    /**
     * @var Smarty
     */
    public $smarty;

    /**
     * @var boolean|string mobile device of the customer
     */
    protected $mobile_device;

    public function __construct()
    {
        $this->tab = null;

        $this->cookie = (isset($GLOBALS['cookie']) ? $GLOBALS['cookie'] : null);
        $this->cart = (isset($GLOBALS['cart']) ? $GLOBALS['cart'] : null);
        $this->smarty = (isset($GLOBALS['smarty']) ? $GLOBALS['smarty'] : null);
        $this->link = (isset($GLOBALS['link']) ? $GLOBALS['link'] : null);

        $this->controller = new FroggyControllerBackwardModule();
        if (is_object($this->cookie)) {
            $this->currency = new Currency((int)$this->cookie->id_currency);
            $this->language = new Language((int)$this->cookie->id_lang);
            $this->country = new Country((int)$this->cookie->id_country);
            $this->customer = new FroggyCustomerBackwardModule((int)$this->cookie->id_customer);
            $this->employee = new Employee((int)$this->cookie->id_employee);
        } else {
            $this->currency = null;
            $this->language = null;
            $this->country = null;
            $this->customer = null;
            $this->employee = null;
        }
        if (!Validate::isLoadedObject($this->currency)) {
            $this->currency = new Currency((int)Configuration::get('PS_CURRENCY_DEFAULT'));
        }

        $this->shop = new FroggyShopBackwardModule();
    }


    /**
     * Get a singleton context
     *
     * @return Context
     */
    public static function getContext()
    {
        if (!isset(self::$instance)) {
            self::$instance = new FroggyContext();
        }
        return self::$instance;
    }

    /**
     * Clone current context
     *
     * @return Context
     */
    public function cloneContext()
    {
        return clone($this);
    }

    /**
     * @return int Shop context type (Shop::CONTEXT_ALL, etc.)
     */
    public static function shop()
    {
        if (!self::$instance->shop->getContextType()) {
            return FroggyShopBackwardModule::CONTEXT_ALL;
        }
        return self::$instance->shop->getContextType();
    }
}
