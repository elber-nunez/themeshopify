<?php
/**
 * 2017-2018 Zemez
 *
 * JX Look Book
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

class AdminJXLookBookController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();

        $this->module = new Jxlookbook();
    }

    public function ajaxProcessGetTemplates()
    {
        $templates = $this->module->renderCollectionTemplatesForm();

        die(json_encode(array('templates_form' => $templates)));
    }
    public function ajaxProcessGetProducts()
    {
        $jxlookbook = new Jxlookbook();
        $products = $jxlookbook->getProducts(Tools::getValue('id_category'));
        $type = Tools::getValue('type');
        if (!$selected_products = json_decode(Tools::getValue('selected_products'))) {
            $content = $jxlookbook->renderProductList($products, $type);
            die(json_encode(array('status' => 'true', 'content' => $content)));
        }

        foreach ($products as $key => $product) {
            if (is_numeric(array_search($product['id_product'], $selected_products))) {
                unset($products[$key]);
            }
        }

        if (count($products)) {
            $content = $jxlookbook->renderProductList($products, $type);
        } else {
            $content = $jxlookbook->displayWarning($this->l('No products to select'));
        }


        die(json_encode(array('status' => 'true', 'content' => $content)));
    }

    public function ajaxProcessUpdateCollectionsPosition()
    {
        $items = Tools::getValue('item');
        $total = count($items);
        $id_shop = (int)$this->context->shop->id;
        $success = true;

        for ($i = 1; $i <= $total; $i++) {
            $success &= Db::getInstance()->update(
                'jxlookbook',
                array('sort_order' => $i),
                '`id_collection` = '.preg_replace('/(item_)([0-9]+)/', '${2}', $items[$i - 1]).'
                AND `id_shop` ='.$id_shop
            );
        }
        if (!$success) {
            die(json_encode(array('error' => 'Update Fail')));
        }
        die(json_encode(array('success' => 'Update Success !', 'error' => false)));
    }

    public function ajaxProcessUpdateHooksPosition()
    {
        $items = Tools::getValue('item');
        $total = count($items);
        $hook = Tools::getValue('hook_name');
        $id_shop = (int)$this->context->shop->id;
        $success = true;

        for ($i = 1; $i <= $total; $i++) {
            $success &= Db::getInstance()->update(
                'jxlookbook_hook',
                array('sort_order' => $i),
                '`id` = '.preg_replace('/(item_)([0-9]+)/', '${2}', $items[$i - 1]).'
                AND `id_shop` ='.$id_shop .
                ' AND `hook_name` = \''.$hook.'\''
            );
        }
        if (!$success) {
            die(json_encode(array('error' => 'Update Fail')));
        }
        die(json_encode(array('success' => 'Update Success !', 'error' => false)));
    }

    public function ajaxProcessUpdateTabsPosition()
    {
        $items = Tools::getValue('item');
        $total = count($items);
        $success = true;

        for ($i = 1; $i <= $total; $i++) {
            $success &= Db::getInstance()->update(
                'jxlookbook_tabs',
                array('sort_order' => $i),
                '`id_tab` = '.preg_replace('/(item_)([0-9]+)/', '${2}', $items[$i - 1]).'
                AND `id_page` = '.(int)Tools::getValue('id_page')
            );
        }
        if (!$success) {
            die(json_encode(array('error' => 'Update Fail')));
        }
        die(json_encode(array('success' => 'Update Success !', 'error' => false)));
    }

    public function ajaxProcessGetHotSpotForm()
    {
        $lookbook = new Jxlookbook();

        $form = $lookbook->renderHotSpotForm();

        die(json_encode(array('form' => $form)));
    }

    public function ajaxProcessSaveHotSpot()
    {
        $lookbook = new Jxlookbook();

        $errors = $lookbook->validateHostSpotFields();

        if (count($errors) > 0) {
            die(json_encode(array('status' => false, 'errors' => $lookbook->displayError($errors))));
        }

        $id_spot = $lookbook->saveHotSpot();

        die(json_encode(array('status' => true, 'id_spot' => $id_spot, 'message' => 'Update Success !')));
    }

    public function ajaxProcessRemoveHotSpot()
    {
        $lookbook = new Jxlookbook();

        $lookbook->deleteHotSpot();

        die(json_encode(array('status' => true)));
    }

    public function ajaxProcessDeleteHotSpots()
    {
        JXLookBookHotSpotEntity::deleteByTabId(Tools::getValue('id_tab'));

        die(json_encode(array('status' => true)));
    }

    public function ajaxProcessUpdatePointPosition()
    {
        $id_point = Tools::getValue('id');
        $coordinates = Tools::getValue('coordinates');

        $success = JXLookBookHotSpotEntity::updateCoordinates($id_point, $coordinates);

        if (!$success) {
            die(json_encode(array('error' => 'Update Fail')));
        }
        die(json_encode(array('success' => 'Update Success !', 'error' => false)));
    }
}
