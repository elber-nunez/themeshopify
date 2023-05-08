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

use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchQuery;
use PrestaShop\PrestaShop\Core\Product\Search\SortOrder;

class JxSearchJxSearchModuleFrontController extends ProductListingFrontController
{
    public $php_self = 'jxsearch';
    /**
     * Initializes controller.
     *
     * @see FrontController::init()
     *
     * @throws PrestaShopException
     */
    public function init()
    {
        parent::init();

        $this->doProductSearch('catalog/listing/search', array('entity' => 'search'));
    }

    protected function getProductSearchQuery()
    {
        $query = new ProductSearchQuery();
        $query
            ->setQueryType('jxsearch')
            ->setSortOrder(new SortOrder('product', 'date_add', 'desc'))
        ;

        return $query;
    }

    protected function getDefaultProductSearchProvider()
    {
        return new JxSearchProvider(
            $this->getTranslator()
        );
    }

    public function getListingLabel()
    {
        return $this->trans(
            'JX Search',
            array(),
            'Shop.Theme.Catalog'
        );
    }
}
