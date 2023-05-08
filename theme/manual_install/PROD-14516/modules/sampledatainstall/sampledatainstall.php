<?php
/**
 * 2017-2018 Zemez
 *
 * Sampledatainstall
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the General Public License (GPL 2.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/GPL-2.0
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the module to newer
 * versions in the future.
 *
 *  @author    Zemez (Alexander Grosul)
 *  @copyright 2017-2018 Zemez
 *  @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once(_PS_MODULE_DIR_.'sampledatainstall/src/TabManager.php');

class Sampledatainstall extends Module
{
    protected $config_form = false;
    protected $send_path = false;
    public $module_path;
    public $tabManager;

    public function __construct()
    {
        $this->name = 'sampledatainstall';
        $this->tab = 'export';
        $this->version = '1.1.2';
        $this->author = 'Zemez';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('Sample Data Install');
        $this->description = $this->l('Module for creating and installation of a sample data');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->module_path = $this->local_path;
        $this->tabManager = new SampledatainstallTabManager();
    }

    public function install()
    {
        return $this->tabManager->installTabs() &&
            parent::install() &&
            $this->registerHook('displayBackOfficeHeader');
    }

    public function uninstall()
    {
        return parent::uninstall() && $this->tabManager->removeTab();
    }

    public function getContent()
    {}
    public function sendPath()
    {
        return $this->local_path;
    }
    public function getWebPath()
    {
        return $this->_path;
    }

    public static function cmp($a, $b)
    {
        return strcmp($a['id_attribute_group'], $b['id_attribute_group']);
    }

    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }
}
