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

class AMPStructuredDataProduct
{
    public $product;
    public $structuredProduct;

    public function __construct(array $product)
    {
        $condition = '';
        $link = new Link();
        $this->product = $product;
        $this->structuredProduct['@type'] = 'Product';
        $this->structuredProduct['name'] = $this->product['name'];
        $this->structuredProduct['url'] = $link->getProductLink($this->product['id_product']);
        $this->structuredProduct['image'] = $this->product['cover']['bySize'][ImageType::getFormattedName('medium')]['url'];
        $this->structuredProduct['description'] = $this->product['description'];
        if (!isset($this->product['manufacturer_name'])) {
            $this->structuredProduct['brand'] = array('@type' => 'Thing', 'name' => Manufacturer::getNameById($this->product['id_manufacturer']));
        } else {
            $this->structuredProduct['brand'] = array('@type' => 'Thing', 'name' => $this->product['manufacturer_name']);
        }
        if ($this->product['embedded_attributes']['condition'] && $this->product['embedded_attributes']['condition'] == 'new') {
            $condition = 'http://schema.org/NewCondition';
        } elseif ($this->product['embedded_attributes']['condition'] && $this->product['embedded_attributes']['condition'] == 'used') {
            $condition = 'http://schema.org/UsedCondition';
        }
        if ($this->product['embedded_attributes']['quantity'] > 0) {
            $inStock = 'http://schema.org/InStock';
        } else {
            $inStock = 'http://schema.org/OutOfStock';
        }
        $this->structuredProduct['offers'] = array(
            '@type' => 'Offer',
            'priceCurrency' => Context::getContext()->currency->iso_code,
            'price' => $this->product['embedded_attributes']['price'],
            'itemCondition' => $condition,
            'availability' => $inStock
        );
    }

    public function setContext()
    {
        $this->structuredProduct['@context'] = 'http://schema.org/';

        return $this;
    }

    public function getStructuredProduct()
    {
        return $this->structuredProduct;
    }
}
