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

class OystHookDisplayPaymentProcessor extends FroggyHookProcessor
{
    public function run()
    {
        if (Configuration::get('FC_OYST_PAYMENT_FEATURE') != 1) {
            return '';
        }

        $assign = array();
        $assign['module_dir'] = $this->path;
        $this->smarty->assign($this->module->name, $assign);
        return $this->module->fcdisplay(__FILE__, 'displayPayment.tpl');
    }
}
