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

require_once(dirname(__FILE__).'/FroggyHookProcessor.php');
require_once(dirname(__FILE__).'/FroggyDefinitionsModuleParser.php');

class FroggyModule extends Module
{

    /**
     * @var mixed
     */
    protected $definitions_elements = array('hooks', 'configurations', 'controllers', 'sql');

    /**
     * Construct for module
     */
    public function __construct()
    {
        // Set name with ClassName of module
        $this->name = Tools::strtolower(get_class($this));

        // Get definition file content
        $parser = new FroggyDefinitionsModuleParser(_PS_MODULE_DIR_.$this->name.'/definitions.json');
        $definitions = $parser->parse();

        $this->tab = isset($definitions['tab']) ? $definitions['tab'] : null;
        $this->version = isset($definitions['version']) ? $definitions['version'] : 1;
        $this->need_instance = isset($definitions['need_instance']) ? $definitions['need_instance'] : 0;

        $this->author = 'Froggy Commerce';

        // If PS 1.6 or greater, we enable bootstrap
        if (version_compare(_PS_VERSION_, '1.6.0') >= 0) {
            $this->bootstrap = true;
        }

        parent::__construct();

        foreach ($this->definitions_elements as $key) {
            if (isset($definitions[$key])) {
                $this->$key = $definitions[$key];
            }
        }

        // If PS version is lower than 1.5, call backward script
        if (version_compare(_PS_VERSION_, '1.5') < 0) {
            require(dirname(__FILE__).'/FroggyBackward.php');
            require_once(dirname(__FILE__).'/FroggyOverride.php');
        }
        require_once(dirname(__FILE__).'/FroggyHelperFormList.php');
        require_once(dirname(__FILE__).'/FroggyHelperTreeCategories.php');

        // Define local path if not exists (1.4 compatibility)
        if (!isset($this->local_path)) {
            $this->local_path = _PS_MODULE_DIR_.'/'.$this->name.'/';
        }

        // 1.4 retrocompatibility
        if (!isset($this->context->smarty_methods['FroggyGetAdminLink'])) {
            smartyRegisterFunction($this->context->smarty, 'function', 'FroggyGetAdminLink', 'FroggyGetAdminLink');
            $this->context->smarty_methods['FroggyGetAdminLink'] = true;
        }

        // Security function
        if (!isset($this->context->smarty_methods['FroggyDisplaySafeHtml'])) {
            smartyRegisterFunction($this->context->smarty, 'function', 'FroggyDisplaySafeHtml', 'FroggyDisplaySafeHtml');
            $this->context->smarty_methods['FroggyDisplaySafeHtml'] = true;
        }

        // Define module configuration url
        if (isset($this->context->employee->id)) {
            $this->configuration_url = 'index.php?tab=AdminModules&controller=AdminModules&token='.Tools::getAdminTokenLite('AdminModules').'&configure='.$this->name.'&module_name='.$this->name;
        }
    }

    /**
     * @param $method
     * @param $args
     * @return null
     */
    public function __call($method, $args)
    {
        // Fix for some server configuration (methods are in lowercase and server is case sensitive file for hook Processor)
        $prefix_call = array(
            'hookdisplay' => 'hookDisplay', 'hookdisplaybackoffice' => 'hookDisplayBackOffice', 'hookaction' => 'hookAction',
            'hookbackoffice' => 'hookBackOffice', 'hook' => 'hook',
        );
        foreach ($prefix_call as $prefix_search => $prefix_replace) {
            if (strpos($method, $prefix_search) !== false) {
                $method = $prefix_replace.Tools::ucfirst(str_replace($prefix_search, '', $method));
            }
        }

        // Check alternative hook method name for both method in main class and hook processor
        $hook_methods = array($method, str_replace('hook', 'hookDisplay', $method), str_replace('hook', 'hookAction', $method));
        foreach ($hook_methods as $method) {
            // Build name of class
            $processor_classname = get_class($this).Tools::ucfirst($method).'Processor';
            $processor_class_path = $this->local_path.'/hooks/'.$processor_classname.'.php';

            // Check if processor class exists
            if (file_exists($processor_class_path)) {
                require_once $processor_class_path;
                if (class_exists($processor_classname)) {
                    $args = array(
                        'module' => $this,
                        'context' => $this->context,
                        'smarty' => $this->smarty,
                        'path' => $this->_path,
                        'params' => array_pop($args),
                    );
                    $processor = new $processor_classname($args);
                    if ($processor instanceof FroggyHookProcessor) {
                        return $processor->run();
                    } else {
                        throw new Exception('Hook processor must extends "FroggyHookProcessor" class!');
                    }
                } else {
                    // If processor class not implement interface
                    throw new Exception('Hook processor cannot be used !');
                }
            }

            // Search for new hook name match
            if (method_exists($this, $method)) {
                return $this->{$method}(array_pop($args));
            }
        }

        return null;
    }

    /**
     * Method for module installation
     *
     * @return bool
     */
    public function install()
    {
        if (parent::install()) {
            if (class_exists('FroggyOverride') && !$this->installFroggyOverrides()) {
                return false;
            }

            if (!$this->registerDefinitionsHooks()) {
                return false;
            }

            if (!$this->registerDefinitionsConfigurations()) {
                return false;
            }

            if (!$this->registerDefinitionsControllers()) {
                return false;
            }

            if (!$this->runDefinitionsSql()) {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * Method for module uninstallation
     *
     * @return bool
     */
    public function uninstall()
    {
        if (parent::uninstall()) {

            if (class_exists('FroggyOverride') && !$this->uninstallFroggyOverrides()) {
                return false;
            }

            if (!$this->deleteConfigurations()) {
                return false;
            }

            if (!$this->deleteModuleControllers()) {
                return false;
            }

            if (!$this->runDefinitionsSql('uninstall')) {
                return false;
            }

            return true;
        }
        return false;
    }

    /**
     * Install overrides files for the module
     *
     * @return bool
     */
    public function installFroggyOverrides()
    {
        $fo = new FroggyOverride($this->name);
        try {
            return $fo->installOverrides();
        } catch (Exception $e) {
            $this->_errors[] = sprintf(Tools::displayError('Unable to install override: %s'), $e->getMessage());
            $fo->uninstallOverrides();
            return false;
        }
        return true;
    }

    /**
     * Uninstall overrides files for the module
     *
     * @return bool
     */
    public function uninstallFroggyOverrides()
    {
        $fo = new FroggyOverride($this->name);
        return $fo->uninstallOverrides();
    }

    /**
     * Enable module (and tabs = admin controllers access)
     *
     * @param bool $force_all Force enable
     * @return bool Result of enabling
     */
    public function enable($force_all = false)
    {
        if (version_compare(_PS_VERSION_, '1.5') >= 0) {
            Tab::enablingForModule($this->name);
        }
        return parent::enable($force_all);
    }

    /**
     * Disable module (and tabs = admin controllers access)
     *
     * @param bool $force_all Force enable
     * @return bool Result of disabling
     */
    public function disable($force_all = false)
    {
        if (version_compare(_PS_VERSION_, '1.5') >= 0) {
            Tab::disablingForModule($this->name);
        }
        return parent::disable($force_all);
    }

    /**
     * Register hooks of module
     *
     * @return bool
     */
    protected function registerDefinitionsHooks()
    {
        if (isset($this->hooks) && is_array($this->hooks)) {
            $versions = array_keys($this->hooks);
            $key = 0;
            foreach ($this->hooks as $version => $hooks) {
                if ($version == 'all' || (version_compare(_PS_VERSION_, $version) >= 0 && (!isset($versions[$key + 1]) || (isset($versions[$key + 1]) && version_compare(_PS_VERSION_, $versions[$key + 1]) < 0)))) {
                    foreach ($hooks as $hook) {
                        if (!$this->registerHook($hook)) {
                            return false;
                        }
                    }
                }
                $key++;
            }
        }
        return true;
    }

    /**
     * Register configuration for module
     *
     * @return bool
     */
    protected function registerDefinitionsConfigurations()
    {
        if (isset($this->configurations) && is_array($this->configurations)) {
            foreach ($this->configurations as $name => $value) {
                // In multilanguage case
                if (is_array($value)) {
                    $values = array();
                    foreach (Language::getLanguages(false) as $language) {
                        if (isset($value[$language['iso_code']])) {
                            $values[$language['id_lang']] = $value[$language['iso_code']];
                        } else {
                            $values[$language['id_lang']] = $value['default'];
                        }
                    }
                    $value = $values;
                }

                if (!Configuration::updateValue($name, $value)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Delete configurations of module
     *
     * @return bool
     */
    protected function deleteConfigurations()
    {
        if (isset($this->configurations) && is_array($this->configurations)) {
            foreach ($this->configurations as $name => $value) {
                if (!Configuration::deleteByName($name)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Install module controllers
     *
     * @return bool
     */
    protected function registerDefinitionsControllers()
    {
        if (isset($this->controllers) && is_array($this->controllers)) {
            foreach ($this->controllers as $controller) {
                $tab = new Tab();
                $tab->class_name = $controller['classname'];
                $tab->module = $this->name;
                $tab->id_parent = Tab::getIdFromClassName($controller['parent']);

                foreach (Language::getLanguages(false) as $language) {
                    if (isset($controller[$language['iso_code']])) {
                        $tab->name[$language['id_lang']] = $controller['name'][$language['iso_code']];
                    } else {
                        $tab->name[$language['id_lang']] = $controller['name']['default'];
                    }
                }

                if (!$tab->add()) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Uninstall module controllers
     *
     * @return bool
     */
    protected function deleteModuleControllers()
    {
        if (isset($this->controllers) && is_array($this->controllers)) {
            foreach ($this->controllers as $controller) {
                $id_tab = Tab::getIdFromClassName($controller['classname']);
                $tab = new Tab($id_tab);
                if (!$tab->delete()) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Run SQL file
     *
     * @param string $type
     * @return bool
     * @throws Exception
     */
    protected function runDefinitionsSql($type = 'install')
    {
        if (isset($this->sql) && is_array($this->sql)) {
            if (!isset($this->sql[$type])) {
                throw new Exception('SQL file type not exists');
            }

            foreach ($this->sql[$type] as $file) {
                if (!file_exists(_PS_MODULE_DIR_.$this->name.'/sql/'.$file)) {
                    throw new Exception('This SQL file not exists');
                }

                $content = Tools::file_get_contents(_PS_MODULE_DIR_.$this->name.'/sql/'.$file);
                $content = str_replace('@PREFIX@', _DB_PREFIX_, $content);
                $content = str_replace('@ENGINE@', _MYSQL_ENGINE_, $content);
                $queries = preg_split("/;\s*[\r\n]+/", $content);

                foreach ($queries as $query) {
                    if (!empty($query)) {
                        if (!Db::getInstance()->execute(trim($query))) {
                            return false;
                        }
                    }
                }
            }
        }
        return true;
    }

    /**
     * Get all module configurations keys
     * @return array
     */
    public function getModuleConfigurationsKeys()
    {
        return array_keys($this->configurations);
    }

    /**
     * Get all module configurations values
     * @return mixed
     */
    public function getModuleConfigurations()
    {
        $configurations = array();
        $languages = Language::getLanguages(false);

        foreach ($this->getModuleConfigurationsKeys() as $key) {
            if ($this->isConfigurationLangKey($key)) {
                foreach ($languages as $lang) {
                    $configurations[$key][$lang['id_lang']] = Configuration::get($key, $lang['id_lang']);
                }
            } else {
                $configurations[$key] = Configuration::get($key);
            }
        }

        return $configurations;
    }

    /**
     * Display bootstrap template if PrestaShop is 1.6 or greater
     * @param $file
     * @param $template
     * @param null $cacheId
     * @param null $compileId
     * @return mixed
     */
    public function fcdisplay($file, $template, $cacheId = null, $compileId = null)
    {
        // Make fcdisplay compliant with hook processor
        if (Tools::substr(dirname($file), -Tools::strlen(DIRECTORY_SEPARATOR.'hooks')) === DIRECTORY_SEPARATOR.'hooks') {
            $file = dirname($file);
            $file = Tools::substr($file, 0, Tools::strlen($file) - Tools::strlen(DIRECTORY_SEPARATOR.'hooks'));
            $file = $file.DIRECTORY_SEPARATOR.basename($file).'.php';
        }

        // If PS 1.6 or greater, we choose bootstrap template
        if (version_compare(_PS_VERSION_, '1.6.0') >= 0) {
            $template_bootstrap = str_replace('.tpl', '.bootstrap.tpl', $template);
            if ($this->getTemplatePath($template_bootstrap) !== null) {
                $template = $template_bootstrap;
            }
        }

        // On PS 1.4, we have to show him the path
        if (version_compare(_PS_VERSION_, '1.5') < 0) {
            return parent::display($file, 'views'.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'hook'.DIRECTORY_SEPARATOR.$template, $cacheId, $compileId);
        } else {
            return parent::display($file, $template, $cacheId, $compileId);
        }
    }

    /**
     * Render categories tree method
     */
    public function renderCategoriesTree($id_category_root, $config_name, $field_name)
    {
        $categories = array();
        $categories_selected = Configuration::get($config_name);
        if (!empty($categories_selected)) {
            foreach (Tools::jsonDecode($categories_selected, true) as $key => $category) {
                $categories[] = $category;
            }
        }

        $tree = new FroggyHelperTreeCategories();
        $tree->setAttributeName($field_name);
        $tree->setRootCategory($id_category_root);
        $tree->setLang($this->context->employee->id_lang);
        $tree->setSelectedCategories($categories);
        $tree->setContext($this->context);
        $tree->setModule($this);
        return $tree->render();
    }

    /**
     * Backwrd method in order to replace Configuration::isLangKey($key)
     *
     * @param $key
     * @return bool
     */
    protected function isConfigurationLangKey($key)
    {
        if (version_compare(_PS_VERSION_, '1.5') >= 0) {
            return Configuration::isLangKey($key);
        } else {
            return (bool)Db::getInstance()->getValue('
                SELECT COUNT(1)
                FROM `'._DB_PREFIX_.'configuration_lang` cl
                LEFT JOIN `'._DB_PREFIX_.'configuration` c ON (cl.`id_configuration` = c.`id_configuration`)
                WHERE c.`name` = \''.pSQL($key).'\'');
        }
    }

    /**
     * Backward method for module controller link
     *
     * @param $controller_name
     * @return string
     */
    protected function getModuleLink($controller_name)
    {
        if (version_compare(_PS_VERSION_, '1.5') >= 0) {
            $link = $this->context->link->getModuleLink($this->name, $controller_name);
        } else {
            // In 1.4 version, you need to create a PHP file in order to call the controller
            $link = $this->_path.$controller_name.'.php?';
        }
        return $link;
    }
}


/***** Multi compliancy methods *****/

function FroggyGetAdminLink($params, &$smarty)
{
    // In 1.5, we use getAdminLink method
    if (version_compare(_PS_VERSION_, '1.5.0') >= 0) {
        if (version_compare(_PS_VERSION_, '1.6.0') >= 0 && $params['a'] == 'AdminHome') {
            $params['a'] = 'AdminDashboard';
        }
        return Context::getContext()->link->getAdminLink($params['a']);
    }

    // Match compatibility between 1.4 and 1.5
    $match = array(
        'AdminProducts' => 'AdminCatalog',
        'AdminCategories' => 'AdminCatalog',
        'AdminCmsContent' => 'AdminCMSContent',
    );
    if (isset($match[$params['a']])) {
        $params['a'] = $match[$params['a']];
    }

    // In 1.4, we build it with cookie for back office or with argument for front office (see froggytoolbar)
    $tab = $params['a'];
    $id_employee = FroggyContext::getContext()->employee->id;
    if (isset($params['e'])) {
        $id_employee = $params['e'];
    }
    $token = Tools::getAdminToken($tab.(int)Tab::getIdFromClassName($tab).(int)$id_employee);

    // Return link
    return 'index.php?tab='.$tab.'&token='.$token;
}

function FroggyDisplaySafeHtml($params, &$smarty)
{
    return $params['s'];
}

function FroggyDisplayDate($date, $id_lang = null, $full = false, $separator = null)
{
    if (version_compare(_PS_VERSION_, '1.6.0') >= 0) {
        return Tools::displayDate($date, null, $full, null);
    }
    return Tools::displayDate($date, $id_lang, $full, $separator);
}
