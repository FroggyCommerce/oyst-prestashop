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

class OystPaymentReturnModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function initContent()
    {
        parent::initContent();

        // Build urls and amount
        $glue = '&';
        if (Configuration::get('PS_REWRITING_SETTINGS') == 1) {
            $glue = '?';
        }
        $url = $this->context->link->getPageLink('order-confirmation').$glue.'id_cart='.$this->context->cart->id.'&id_module='.Module::getModuleIdByName('oyst').'&key='.$this->context->customer->secure_key;

        // Load cart and order
        $id_cart = (int)Tools::getValue('id_cart');
        $id_order = Order::getOrderByCartId($id_cart);
        $order = new Order($id_order);

        if (Validate::isLoadedObject($order)) {
            Tools::redirect($url);
        }

        $this->setTemplate('return'.(version_compare(_PS_VERSION_, '1.6.0') ? '.bootstrap' : '').'.tpl');
    }
}
