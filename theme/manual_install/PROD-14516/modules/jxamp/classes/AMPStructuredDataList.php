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

class AMPStructuredDataList
{
    public $category;
    public $manufacturer;
    public $supplier;
    public $structuredResult = array();
    protected $link;

    public function __construct()
    {
        $this->link = new Link();
        $this->structuredResult['context'] = 'http://schema.org/';
        $this->structuredResult['@type'] = 'ItemList';
        $this->structuredResult['itemListElement'] = array();
    }

    public function addStructuredProducts(array $products)
    {
        $this->setCount($products['totalProducts']);
        foreach ($products['products'] as $key => $product) {
            $this->structuredResult['itemListElement'][$key] = array('@type' => 'ListItem', 'position' => $key + 1);
            $structuredProduct = new AMPStructuredDataProduct($product);
            $this->structuredResult['itemListElement'][$key]['item'] = $structuredProduct->getStructuredProduct();
        }

        return $this;
    }

    public function setCategory(Category $category)
    {
        $this->category = $category;

        return $this;
    }

    public function setManufacturer(Manufacturer $manufacturer)
    {
        $this->manufacturer = $manufacturer;

        return $this;
    }

    public function setSupplier(Supplier $supplier)
    {
        $this->supplier = $supplier;

        return $this;
    }

    public function setCategoryName()
    {
        $this->structuredResult['name'] = $this->category->name;

        return $this;
    }

    public function setManufacturerName()
    {
        $this->structuredResult['name'] = $this->manufacturer->name;

        return $this;
    }

    public function setSupplierName()
    {
        $this->structuredResult['name'] = $this->supplier->name;

        return $this;
    }

    public function setCategoryDescription()
    {
        $this->structuredResult['description'] = $this->category->description;

        return $this;
    }

    public function setManufacturerDescription()
    {
        $this->structuredResult['description'] = $this->manufacturer->description;

        return $this;
    }

    public function setSupplierDescription()
    {
        $this->structuredResult['description'] = $this->supplier->description;

        return $this;
    }

    public function setCategoryUrl()
    {
        $this->structuredResult['url'] = $this->link->getCategoryLink($this->category);

        return $this;
    }

    public function setManufacturerUrl()
    {
        $this->structuredResult['url'] = $this->link->getManufacturerLink($this->manufacturer);

        return $this;
    }

    public function setSupplierUrl()
    {
        $this->structuredResult['url'] = $this->link->getSupplierLink($this->supplier);

        return $this;
    }


    protected function setCount($count)
    {
        $this->structuredResult['numberOfItems'] = $count;
    }

    public function structureResult()
    {
        return $this->structuredResult;
    }
}
