<?php
/**
* 2017-2018 Zemez
*
* JX Wishlist
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
*  @author    Zemez
*  @copyright 2017-2018 Zemez
*  @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once(_PS_MODULE_DIR_.'jxwishlist/classes/ClassJxWishlist.php');


class Jxwishlist extends ModuleGrid
{
    protected $ssl = 'http://';
    private $columns = null;
    private $default_sort_column = null;
    private $default_sort_direction = null;
    private $empty_message = null;
    private $paging_message = null;

    public function __construct()
    {
        $this->name = 'jxwishlist';
        $this->tab = 'front_office_features';
        $this->version = '1.1.1';
        $this->bootstrap = true;
        $this->author = 'Zemez';
        parent::__construct();
        $this->default_sort_column = 'totalPriceSold';
        $this->default_sort_direction = 'DESC';
        $this->displayName = $this->l('JX Wishlist');
        $this->module_key = '4807724721dbb4cdd5ad952d5da5bcf3';
        $this->description = $this->l('Module to create a wishlist and share post on facebook.');
        $this->default_wishlist_name = $this->l('My wishlist');
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->id_shop = Context::getContext()->shop->id;

        $this->controllers = array(
            'wishlists',
            'wishlist'
        );

        $this->columns = array(
            array(
                'id' => 'id_product',
                'header' => $this->l('id'),
                'dataIndex' => 'id_product',
                'align' => 'center'
            ),
            array(
                'id' => 'name',
                'header' => $this->l('Name'),
                'dataIndex' => 'name',
                'align' => 'left'
            ),
            array(
                'id' => 'totalQuantityAdds',
                'header' => $this->l('Quantity adds'),
                'dataIndex' => 'totalQuantityAdds',
                'align' => 'center'
            ),
            array(
                'id' => 'totalQuantitySold',
                'header' => $this->l('Quantity sold'),
                'dataIndex' => 'totalQuantitySold',
                'align' => 'center'
            )
        );
    }

    public function install()
    {
        include(dirname(__FILE__).'/sql/install.php');

        Configuration::updateValue('JX_WISHLIST_APP_ID', '');

        return parent::install()
        && $this->registerHook('registerGDPRConsent')
        && $this->registerHook('actionDeleteGDPRCustomer')
        && $this->registerHook('actionExportGDPRData')
        && $this->registerHook('displayHeader')
        && $this->registerHook('moduleRoutes')
        && $this->registerHook('displayCustomerAccount')
        && $this->registerHook('productActions')
        && $this->registerHook('displayBackOfficeHeader')
        && $this->registerHook('AdminStatsModules')
        && $this->registerHook('displayBeforeBodyClosingTag')
        && $this->registerHook('displayNav2')
        && $this->registerHook('displayMyAccountBlock')
        && $this->registerHook('displayProductListFunctionalButtons')
        && $this->registerHook('displayProductListReviews');
    }

    public function uninstall()
    {
        include(dirname(__FILE__).'/sql/uninstall.php');

        if (!Configuration::deleteByName('JX_WISHLIST_APP_ID')
            || !parent::uninstall()) {
            return false;
        }

        return true;
    }

    public function getContent()
    {
        $output = $this->display($this->_path, 'views/templates/admin/jxwishlist-documentation.tpl');;
        $errors = array();

        if (Tools::isSubmit('submitJxWishlist')) {
            if (Tools::isEmpty(Tools::getValue('JX_WISHLIST_APP_ID'))) {
                $errors[] = $this->l('App id is required.');
            } else {
                $this->postProcess();
            }

            if (isset($errors) && count($errors)) {
                $output .= $this->displayError(implode('<br />', $errors));
            } else {
                $output .= $this->displayConfirmation($this->l('Settings updated.'));
            }
        }

        return $output.$this->renderForm();
    }

    /**
     * Generate form for setting creating
     */
    public function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Facebook App Id'),
                        'name' => 'JX_WISHLIST_APP_ID',
                        'col' => 2,
                        'required' => true
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitJxWishlist';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).
            '&configure='.$this->name.
            '&tab_module='.$this->tab.
            '&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    /**
     * @return array setting values for list
     */
    public function getConfigFieldsValues()
    {
        return array(
            'JX_WISHLIST_APP_ID' => Tools::getValue('JX_WISHLIST_APP_ID', Configuration::get('JX_WISHLIST_APP_ID')),
        );
    }

    /**
     * Update Configuration values
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFieldsValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    public function getDateByClassJxWishlist()
    {
        return $this->getDate();
    }

    /**
     * Creating Stats
     */
    public function getData()
    {
        $adds = ClassJxWishlist::getProductByStatsAdds();
        $orders = ClassJxWishlist::getProductByStatsOrders();
        if ($orders) {
            foreach ($orders as $order) {
                foreach ($adds as $key => $add) {
                    if ($order['id_product'] == $add['id_product']) {
                        $adds[$key]['totalQuantitySold'] = $order['totalQuantitySold'];
                    }
                    if (!isset($adds[$key]['totalQuantitySold'])) {
                        $adds[$key]['totalQuantitySold'] = 0;
                    }
                }
            }
        } else {
            foreach ($adds as $key => $add) {
                if (!isset($adds[$key]['totalQuantitySold'])) {
                    $adds[$key]['totalQuantitySold'] = 0;
                }
            }
        }

        $this->_values = $adds;
        $this->_totalCount = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT FOUND_ROWS()');
    }

    /**
     * Get link for ajax controller
     * @param $type and $data
     * @return string
     */
    public function getAjaxHtml($type, $data)
    {
        $this->context->smarty->assign('data', $data);
        return $this->display($this->_path, 'views/templates/front/'.$type.'.tpl');
    }

    public function hookActionExportGDPRData($customer)
    {
        if (!Tools::isEmpty($customer['email']) && Validate::isEmail($customer['email'])) {
            $user = Customer::getCustomersByEmail($customer['email']);
            if ($customerWishlists = ClassJxWishlist::getUsersWishlistsOnRequest($user[0]['id_customer'])) {
                return json_encode($customerWishlists);
            }

            return json_encode($this->displayName.$this->l(' module doesn\'t contain any information about you or it is unable to export it using email.'));
        }
    }

    public function hookActionDeleteGDPRCustomer($customer)
    {
        if (!empty($customer['email']) && Validate::isEmail($customer['email'])) {
            if ($user = Customer::getCustomersByEmail($customer['email'])) {
                return json_encode(ClassJxWishlist::removeUsersWishlistsOnRequest($user[0]['id_customer']));
            }

            return json_encode($this->displayName.$this->l(' module! An error occurred during customer data removing'));
        }
    }

    /**
     * Create url for wishlist result page
     *
     * @param $params
     *
     * @return array
     */
    public function hookModuleRoutes($params)
    {
        return array(
            'module-jxwishlist-wishlists' => array(
                'controller' => 'wishlists',
                'rule'       => 'wishlists',
                'keywords'   => array(),
                'params'     => array(
                    'fc'     => 'module',
                    'module' => 'jxwishlist',
                ),
            ),
            'module-jxwishlist-wishlist' => array(
                'controller' => 'wishlist',
                'rule' => 'wishlist',
                'keywords'   => array(),
                'params' => array(
                    'fc' => 'module',
                    'module' => 'jxwishlist'
                ),
            )
        );
    }

    public function hookAdminStatsModules()
    {
        $engine_params = array(
            'id' => 'id_product',
            'title' => $this->displayName,
            'columns' => $this->columns,
            'defaultSortColumn' => $this->default_sort_column,
            'defaultSortDirection' => $this->default_sort_direction,
            'emptyMessage' => $this->empty_message,
            'pagingMessage' => $this->paging_message
        );

        if (Tools::getValue('export')) {
            $this->csvExport($engine_params);
        }

        $this->smarty->assign(
            array(
                'display_name' => $this->displayName,
                'engine_params' => $this->engine($engine_params),
                'export_url' => Tools::safeOutput($_SERVER['REQUEST_URI'].'&export=1')
            )
        );

        return $this->display(__FILE__, 'views/templates/admin/jxwishlist-stats.tpl');
    }

    public function hookDisplayNav2()
    {
        if (Configuration::get('JX_WISHLIST_APP_ID') != false) {
            return $this->display(__FILE__, 'views/templates/hook/jxwishlist-top.tpl');
        }
    }

    public function hookDisplayCustomerAccount()
    {
        if (Configuration::get('JX_WISHLIST_APP_ID') != false) {
            return $this->display(__FILE__, 'views/templates/hook/jxwishlist-customer-account.tpl');
        }
    }

    public function hookDisplayMyAccountBlock()
    {
        if (Configuration::get('JX_WISHLIST_APP_ID') != false) {
            return $this->display(__FILE__, 'views/templates/hook/jxwishlist-my-account.tpl');
        }
    }

    public function hookProductActions($params)
    {
        if (Configuration::get('JX_WISHLIST_APP_ID') != false) {
            $cookie = $params['cookie'];

            $this->smarty->assign(array(
                'id_product' => (int)Tools::getValue('id_product'),
            ));

            if (isset($cookie->id_customer)) {
                $this->smarty->assign(array(
                    'wishlists' => ClassJxWishlist::getByIdCustomer($cookie->id_customer),
                ));
            }

            return $this->display(__FILE__, 'views/templates/hook/jxwishlist-product.tpl');
        }
    }

    public function hookDisplayProductListFunctionalButtons($params)
    {
        if (Configuration::get('JX_WISHLIST_APP_ID') != false) {
            $cookie = $params['cookie'];

            $this->smarty->assign(array(
                'product' => $params['product'],
            ));

            if (isset($cookie->id_customer)) {
                $this->smarty->assign(array(
                    'wishlists' => ClassJxWishlist::getByIdCustomer($cookie->id_customer),
                ));
            }

            return $this->display(__FILE__, 'views/templates/hook/jxwishlist-products-list.tpl');
        }
    }

    public function hookDisplayProductListReviews($params)
    {
        return $this->hookDisplayProductListFunctionalButtons($params);
    }

    public function hookHeader()
    {
        $this->context->controller->requireAssets(array('font-awesome'));
        $this->context->controller->registerJavascript('module-jxwishlist', 'modules/'.$this->name.'/views/js/ajax-wishlists.js');
        $this->context->controller->registerStylesheet('module-jxwishlist', 'modules/'.$this->name.'/views/css/front_wishlists.css');

        $layouts = Tools::scandir($this->local_path . 'views/js/layouts/', 'js');

        foreach ($layouts as $layout) {
            $this->context->controller->addJS($this->_path . 'views/js/layouts/' . $layout);
        }

        $wishlists = ClassJxWishlist::getByIdCustomer($this->context->customer->id);

        if (empty($this->context->cookie->id_wishlist) === true || ClassJxWishlist::exists($this->context->cookie->id_wishlist, $this->context->customer->id) === false) {
            if (!count($wishlists)) {
                $id_wishlist = false;
            } else {
                $id_wishlist = (int)$wishlists[0]['id_wishlist'];
                $this->context->cookie->id_wishlist = (int)$id_wishlist;
            }
        } else {
            $id_wishlist = $this->context->cookie->id_wishlist;
        }

        $this->smarty->assign(
            array(
                'id_wishlist' => $id_wishlist,
                'wishlists' => $wishlists
            )
        );

        Media::addJsDefL('loggin_wishlist_required', $this->l('You must be logged in to manage your wishlist.'));
        Media::addJsDefL('added_to_wishlist', $this->l('The product was successfully added to your wishlist.'));
        Media::addJsDefL('change_name_wishlist', $this->l('Change name'));
        Media::addJsDefL('btn_wishlist', $this->l('My wishlists'));
        Media::addJsDefL('share_btn_text', $this->l('Share'));
        Media::addJsDefL('back_btn_text', $this->l('Back'));
        Media::addJsDefL('wishlist_title_step_1', $this->l('Step 1'));
        Media::addJsDefL('wishlist_title_step_1_desc', $this->l('(Select a layout to create an image that you post it)'));
        Media::addJsDefL('wishlist_title_step_2_desc', $this->l('(To add to the image of the cell)'));
        Media::addJsDefL('wishlist_title_step_2', $this->l('Step 2'));
        Media::addJsDefL('wishlist_no_product', $this->l('No products in this wishlist'));
        Media::addJsDef(array('mywishlists_url' => $this->context->link->getModuleLink('jxwishlist','wishlists',array(), true)));
        Media::addJsDef(array('logo_url' => _PS_IMG_.Configuration::get('PS_LOGO')));
        Media::addJsDef(array('isLogged' => Context::getContext()->customer->isLogged()));
    }

    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('configure') != $this->name) {
            return;
        }

        $this->context->controller->addJS($this->_path.'views/js/jxwishlist_admin.js');
        $this->context->controller->addCSS($this->_path.'views/css/jxwishlist_admin.css');
    }

    public function hookDisplayBeforeBodyClosingTag()
    {
       if (Tools::getValue('controller') == 'wishlists') {
           return $this->display($this->_path, '/views/templates/hook/jxwishlist-script.tpl');
       }
    }
}
