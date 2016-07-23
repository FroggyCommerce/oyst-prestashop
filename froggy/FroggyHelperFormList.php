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

class FroggyHelperFormList
{
    private $configuration;
    private $form_url;
    private $module;
    private $context;

    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
    }

    public function setFormUrl($form_url)
    {
        $this->form_url = $form_url;
    }

    public function setModule($module)
    {
        $this->module = $module;
    }

    public function setContext($context)
    {
        $this->context = $context;
    }

    public function prefillFormFields()
    {
        if (isset($this->configuration['form'])) {
            foreach ($this->configuration['form'] as $key_section => $sections) {
                foreach ($sections['fields'] as $key_field => $field) {
                    if (!isset($field['prefill'])) {
                        $this->configuration['form'][$key_section]['fields'][$key_field]['value'] = Tools::getValue($field['name'], (isset($field['default_value']) ? $field['default_value'] : ''));
                    }
                }
            }
        }

        if (isset($this->configuration['list']['data_request'])) {
            $this->configuration['list']['data'] = Db::getInstance()->executeS($this->configuration['list']['data_request']);
        }
    }

    public function render()
    {
        $assign = array(
            'module_name' => $this->module->name,
            'configuration' => $this->configuration,
            'form_url' => $this->form_url,
            'templates_dir' => dirname(__FILE__).'/../../'.$this->module->name.'/views/templates/hook/helpers',
        );

        $this->context->smarty->assign('froggyhelper', $assign);
        return $this->module->fcdisplay(dirname(__FILE__).'/../../'.$this->module->name, 'helpers/helper.tpl');
    }
}
