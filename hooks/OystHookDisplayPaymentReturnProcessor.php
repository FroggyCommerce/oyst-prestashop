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

class OystHookDisplayPaymentReturnProcessor extends FroggyHookProcessor
{
    public function run()
    {
        // Check if payment is enabled
        if (Configuration::get('FC_OYST_PAYMENT_FEATURE') != 1) {
            return '';
        }

        // Load data
        $id_cart = (int)Tools::getValue('id_cart');
        $id_order = Order::getOrderByCartId($id_cart);
        $order = new Order($id_order);
        $transaction_id = Db::getInstance()->getValue('
        SELECT `payment_id` FROM `'._DB_PREFIX_.'oyst_payment_notification`
        WHERE `id_cart` = '.(int)$id_cart);

        // Security check
        if ($order->secure_key != Tools::getValue('key')) {
            die('Secure key is invalid');
        }

        // Assign data
        $assign = array(
            'order_reference' => $order->reference,
            'transaction_id' => $transaction_id,
        );
        $this->smarty->assign($this->module->name, $assign);
        return $this->module->fcdisplay(__FILE__, 'displayPaymentReturn.tpl');
    }
}
