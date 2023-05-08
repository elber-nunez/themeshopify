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

use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;

class JxWishlistWishlistModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        $this->context = Context::getContext();
        $token = Tools::getValue('token');

        if ($token) {
            $wishlist = ClassJxWishlist::getByToken($token);
            $products = ClassJxWishlist::getProductByIdWishlist((int)$wishlist['id_wishlist']);
            $wishlists = ClassJxWishlist::getByIdCustomer((int)$wishlist['id_customer']);

            foreach ($products as $key => $slide) {
                $image = new Image();
                $product = (new ProductAssembler($this->context))->assembleProduct(array('id_product' => $slide['id_product'], 'id_product_attribute' => $slide['id_product_attribute']));
                $presenterFactory = new ProductPresenterFactory($this->context);
                $presentationSettings = $presenterFactory->getPresentationSettings();
                $presenter = new ProductListingPresenter(new ImageRetriever($this->context->link), $this->context->link, new PriceFormatter(), new ProductColorsRetriever(), $this->context->getTranslator());
                $products[$key]['info_array'] = $presenter->present($presentationSettings, $product, $this->context->language);
                $products[$key]['image'] = $image->getCover($slide['id_product']);
            }

            foreach ($wishlists as $key => $item) {
                if ($item['id_wishlist'] == $wishlist['id_wishlist']) {
                    unset($wishlists[$key]);
                    break;
                }
            }

            $this->context->smarty->assign(
                array(
                    'current_wishlist' => $wishlist,
                    'token' => $token,
                    'wishlists' => $wishlists,
                    'products' => $products,
                )
            );
        }

        $this->setTemplate('module:jxwishlist/views/templates/front/wishlist.tpl');
    }
}
