<?php
/**
* 2017-2018 Zemez
*
* JX Header Account
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

use PrestaShop\PrestaShop\Core\Module\WidgetInterface as WidgetInterface;
use PrestaShop\PrestaShop\Core\Crypto\Hashing;

include_once _PS_MODULE_DIR_ . 'jxheaderaccount/src/entities/JXHeaderAccountEntity.php';
include_once _PS_MODULE_DIR_ . 'jxheaderaccount/src/repositories/JXHeaderAccountRepository.php';

class Jxheaderaccount extends Module implements WidgetInterface
{
    protected $config_form            = false;
    protected $facebook_compatibility = true;
    protected $defConfigs;
    protected $id_shop;
    protected $img_dir;
    public $repository;

    public function __construct()
    {
        $this->name = 'jxheaderaccount';
        $this->tab = 'front_office_features';
        $this->version = '2.1.1';
        $this->author = 'Zemez (Alexander Grosul)';
        $this->need_instance = 0;
        $this->module_key = '9bddf1e52deda8ef5305b4f2ea1edcb9';
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('JX Header Account');
        $this->description = $this->l('Display customer account information in the site header');
        $this->confirmUninstall = $this->l('Are you sure that you want to delete all of your API\'s?');
        $this->repository = new JXHeaderAccountRepository(
            Db::getInstance(),
            $this->context->shop,
            $this->context->language
        );
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        if (version_compare(PHP_VERSION, '5.4.0', '<')) {
            $this->facebook_compatibility = false;
        }
        $this->id_shop = $this->context->shop->id;
        $this->img_dir = __PS_BASE_URI__.'modules/jxheaderaccount/views/img/';
        $this->defConfigs = array(
            'JXHEADERACCOUNT_DISPLAY_TYPE'  => 'dropdown',
            'JXHEADERACCOUNT_DISPLAY_STYLE' => 'onecolumn',
            'JXHEADERACCOUNT_USE_REDIRECT'  => 'false',
            'JXHEADERACCOUNT_USE_AVATAR'    => 'false',
            'JXHEADERACCOUNT_AVATAR'        => $this->img_dir.'avatar/avatar.jpg',
            'JXHEADERACCOUNT_FSTATUS'       => 'false',
            'JXHEADERACCOUNT_FAPPID'        => '',
            'JXHEADERACCOUNT_FAPPSECRET'    => '',
            'JXHEADERACCOUNT_GSTATUS'       => 'false',
            'JXHEADERACCOUNT_GAPPID'        => '',
            'JXHEADERACCOUNT_GAPPSECRET'    => '',
            'JXHEADERACCOUNT_GREDIRECT'     => '',
            'JXHEADERACCOUNT_VKSTATUS'      => 'false',
            'JXHEADERACCOUNT_VKAPPID'       => '',
            'JXHEADERACCOUNT_VKAPPSECRET'   => '',
            'JXHEADERACCOUNT_VKREDIRECT'    => ''
        );
        $this->displayTypes = array(
            array('type' => 'dropdown', 'name' => $this->l('Drop down')),
            array('type' => 'popup', 'name' => $this->l('Popup')),
            array('type' => 'leftside', 'name' => $this->l('Left side')),
            array('type' => 'rightside', 'name' => $this->l('Right side'))
        );
    }

    public function install()
    {
        if ($this->facebook_compatibility) {
            require_once(dirname(__FILE__).'/libs/facebook/autoload.php');
        }

        return parent::install()
        && $this->repository->createTables()
        && $this->installDefOptions()
        && $this->registerHook('registerGDPRConsent')
        && $this->registerHook('actionDeleteGDPRCustomer')
        && $this->registerHook('actionExportGDPRData')
        && $this->registerHook('backOfficeHeader')
        && $this->registerHook('displayHeader')
        && $this->registerHook('displayNav2')
        && $this->registerHook('displayTop')
        && $this->registerHook('displayCustomerAccountFormTop')
        && $this->registerHook('displayCustomerAccount')
        && $this->registerHook('displayHeaderLoginButtons')
        && $this->registerHook('displaySocialLoginButtons')
        && $this->registerHook('displayLeftColumn')
        && $this->registerHook('displayRightColumn')
        && $this->registerHook('additionalCustomerFormFields');
    }

    public function uninstall()
    {
        return parent::uninstall()
        && $this->repository->dropTables()
        && $this->uninstallDefOptions();
    }

    /**
     * @return bool Install def options of the module
     */
    protected function installDefOptions()
    {
        foreach ($this->defConfigs as $name => $value) {
            Configuration::updateValue($name, $value);
        }

        return true;
    }

    /**
     * @return bool Uninstall def options of the module
     */
    protected function uninstallDefOptions()
    {
        foreach (array_keys($this->defConfigs) as $name) {
            Configuration::deleteByName($name);
        }

        return true;
    }

    /**
     * @param bool $type
     *
     * @return array Return module options
     */
    protected function getOptions($type = false)
    {
        $configs = array();
        if (!$type) {
            foreach (array_keys($this->defConfigs) as $name) {
                $configs[$name] = Tools::getValue($name);
            }
        } else {
            foreach (array_keys($this->defConfigs) as $name) {
                $configs[$name] = Tools::getValue($name, Configuration::get($name));
            }
        }

        return $configs;
    }

    /**
     * Return options for frontoffice
     *
     * @return array Options
     */
    protected function getOptionsFront()
    {
        $configs = array();
        foreach (array_keys($this->defConfigs) as $name) {
            $configs[$name] = Configuration::get($name);
        }

        return $configs;
    }

    /**
     * @param $options array
     *
     * Update options
     */
    protected function setOptions($options)
    {
        foreach ($options as $name => $value) {
            Configuration::updateValue($name, $value);
        }
    }

    /**
     * Add def variables to js
     */
    protected function addConfigsToJs()
    {
        Media::addJsDef($this->getOptionsFront());
    }

    /**
     * Get module errors
     */
    public function getErrors()
    {
        $this->context->controller->errors = $this->_errors;
    }

    /**
     * Get module confirmations
     */
    public function getConfirmations()
    {
        $this->context->controller->confirmations = $this->_confirmations;
    }

    /**
     * Get module warnings
     */
    protected function getWarnings()
    {
        $this->context->controller->warnings = $this->warning;
    }

    /**
     * Check warning
     */
    protected function checkPhpWarnings()
    {
        if (!$this->facebook_compatibility) {
            $this->warning = $this->l(
                'JX Header Account(Facebook) requires PHP version 5.4 or higher. Facebook login will be unavailable.'
            );
        }
        if (!function_exists('curl_init')) {
            $this->facebook_compatibility = false;
            $this->warning = $this->l(
                'JX Header Account(Facebook) need the CURL PHP extension. Facebook login will be unavailable.'
            );
        }
        if (!function_exists('json_decode')) {
            $this->facebook_compatibility = false;
            $this->warning = $this->l(
                'JX Header Account(Facebook) need the JSON PHP extension. Facebook login will be unavailable.'
            );
        }
        if (!function_exists('hash_hmac')) {
            $this->facebook_compatibility = false;
            $this->warning = $this->l(
                'JX Header Account(Facebook) need the HMAC Hash (hash_hmac) PHP extension. Facebook login will be unavailable.'
            );
        }
    }

    public function getContent()
    {
        $content = $this->getBackOfficeContent();
        $this->getErrors();
        $this->getWarnings();
        $this->getConfirmations();
        $this->processImageUpload($_FILES);

        return $content;
    }

    /**
     * Render module backoffice
     * @return mixed Html of backoffice
     */
    protected function getBackOfficeContent()
    {
        $this->checkPhpWarnings();
        if (Tools::isSubmit('submitJxheaderaccount')) {
            $options = $this->getOptions();
            if ($this->validateAllFields($options)) {
                $this->_confirmations = $this->l('Settings saved');
                $this->setOptions($options);
                $this->clearCache();
            }
        }
        $this->context->smarty->assign('module_dir', $this->_path);

        return $this->renderMainForm();
    }

    /**
     * Render main form of backoffice
     *
     * @return mixed html
     */
    protected function renderMainForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitJxheaderaccount';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getOptions(true), /* Add values for your inputs */
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getMainConfigForm()));
    }

    /**
     * Get configs for main form
     *
     * @return array Configs
     */
    protected function getMainConfigForm()
    {
        $disabled = false;
        if (!$this->facebook_compatibility) {
            $disabled = true;
        }
        $img_desc = '';
        $img_desc .= ''.$this->l('Upload a Avatar from your computer.N.B : Only jpg image is allowed');
        $img_desc .= '<br/><img style="clear:both;border:1px solid black;" alt="" src="'.$this->img_dir.'avatar/avatar.jpg" width="100"/><br />';

        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon'  => 'icon-cogs',
                ),
                'input'  => array(
                    array(
                        'type'    => 'select',
                        'label'   => $this->l('Display type'),
                        'name'    => 'JXHEADERACCOUNT_DISPLAY_TYPE',
                        'options' => array(
                            'query' => $this->displayTypes,
                            'id'    => 'type',
                            'name'  => 'name'
                        )
                    ),
                    array(
                        'type'   => 'radio',
                        'label'  => $this->l('Display style after login:'),
                        'name'   => 'JXHEADERACCOUNT_DISPLAY_STYLE',
                        'values' => array(
                            array(
                                'id'       => 'twocolumns',
                                'value'    => 'twocolumns',
                                'label'    => $this->l('Two columns'),
                                'img_link' => $this->img_dir.'/twocolumns.png'
                            ),
                            array(
                                'id'       => 'onecolumn',
                                'value'    => 'onecolumn',
                                'label'    => $this->l('One Column'),
                                'img_link' => $this->img_dir.'/onecolumn.png'
                            ),
                        ),
                    ),
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Display avatar'),
                        'name'    => 'JXHEADERACCOUNT_USE_AVATAR',
                        'is_bool' => true,
                        'values'  => array(
                            array(
                                'id'    => 'enable',
                                'value' => 1,
                                'label' => $this->l('Yes')),
                            array(
                                'id'    => 'disable',
                                'value' => 0,
                                'label' => $this->l('No')),
                        ),
                    ),
                    array(
                        'type'          => 'file',
                        'label'         => $this->l('Default avatar:'),
                        'name'          => 'JXHEADERACCOUNT_AVATAR',
                        'display_image' => false,
                        'desc'          => $img_desc
                    ),
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Use redirect'),
                        'name'    => 'JXHEADERACCOUNT_USE_REDIRECT',
                        'is_bool' => true,
                        'values'  => array(
                            array(
                                'id'    => 'enable',
                                'value' => 1,
                                'label' => $this->l('Yes')),
                            array(
                                'id'    => 'disable',
                                'value' => 0,
                                'label' => $this->l('No')),
                        ),
                    ),
                    array(
                        'type'     => 'switch',
                        'label'    => $this->l('Use Facebook Login'),
                        'name'     => 'JXHEADERACCOUNT_FSTATUS',
                        'disabled' => $disabled,
                        'is_bool'  => true,
                        'values'   => array(
                            array(
                                'id'    => 'enable',
                                'value' => 1,
                                'label' => $this->l('Yes')),
                            array(
                                'id'    => 'disable',
                                'value' => 0,
                                'label' => $this->l('No')),
                        ),
                    ),
                    array(
                        'col'      => 3,
                        'type'     => 'text',
                        'required' => true,
                        'name'     => 'JXHEADERACCOUNT_FAPPID',
                        'label'    => $this->l('App ID'),
                        'class'    => 'fb-field',
                        'disabled' => $disabled,
                    ),
                    array(
                        'col'      => 3,
                        'type'     => 'text',
                        'required' => true,
                        'name'     => 'JXHEADERACCOUNT_FAPPSECRET',
                        'label'    => $this->l('App Secret'),
                        'class'    => 'fb-field',
                        'disabled' => $disabled,
                    ),
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Use Google Login'),
                        'name'    => 'JXHEADERACCOUNT_GSTATUS',
                        'is_bool' => true,
                        'values'  => array(
                            array(
                                'id'    => 'enable',
                                'value' => 1,
                                'label' => $this->l('Yes')),
                            array(
                                'id'    => 'disable',
                                'value' => 0,
                                'label' => $this->l('No')),
                        ),
                    ),
                    array(
                        'col'      => 3,
                        'type'     => 'text',
                        'required' => true,
                        'name'     => 'JXHEADERACCOUNT_GAPPID',
                        'label'    => $this->l('App ID'),
                        'class'    => 'google-field',
                    ),
                    array(
                        'col'      => 3,
                        'type'     => 'text',
                        'required' => true,
                        'name'     => 'JXHEADERACCOUNT_GAPPSECRET',
                        'label'    => $this->l('App Secret'),
                        'class'    => 'google-field',
                    ),
                    array(
                        'col'      => 3,
                        'type'     => 'text',
                        'required' => true,
                        'name'     => 'JXHEADERACCOUNT_GREDIRECT',
                        'desc'     => 'Your shop URL + index.php?fc=module&module=jxheaderaccount&controller=googlelogin',
                        'label'    => $this->l('Redirect URIs'),
                        'class'    => 'google-field',
                    ),
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Use VK Login'),
                        'name'    => 'JXHEADERACCOUNT_VKSTATUS',
                        'is_bool' => true,
                        'values'  => array(
                            array(
                                'id'    => 'enable',
                                'value' => 1,
                                'label' => $this->l('Yes')),
                            array(
                                'id'    => 'disable',
                                'value' => 0,
                                'label' => $this->l('No')),
                        ),
                    ),
                    array(
                        'col'      => 3,
                        'type'     => 'text',
                        'required' => true,
                        'name'     => 'JXHEADERACCOUNT_VKAPPID',
                        'label'    => $this->l('App ID'),
                        'class'    => 'vk-field',
                    ),
                    array(
                        'col'      => 3,
                        'type'     => 'text',
                        'required' => true,
                        'name'     => 'JXHEADERACCOUNT_VKAPPSECRET',
                        'label'    => $this->l('App Secret'),
                        'class'    => 'vk-field',
                    ),
                    array(
                        'col'      => 3,
                        'type'     => 'text',
                        'required' => true,
                        'name'     => 'JXHEADERACCOUNT_VKREDIRECT',
                        'desc'     => 'Your shop URL + index.php?fc=module&module=jxheaderaccount&controller=vklogin',
                        'label'    => $this->l('Redirect URIs'),
                        'class'    => 'vk-field',
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save')
                ),
            ),
        );
    }

    /**
     * Validate facebook fields
     *
     * @param $fbstatus
     * @param $fbappid
     * @param $fbappsecret
     */
    protected function validateFbFields($fbstatus, $fbappid, $fbappsecret)
    {
        if (((int)$fbstatus && $fbstatus != 0) && (empty($fbappid) || empty($fbappsecret))) {
            $this->_errors[] = $this->l('Please fill all Facebook fields!');
        }
    }

    /**
     * Validate google fields
     *
     * @param $gstatus
     * @param $gappid
     * @param $gappsecret
     * @param $gredirect
     */
    protected function validateGoogleFields($gstatus, $gappid, $gappsecret, $gredirect)
    {
        if (($gstatus && $gstatus != 0) && (empty($gappid) || empty($gappsecret) || empty($gredirect))) {
            $this->_errors[] = $this->l('Please fill all Google fields!');
        }
    }

    /**
     * Validate vk fields
     *
     * @param $vkstatus
     * @param $vkappid
     * @param $vkappsecret
     * @param $vkredirect
     */
    protected function validateVkFields($vkstatus, $vkappid, $vkappsecret, $vkredirect)
    {
        if (($vkstatus && $vkstatus != 0) && (empty($vkappid) || empty($vkappsecret) || empty($vkredirect))) {
            $this->_errors[] = $this->l('Please fill all VK fields!');
        }
    }

    /**
     * Validate all backoffice fields
     *
     * @param $options
     *
     * @return bool result
     */
    protected function validateAllFields($options)
    {
        if (isset($_FILES['JXHEADERACCOUNT_AVATAR']) && isset($_FILES['JXHEADERACCOUNT_AVATAR']['tmp_name']) && !empty($_FILES['JXHEADERACCOUNT_AVATAR']['tmp_name'])) {
            if ($error = ImageManager::validateUpload($_FILES['JXHEADERACCOUNT_AVATAR'], 4000000, array('jpg'))) {
                $this->_errors[] = $this->l('Image format not recognized, allowed format is .jpg only');
            }
        }
        $this->validateFbFields(
            $options['JXHEADERACCOUNT_FSTATUS'],
            $options['JXHEADERACCOUNT_FAPPID'],
            $options['JXHEADERACCOUNT_FAPPSECRET']
        );
        $this->validateGoogleFields(
            $options['JXHEADERACCOUNT_GSTATUS'],
            $options['JXHEADERACCOUNT_GAPPID'],
            $options['JXHEADERACCOUNT_GAPPSECRET'],
            $options['JXHEADERACCOUNT_GREDIRECT']
        );
        $this->validateVkFields(
            $options['JXHEADERACCOUNT_VKSTATUS'],
            $options['JXHEADERACCOUNT_VKAPPID'],
            $options['JXHEADERACCOUNT_VKAPPSECRET'],
            $options['JXHEADERACCOUNT_VKREDIRECT']
        );
        if (count($this->_errors) > 0) {
            return false;
        }

        return true;
    }

    /**
     * Get social id of customer by social network type
     *
     * @param $type social network type
     *
     * @return bool|false|null|string Social id
     */
    protected function getSocialId($type)
    {
        if ($id_customer = $this->context->customer->id) {
            $headeraccount = new HeaderAccount();
            if (!$id_social = $headeraccount->getSocialId($type, $id_customer)) {
                return false;
            }

            return $id_social;
        }

        return false;
    }

    /**
     * @param $type social network type
     *
     * @return bool|false|null|string Link
     * Return image link of user avatar
     */
    protected function getImageUrl($type)
    {
        if ($id_customer = $this->context->customer->id) {
            $headeraccount = new HeaderAccount();
            if (!$id_social = $headeraccount->getImageUrl($type, $id_customer)) {
                return false;
            }

            return $id_social;
        }

        return false;
    }

    /**
     * Get user avatar
     *
     * @return bool|false|null|string link
     */
    protected function getUserAvatar()
    {
        if ($social_id = $this->getSocialId('facebook')) {
            return 'https://graph.facebook.com/'.$social_id.'/picture?width=300&height=300';
        } elseif ($this->getSocialId('google')) {
            return $this->getImageUrl('google');
        } elseif ($this->getSocialId('vk')) {
            return $this->getImageUrl('vk');
        } else {
            return $this->img_dir.'avatar/avatar.jpg';
        }
    }

    /**
     * Upload default avatar image
     *
     * @param $FILES
     *
     * @return mixed
     */
    public function processImageUpload($FILES)
    {
        if (isset($FILES['JXHEADERACCOUNT_AVATAR']) && isset($FILES['JXHEADERACCOUNT_AVATAR']['tmp_name']) && !empty($FILES['JXHEADERACCOUNT_AVATAR']['tmp_name'])) {
            if (ImageManager::validateUpload($FILES['JXHEADERACCOUNT_AVATAR'], 4000000, array('jpg'))) {
                return $this->displayError($this->l('Invalid image'));
            } else {
                $ext = Tools::substr(
                    $FILES['JXHEADERACCOUNT_AVATAR']['name'],
                    strrpos($FILES['JXHEADERACCOUNT_AVATAR']['name'], '.') + 1
                );
                $file_name = 'avatar.'.$ext;
                $path = _PS_MODULE_DIR_.'jxheaderaccount/views/img/avatar/'.$file_name;
                if (!move_uploaded_file($FILES['JXHEADERACCOUNT_AVATAR']['tmp_name'], $path)) {
                    return $this->displayError($this->l('An error occurred while attempting to upload the file.'));
                } else {
                    Configuration::updateValue('JXHEADERACCOUNT_AVATAR', $path);
                }
            }
        }
    }

    protected function assignDate()
    {
        $selectedYears = (int)(Tools::getValue('years', 0));
        $years = Tools::dateYears();
        $selectedMonths = (int)(Tools::getValue('months', 0));
        $months = Tools::dateMonths();
        $selectedDays = (int)(Tools::getValue('days', 0));
        $days = Tools::dateDays();
        $this->context->smarty->assign(
            array(
                'one_phone_at_least' => (int)Configuration::get('PS_ONE_PHONE_AT_LEAST'),
                'onr_phone_at_least' => (int)Configuration::get('PS_ONE_PHONE_AT_LEAST'), //retro compat
                'years'              => $years,
                'sl_year'            => $selectedYears,
                'months'             => $months,
                'sl_month'           => $selectedMonths,
                'days'               => $days,
                'sl_day'             => $selectedDays
            )
        );
    }

    protected function assignCountries()
    {
        $this->id_country = (int)Tools::getCountry();
        if (Configuration::get('PS_RESTRICT_DELIVERED_COUNTRIES')) {
            $countries = Carrier::getDeliveredCountries($this->context->language->id, true, true);
        } else {
            $countries = Country::getCountries($this->context->language->id, true);
        }
        $this->context->smarty->assign(
            array(
                'countries'                    => $countries,
                'PS_REGISTRATION_PROCESS_TYPE' => Configuration::get('PS_REGISTRATION_PROCESS_TYPE'),
                'sl_country'                   => (int)$this->id_country,
                'vat_management'               => Configuration::get('VATNUMBER_MANAGEMENT')
            )
        );
    }

    protected function assignAddressFormat()
    {
        $addressItems = array();
        $addressFormat = AddressFormat::getOrderedAddressFields((int)$this->id_country, false, true);
        $requireFormFieldsList = AddressFormat::getFieldsRequired();
        foreach ($addressFormat as $addressline) {
            foreach (explode(' ', $addressline) as $addressItem) {
                $addressItems[] = trim($addressItem);
            }
        }
        // Add missing require fields for a new user susbscription form
        foreach ($requireFormFieldsList as $fieldName) {
            if (!in_array($fieldName, $addressItems)) {
                $addressItems[] = trim($fieldName);
            }
        }
        foreach (array('inv', 'dlv') as $addressType) {
            $this->context->smarty->assign(
                array(
                    $addressType.'_adr_fields' => $addressFormat,
                    $addressType.'_all_fields' => $addressItems,
                    'required_fields'          => $requireFormFieldsList
                )
            );
        }
    }

    public function clearCache()
    {
        $this->_clearCache('social-login-buttons.tpl');
        $this->_clearCache('customer-account-form-top.tpl');
        $this->_clearCache('customer-account.tpl');
        $this->_clearCache('header-account.tpl');
    }

    public function hookActionExportGDPRData($customer)
    {
        if (!Tools::isEmpty($customer['email']) && Validate::isEmail($customer['email'])) {
            $user = Customer::getCustomersByEmail($customer['email']);
            if ($customerInfo = HeaderAccount::getGDPRCustomerInfoById($user[0]['id_customer'])) {
                if ($customerInfo) {
                    return json_encode($customerInfo);
                }
            }

            return json_encode($this->displayName.$this->l(' module doesn\'t contain any information about you or it is unable to export it using email.'));
        }
    }

    public function hookActionDeleteGDPRCustomer($customer)
    {
        if (!empty($customer['email']) && Validate::isEmail($customer['email'])) {
            if ($user = Customer::getCustomersByEmail($customer['email'])) {
                $entries = new HeaderAccount();
                return json_encode($entries->removeEntriesByCustomerId($user[0]['id_customer']));
            }

            return json_encode($this->displayName.$this->l(' module! An error occurred during customer data removing'));
        }
    }

    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('configure') == $this->name || Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJquery();
            $this->context->controller->addJS($this->_path . 'views/js/jxheaderaccount_admin.js');
            $this->context->controller->addCSS($this->_path . 'views/css/jxheaderaccount_admin.css');
        }
    }

    public function hookDisplayHeader()
    {
        $this->addConfigsToJs();
        $this->context->controller->registerJavascript('validate', 'js/validate.js');
        $this->context->controller->requireAssets(array('font-awesome'));
        $this->context->controller->registerJavascript('module-jxheaderaccount', 'modules/' .$this->name. '/views/js/jxheaderaccount.js');
        $this->context->controller->registerStylesheet('module-jxheaderaccount', 'modules/' .$this->name. '/views/css/jxheaderaccount.css');
    }

    protected function authInit()
    {
        $this->context->smarty->assign('genders', Gender::getGenders());

        $this->assignDate();

        $this->assignCountries();

        $newsletter = Configuration::get('PS_CUSTOMER_NWSL') || (Module::isInstalled('blocknewsletter') && Module::getInstanceByName('blocknewsletter')->active);
        $this->context->smarty->assign('newsletter', $newsletter);
        $this->context->smarty->assign('optin', (bool)Configuration::get('PS_CUSTOMER_OPTIN'));

        $back = Tools::getValue('back');
        $key = Tools::safeOutput(Tools::getValue('key'));

        if (!empty($key)) {
            $back .= (strpos($back, '?') !== false ? '&' : '?').'key='.$key;
        }

        if ($back == Tools::secureReferrer(Tools::getValue('back'))) {
            $this->context->smarty->assign('back', html_entity_decode($back));
        } else {
            $this->context->smarty->assign('back', Tools::safeOutput($back));
        }

        if (Tools::getValue('create_account')) {
            $this->context->smarty->assign('email_create', 1);
        }

        if (Tools::getValue('multi-shipping') == 1) {
            $this->context->smarty->assign('multi_shipping', true);
        } else {
            $this->context->smarty->assign('multi_shipping', false);
        }

        $this->context->smarty->assign('field_required', $this->context->customer->validateFieldsRequiredDatabase());

        $this->assignAddressFormat();

        // Call a hook to display more information on form
        $this->context->smarty->assign(array(
            'HOOK_CREATE_ACCOUNT_FORM' => Hook::exec('displayCustomerAccountForm'),
            'HOOK_CREATE_ACCOUNT_TOP' => Hook::exec('displayCustomerAccountFormTop')
        ));
    }

    protected function addSocialStatus()
    {
        $this->context->smarty->assign(array(
            'f_status' => (int)Configuration::get('JXHEADERACCOUNT_FSTATUS'),
            'g_status' => (int)Configuration::get('JXHEADERACCOUNT_GSTATUS'),
            'vk_status' => (int)Configuration::get('JXHEADERACCOUNT_VKSTATUS')
        ));
    }

    protected function getCurrentURL()
    {
        return Tools::getCurrentUrlProtocolPrefix().$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    }

    protected function makeLoginForm()
    {
        $form = new CustomerLoginForm(
            $this->context->smarty,
            $this->context,
            $this->getTranslator(),
            new CustomerLoginFormatter($this->getTranslator()),
            array()
        );

        $form->setAction($this->getCurrentURL());

        return $form;
    }

    protected function getLoginFormValues()
    {
        $login_form = $this->makeLoginForm()->fillWith(
            Tools::getAllValues()
        );

        return $login_form->getTemplateVariables();
    }

    protected function makeCustomerFormatter()
    {
        $formatter = new CustomerFormatter(
            $this->getTranslator(),
            $this->context->language
        );

        $customer = new Customer();

        $formatter
            ->setAskForPartnerOptin(Configuration::get('PS_CUSTOMER_OPTIN'))
            ->setAskForBirthdate(Configuration::get('PS_CUSTOMER_BIRTHDATE'))
            ->setPartnerOptinRequired($customer->isFieldRequired('optin'))
        ;

        return $formatter;
    }

    protected function makeRegisterForm()
    {
        $form = new CustomerForm(
            $this->context->smarty,
            $this->context,
            $this->getTranslator(),
            $this->makeCustomerFormatter(),
            new CustomerPersister(
                $this->context,
                new Hashing(),
                $this->getTranslator(),
                false
            ),
            array()
        );

        $form->setGuestAllowed(false);

        $form->setAction($this->getCurrentURL());

        return $form;
    }

    protected function getRegisterFormValues()
    {
        $register_form = $this
            ->makeRegisterForm()
            ->setGuestAllowed(false)
            ->fillWith(Tools::getAllValues());

        return $register_form->getTemplateVariables();
    }

    public function hookDisplayCustomerAccount()
    {
        $this->context->smarty->assign(
            array(
                'facebook_id' => $this->getSocialId('facebook'),
                'google_id'   => $this->getSocialId('google'),
                'vkcom_id'    => $this->getSocialId('vk'),
                'f_status'    => (int)Configuration::get('JXHEADERACCOUNT_FSTATUS'),
                'g_status'    => (int)Configuration::get('JXHEADERACCOUNT_GSTATUS'),
                'vk_status'   => (int)Configuration::get('JXHEADERACCOUNT_VKSTATUS')
            )
        );

        return $this->display(__FILE__, 'customer-account.tpl');
    }

    protected function getHookTemplatesPath($hookName, $filename)
    {
        $path = $this->local_path.'/views/templates/hook';
        if (!file_exists($path.'/'.$hookName.'/'.$filename)) {
            return '/views/templates/hook/default/'.$filename;
        }

        return '/views/templates/hook/'.$hookName.'/'.$filename;
    }

    public function hookDisplayCustomerAccountFormTop()
    {
        if (!$this->isCached('customer-account-form-top.tpl', $this->getCacheId('jxheaderaccount'))) {
            $this->addSocialStatus();
        }

        return $this->display(__FILE__, 'customer-account-form-top.tpl', $this->getCacheId('jxheaderaccount'));
    }
    public function hookDisplayHeaderLoginButtons()
    {
        if (!$this->isCached('header-account.tpl', $this->getCacheId('jxheaderaccount'))) {
            $this->addSocialStatus();
        }

        return $this->display($this->_path, 'views/templates/hook/header-account.tpl', $this->getCacheId('jxheaderaccount'));
    }

    public function hookDisplaySocialLoginButtons()
    {
        if (!$this->isCached('social-login-buttons.tpl', $this->getCacheId('jxheaderaccount'))) {
            $this->addSocialStatus();
        }

        return $this->display(__FILE__, 'social-login-buttons.tpl', $this->getCacheId('jxheaderaccount'));
    }

    public function renderWidget($hookName, array $configuration)
    {
        $this->authInit();

        $this->context->smarty->assign(
            $this->getWidgetVariables($hookName, $configuration)
        );

        return $this->display(__FILE__, $this->getHookTemplatesPath($hookName, 'jxheaderaccount.tpl'));
    }

    public function getWidgetVariables($hookName, array $configuration)
    {
        $front_options = $this->getOptionsFront();
        return array(
            'voucherAllowed'        => CartRule::isFeatureActive(),
            'returnAllowed'         => (int)Configuration::get('PS_ORDER_RETURN'),
            'HOOK_BLOCK_MY_ACCOUNT' => Hook::exec('displayCustomerAccount'),
            'configs'               => $front_options,
            'avatar'                => $this->getUserAvatar(),
            'firstname'             => $this->context->customer->firstname,
            'lastname'              => $this->context->customer->lastname,
            'hook'                  => $hookName,
            'login_form'            => $this->getLoginFormValues(),
            'register_form'         => $this->getRegisterFormValues(),
        );
    }

    public function hookAdditionalCustomerFormFields($params)
    {
        $id_lang = Context::getContext()->language->id;
        $currentPage = Context::getContext()->controller->php_self;
        if (!in_array($currentPage, array('identity', 'authentication', 'order'))) {
            if ($active = Configuration::get('PSGDPR_CUSTOMER_FORM_SWITCH') && $label = Configuration::get('PSGDPR_CREATION_FORM', $id_lang)) {
                $formField = new FormField();
                $formField->setName('psgdpr');
                $formField->setType('checkbox');
                $formField->setLabel($label);
                $formField->setRequired(true);

                return array($formField);
            }
        }

        return;
    }
}
