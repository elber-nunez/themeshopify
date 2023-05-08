<?php
/*
* 2017-2018 Zemez
*
* JX Search
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
* @author     Zemez (Alexander Grosul)
* @copyright  2017-2018 Zemez
* @license    http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
*/

use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;

class JxSearchAjaxSearchModuleFrontController extends ModuleFrontController
{
    private $ajax_search = '';
    public function initContent()
    {
        $jxsearch = new Jxsearch();
        $jxsearchclass = new JxSearchSearch();
        $id_lang = Context::getContext()->language->id;
        $category_id = Tools::getValue('category');

        if (!$id_lang) {
            $id_lang = $this->context->language->id;
        }

        $query = Tools::replaceAccentedChars(urldecode(Tools::getValue('q')));
        $output = array();

        $search_results = $jxsearchclass->jxfind((int)$id_lang, $query, $category_id, 1, 1000, 'position', 'desc', true);

        if (is_array($search_results)) {
            foreach ($search_results as &$product) {
                $usetax = (Product::getTaxCalculationMethod((int)Context::getContext()->customer->id) != PS_TAX_EXC);

                $product = (new ProductAssembler($this->context))
                    ->assembleProduct(array('id_product' => $product['id_product']));
                $presenterFactory = new ProductPresenterFactory($this->context);
                $presentationSettings = $presenterFactory->getPresentationSettings();
                $presenter = new ProductListingPresenter(
                    new ImageRetriever(
                        $this->context->link
                    ),
                    $this->context->link,
                    new PriceFormatter(),
                    new ProductColorsRetriever(),
                    $this->context->getTranslator()
                );
                $result = $presenter->present(
                    $presentationSettings,
                    $product,
                    $this->context->language
                );
                $result['manufacturer_name'] = Manufacturer::getNameById($result['id_manufacturer']);
                $result['supplier_name'] = Supplier::getNameById($result['id_supplier']);
                $this->context->smarty->assign(
                    'jxsearchsettings',
                    array(
                        'jxsearch_image' => (bool)Configuration::get('PS_JXSEARCH_AJAX_IMAGE'),
                        'jxsearch_description' => (bool)Configuration::get('PS_JXSEARCH_AJAX_DESCRIPTION'),
                        'jxsearch_price' => (bool)Configuration::get('PS_JXSEARCH_AJAX_PRICE'),
                        'jxsearch_reference' => (bool)Configuration::get('PS_JXSEARCH_AJAX_REFERENCE'),
                        'jxsearch_manufacturer' => (bool)Configuration::get('PS_JXSEARCH_AJAX_MANUFACTURER'),
                        'display_supplier' => (bool)Configuration::get('PS_JXSEARCH_AJAX_SUPPLIERS')
                    )
                );

                $this->context->smarty->assign('usetax', $usetax);
                $this->context->smarty->assign('product', $result);

                $output[] = $jxsearch->display($jxsearch->getLocalPath(), '/views/templates/hook/_items/row.tpl');
            }
        }

        if (!count($output)) {
            $l = new Jxsearch();
            $this->ajaxDie(json_encode(array('empty' => $l->l('No product found'))));
        }

        $total = count($output);

        $this->ajaxDie(json_encode(array('result' => $output, 'total' => $total)));

        parent::initContent();
    }
}
