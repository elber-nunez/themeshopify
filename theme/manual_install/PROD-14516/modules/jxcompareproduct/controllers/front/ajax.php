<?php
/**
 * 2017-2018 Zemez
 *
 * JX Compare Product
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

class JxCompareProductAjaxModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        $action = Tools::getValue('action');
        $action = str_replace(' ', '', ucwords(str_replace('-', ' ', $action)));

        if (!empty($action) && method_exists($this, 'ajax'.$action)) {
            $this->{'ajax'.$action}();
        } else {
            die(json_encode(array('error' => 'method doesn\'t exist')));
        }
    }

    public function ajaxRefreshPreview()
    {
        $id_products = Tools::getValue('products');
        ob_end_clean();
        header('Content-Type: application/json');
        $jxcompareproduct = new Jxcompareproduct();
        $content = $jxcompareproduct->getPreview($id_products);

        die(json_encode(['status' => true, 'response' => $content]));

    }

    public function ajaxAddProductToPreview()
    {
        $id_product = Tools::getValue('id_product');

        if ($id_product) {
            ob_end_clean();
            header('Content-Type: application/json');
            $jxcompareproduct = new Jxcompareproduct();
            $content = $jxcompareproduct->getPreviewProduct($id_product);

            die(json_encode(['status' => true, 'response' => $content]));
        }
    }

    public function ajaxShowModal()
    {
        $id_products = Tools::getValue('products');
        ob_end_clean();
        header('Content-Type: application/json');
        $jxcompareproduct = new Jxcompareproduct();
        $content = $jxcompareproduct->getModal($id_products);

        die(json_encode(['status' => true, 'response' => $content]));
    }
}
