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

class JxLookBookJxLookBookModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        $lookbook = new Jxlookbook();
        if ($id_collection = Tools::getValue('collection')) {
            $collection = new JXLookBookCollectionEntity($id_collection);
            if ($collection->template != '') {
                $tabs = $lookbook->repository->getTabs($id_collection, true);
                foreach ($tabs as $key => $tab) {
                    $products = array();
                    $tabs[$key]['hotspots'] = $lookbook->repository->getHotSpots($tab['id_tab']);
                    if (count($tabs[$key]['hotspots']) > 0) {
                        foreach ($tabs[$key]['hotspots'] as $hotspot_id => $hotspot) {
                            if ($hotspot['type'] == 1) {
                                $products = array_merge($products, $tabs[$key]['hotspots'][$hotspot_id]['product'] = $lookbook->buildTemplateProduct($lookbook->getProductsById(array('0' => $hotspot['id_product']))));
                            }
                        }
                    }
                    $tabs[$key]['products'] = $products;
                }

                $this->context->smarty->assign(array(
                    'name' => $collection->name[$this->context->language->id],
                    'base_url' => _PS_BASE_URL_.__PS_BASE_URI__,
                    'jx_page_name' => $collection->name[$this->context->language->id],
                    'tabs' => $tabs
                ));
                $content = $lookbook->display($lookbook->getPathUri(), 'views/templates/front/_templates/'.$collection->template.'.tpl');

                $this->context->smarty->assign(array(
                    'content' => $content
                ));
                $this->setTemplate('module:jxlookbook/views/templates/front/hook.tpl');
            } else {
                $this->setTemplate('module:jxlookbook/views/templates/front/notfound.tpl');
            }
        } else {
            $this->context->smarty->assign(array(
                'collections' => $lookbook->repository->getCollections(true),
                'base_dir' => _PS_BASE_URL_.__PS_BASE_URI__
            ));
            $this->setTemplate('module:jxlookbook/views/templates/front/lookbooks.tpl');
        }
    }
}
