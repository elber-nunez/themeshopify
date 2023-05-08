<?php
/**
 * 2017-2018 Zemez
 *
 * JX Accelerated Mobile Page
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

use PrestaShop\PrestaShop\Core\Product\ProductPresenter;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;

class JxAmpProductModuleFrontController extends AMPFrontControllerCore
{
    public $product;
    public $assembledProduct;
    protected $urlParameters;
    public $id_product;
    private $combinations;
    private $topGroup;
    private $groups;
    private $defaultCombination;
    private $filter = array();
    private $jsonUrl;
    public function __construct($pageName, $templateName)
    {
        $pageName = 'product';
        $templateName = $pageName;
        if (Tools::getValue('id_product')) {
            $this->id_product = Tools::getValue('id_product');
            $this->parameters = array('id_product' => $this->id_product);
        }

        parent::__construct($pageName, $templateName);
    }

    /**
     * Get product information with attribute
     *
     * @param $id_product_attribute
     *
     * @return array
     */
    public function getProduct($id_product_attribute)
    {
        $assembler = new ProductAssembler($this->context);
        $presenterFactory = new ProductPresenterFactory($this->context);
        $presentationSettings = $presenterFactory->getPresentationSettings();
        $presenter = new ProductPresenter(
            new ImageRetriever(
                $this->context->link
            ),
            $this->context->link,
            new PriceFormatter(),
            new ProductColorsRetriever(),
            $this->context->getTranslator()
        );

        return $this->assembledProduct = $presenter->present(
            $presentationSettings,
            $assembler->assembleProduct(array('id_product' => $this->id_product, 'id_product_attribute' => $id_product_attribute)),
            $this->context->language
        );
    }

    public function initContent()
    {
        // send "Add to cart" request
        if (Tools::getIsset('addtocart')) {
            // add necessary AMP CORS headers
            if (!empty($_POST)) {
                parent::setHeaders();
            }

            if (!Tools::getValue('quantity') || !Tools::getValue('id_product') || !Tools::getValue('id_product_attribute')) {
                die(json_encode(array('status' => false, 'result' => $this->l('An error occurred during a product adding'))));
            }
            // init cart if no one exists
            $this->context->cart = $this->initAmpCart();
            $this->context->cart->updateQty(Tools::getValue('quantity'), Tools::getValue('id_product'), Tools::getValue('id_product_attribute'));
            // send response
            die(json_encode(array('status' => true, 'result' => $this->l('The product successfully added to cart'))));
        }
        $imageRetriever = new ImageRetriever($this->context->link);
        $this->product = new Product($this->id_product, true, $this->context->language->id);
        // get all available product combinations
        $this->defaultCombination = Product::getDefaultAttribute($this->id_product);
        // get product images with information about them
        $productImages = $this->product->getImages($this->context->language->id);
        if ($productImages) {
            foreach ($productImages as $key => $productImage) {
                $productImages[$key]['images'] = $imageRetriever->getImage($this->product, $productImage['id_image']);
            }
        }
        $link = new Link();
        $product = $this->getProduct($this->defaultCombination);
        // assign product for template
        $this->context->smarty->assign('product', $product);
        // build structured data for Google search
        if ($product) {
            $structuredData = new AMPStructuredDataProduct($product);
            $this->context->smarty->assign('microdata', json_encode($structuredData->setContext()->getStructuredProduct()));
        }
        // init product template building
        $this->buildProductTemplate();
        $this->context->smarty->assign('product_images', $productImages);
        $this->context->smarty->assign('top_filter', $this->topGroup);
        $this->context->smarty->assign('defaults', $this->combinations[$this->defaultCombination]);
        $this->context->smarty->assign('default_id_product_attribute', $this->defaultCombination);
        $this->context->smarty->assign('jsonUrl', $this->jsonUrl);
        $this->context->smarty->assign('filter', $this->filter);
        $this->context->smarty->assign('amp_canonical', $link->getProductLink($this->id_product));
        if (Tools::getIsset('ajax')) {
            parent::setHeaders();
            die(json_encode($this->refreshJson()));
        }

        parent::initContent();
    }

    /**
     * Refresh page via Ajax
     *
     * @return mixed
     */
    private function refreshJson()
    {
        $parameters = $this->filterParameters();
        $this->updateDefaultCombination($parameters);
        $this->setDefaultParameters();
        $this->getProduct($this->defaultCombination);

        return $this->buildResultJson();
    }

    /**
     * Filter only valid URL parameters to rebuild an query.
     * Preposition "i" is used in every parameter because it's not allowed
     * to use numbers as a key for AMP State response
     *
     * @return bool
     */
    private function filterParameters()
    {
        $parameters = Tools::getAllValues();
        if (!$parameters) {
            return false;
        }

        foreach ($parameters as $key => $value) {
            $p = str_replace('i', '', $key);
            if (Validate::isInt($p) && Validate::isInt($value)) {
                $this->urlParameters[$key] = $value;
            }
        }

        return $this->urlParameters;
    }

    /**
     * Method which set default combination on page init
     * or after combination has changed from the front-end.
     * Set the default combination if no one exists for current parameters
     *
     *@param $parameters
     */
    private function updateDefaultCombination($parameters)
    {
        if ($parameters) {
            foreach ($this->combinations as $item => $combination) {
                $default = true;
                foreach ($parameters as $key => $value) {
                    if (!isset($combination[$key]) || $combination[$key] != $value) {
                        $default &= false;
                    }
                }
                // if there is no combination with such parameters - set default one
                if ($default) {
                    $this->defaultCombination = $item;
                }
            }
        }
    }

    /* TODO: remove in future */
    protected function findFirstCombination()
    {
        $mainParameter = array_keys($this->urlParameters)[0];
        foreach ($this->combinations as $item => $combination) {
            if ($combination[$mainParameter] == $this->urlParameters[$mainParameter]) {
                return $item;
            }
        }

        return false;
    }

    /**
     * Build template step-by-step
     */
    private function buildProductTemplate()
    {
        $product = $this->product;

        $this->getProductsCombinationsIds($product);

        $this->fillProductsCombinationsGroups($product);

        $this->buildJsonUrl();

        $this->buildFilter($product);
    }

    protected function setDefaultParameters()
    {
        if ($this->combinations[$this->defaultCombination]) {
            foreach ($this->combinations[$this->defaultCombination] as $key => $value) {
                $this->urlParameters[$key] = $value;
            }
        }
    }

    /**
     * Build AMP State response. Every new response is merging with old ones
     * so we need to override all information in each iteration
     *
     * @return mixed
     */
    protected function buildResultJson()
    {
        // hack: fill empty field to override empty fields.
        // It is necessary because AMP-STATE just merge new
        // result, so we need to override old ones
        if ($this->combinations) {
            foreach ($this->combinations as $id_combination => $item) {
                foreach ($item as $keys => $value) {
                    $this->groups[$keys]['e'.$value] = 0;
                }
            }
            foreach ($this->combinations as $id_combination => $item) {
                $k = 0;
                foreach ($item as $key => $value) {
                    if ($key == array_keys($this->urlParameters)[0]) {
                        $this->groups[$key]['e'.$value] = $value;
                    }
                    if ($value == $this->urlParameters[$key]) {
                        if (next($item) && key($item)) {
                            $this->groups[key($item)]['e'.$this->combinations[$id_combination][key($item)]] = $this->combinations[$id_combination][key($item)];
                        }
                    }
                    $k++;
                }
            }
        }

        $this->groups['defaults'] = $this->combinations[$this->defaultCombination];
        $this->groups['defaults']['price'] = $this->assembledProduct['price'];
        $this->groups['defaults']['regular_price'] = $this->assembledProduct['regular_price'];
        $this->groups['defaults']['has_discount'] = $this->assembledProduct['has_discount'];
        $this->groups['defaults']['minimal_quantity'] = $this->assembledProduct['minimal_quantity'];
        $this->groups['defaults']['available_for_order'] = $this->assembledProduct['available_for_order'];
        if ($this->assembledProduct['quantity']) {
            $this->groups['defaults']['quantity'] = $this->assembledProduct['quantity'];
        } else {
            $this->groups['defaults']['quantity'] = '0';
        }
        $this->groups['id_attribute'] = $this->defaultCombination;

        return $this->groups;
    }

    protected function getProductsCombinationsIds(Product $product)
    {
        if ($attributesResume = $product->getAttributesResume($this->context->language->id)) {
            foreach ($attributesResume as $combination) {
                $this->combinations[$combination['id_product_attribute']] = array();
            }
        }
    }

    protected function fillProductsCombinationsGroups(Product $product)
    {
        if ($attributesGroup = $product->getAttributesGroups($this->context->language->id)) {
            foreach ($attributesGroup as $group) {
                $this->combinations[$group['id_product_attribute']]['i'.$group['id_attribute_group']] = $group['id_attribute'];
            }
        }
    }

    /**
     * Build an URL for AMP State. It is here in order to have correct URL independently of attributes quantities
     * @return string
     */
    protected function buildJsonUrl()
    {
        $url = '';
        $i = 1;
        if ($length = count($this->combinations[$this->defaultCombination])) {
            foreach ($this->combinations[$this->defaultCombination] as $key => $value) {
                if ($url == '' && $length > 1) {
                    $url .= "&".$key."=' + ".$key." + '";
                } elseif ($i == $length) {
                    $url .= "&".$key."=' + ".$key;
                } else {
                    $url .= "&".$key."=' + ".$key." + '";
                }
                $i++;
            }
        } else {
            $url = '\'';
        }

        return $this->jsonUrl = $url;
    }

    /**
     * Build filter based on available product attributes
     * Add preposition "i" for every element in due that AMP State response keys can't start with numbers
     *
     * @param Product $product
     */
    protected function buildFilter(Product $product)
    {
        if ($attributesGroups = $product->getAttributesGroups($this->context->language->id)) {
            foreach ($attributesGroups as $group) {
                if ($this->topGroup === null) {
                    $this->topGroup = 'i'.(int)$group['id_attribute_group'];
                }
                $this->filter['i'.$group['id_attribute_group']]['is_color_group'] = $group['is_color_group'];
                $this->filter['i'.$group['id_attribute_group']]['group_name'] = $group['group_name'];
                $this->filter['i'.$group['id_attribute_group']]['public_group_name'] = $group['public_group_name'];
                $this->filter['i'.$group['id_attribute_group']]['group_type'] = $group['group_type'];
                $this->filter['i'.$group['id_attribute_group']]['attributes'][$group['id_attribute']] = array('attribute_name' => $group['attribute_name'], 'attribute_color' => $group['attribute_color']);
            }
        }
    }

    /**
     * The method is necessary when no cart were created before first product addition from AMP page,
     * so we need to init cart at first (seems to be a core bug)
     * @return Cart
     */
    private function initAmpCart()
    {
        if ($this->context->cookie->id_cart) {
            $cart = new Cart($this->context->cookie->id_cart);
        }
        if (!isset($cart) || !$cart->id) {
            $cart = new Cart();
            $cart->id_customer = (int)($this->context->cookie->id_customer);
            $cart->id_address_delivery = (int)(Address::getFirstCustomerAddressId($cart->id_customer));
            $cart->id_address_invoice = $cart->id_address_delivery;
            $cart->id_lang = (int)($this->context->cookie->id_lang);
            $cart->id_currency = (int)($this->context->cookie->id_currency);
            $cart->id_carrier = 1;
            $cart->recyclable = 0;
            $cart->gift = 0;
            $cart->add();
            $this->context->cookie->id_cart = (int)($cart->id);
            $cart->update();
        }

        return $cart;
    }
}
