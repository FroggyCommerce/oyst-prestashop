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
 * Backward function compatibility
 * Need to be called for each module in 1.4
 */

/**
 * Get out if the context is already defined
 */
if (!in_array('FroggyContext', get_declared_classes())) {
    require_once(dirname(__FILE__).'/FroggyContext.php');
}

/**
 * If not under an object we don't have to set the context
 */
$var = 'this';
if (!isset($$var)) {
    return;
}

/**
 * Set variables
 */
$$var->context = FroggyContext::getContext();
$$var->smarty = $$var->context->smarty;
