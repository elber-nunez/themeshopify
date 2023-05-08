<?php
/**
 * 2002-2017 Jetimpex
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
 *  @author    Jetimpex
 *  @copyright 2002-2017 Jetimpex
 *  @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
 */

class JxLookBookPagesModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        if ($id_page = Tools::getValue('id_page')) {
            $lookbook = new Jxlookbook();
            $page = new JXLookBookCollections($id_page);
            $tabs = JXLookBookTabs::getAllTabs($id_page, true);
            foreach ($tabs as $key => $tab) {
                $products = array();
                $tabs[$key]['hotspots'] = JXLookBookHotSpots::getHotSpots($tab['id_tab']);
                if (count($tabs[$key]['hotspots']) > 0) {
                    foreach ($tabs[$key]['hotspots'] as $hotspot_id => $hotspot) {
                        if ($hotspot['type'] == 1) {
                            $products = array_merge($products, $tabs[$key]['hotspots'][$hotspot_id]['product'] = $lookbook->getProductsById(array('0' => $hotspot['id_product'])));
                        }
                    }
                }
                $tabs[$key]['products'] = $products;
            }
            $this->context->smarty->assign(array(
                'tabs' => $tabs,
                'jx_page_name' => $page->name[$this->context->language->id],
            ));
            $this->setTemplate('pages_templates/'.$page->template.'.tpl');
        }
    }
}
