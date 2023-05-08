<?php
/**
 * 2017-2018 Zemez
 *
 * Sampledatainstall
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

if (!defined('_PS_VERSION_')) {
    exit;
}

class SampleDataInstallHelper
{
    public $context;
    public function __construct()
    {
        $this->context = Context::getContext();
    }

    public static function cmp($a, $b)
    {
        return strcmp($a['id_attribute_group'], $b['id_attribute_group']);
    }

    /**
     **	Get all features values for $this->generateFeatureValuesData()
     **	@return array (ID's)
     **/

    public function getAllFeaturesValues()
    {
        $data = array();
        $sql = 'SELECT `id_feature_value` FROM '._DB_PREFIX_.'feature_value';

        if (!$result = Db::getInstance()->executeS($sql)) {
            return false;
        }

        foreach ($result as $res) {
            $data[] = $res['id_feature_value'];
        }

        return $data;
    }

    public function getConvertedProductFeatures($features)
    {
        $result = '';
        if ($features) {
            $divider = '';
            foreach ($features as $position => $feature) {
                $sql = 'SELECT `id_feature`
                                FROM '._DB_PREFIX_.'feature_lang
                                WHERE `name` = "'.pSql($feature['name']).'"';
                $sql1 = 'SELECT `id_feature_value`
                                FROM '._DB_PREFIX_.'feature_value_lang
                                WHERE `value` = "'.pSql($feature['value']).'"';

                $id_feature = Db::getInstance()->getValue($sql);
                $id_feature_value = Db::getInstance()->getValue($sql1);
                $result .= $divider.$id_feature.':'.$id_feature_value.':'.($position + 1);
                $divider = ',';
            }
        }
        return $result;
    }

    /**
     **	Get all products attributes for $this->generateProductAttributesData()
     **  @return array
     **/
    public function getAllProductsAttributes()
    {
        $data = array();
        $sql = 'SELECT pa.`id_product_attribute`
				FROM '._DB_PREFIX_.'product_attribute pa
				LEFT JOIN '._DB_PREFIX_.'product_attribute_shop pas
				ON (pa.`id_product_attribute` = pas.`id_product_attribute`)
				AND pas.`id_shop` = '.$this->context->shop->id.'
				AND pa.`id_product` IS NOT NULL';
        if (!$result = Db::getInstance()->executeS($sql)) {
            return false;
        }

        foreach ($result as $res) {
            $data[] = $res['id_product_attribute'];
        }

        return $data;
    }

    /**
     **	Get attribute values for $this->generateProductAttributesData()
     **  @return array
     **/
    public function getAttributeValues($id_product_attribute)
    {
        $data = array();
        $sql = 'SELECT `id_attribute`
				FROM '._DB_PREFIX_.'product_attribute_combination
				WHERE `id_product_attribute` = '.$id_product_attribute;

        if (!$result = Db::getInstance()->executeS($sql)) {
            return false;
        }

        foreach ($result as $res) {
            $data[] = $res['id_attribute'];
        }

        return $data;
    }

    /**
     **	get all products packs for $this->generateProductPackData()
     ** 	@return array
     **/
    public function getAllProductsPacks()
    {
        $sql = 'SELECT * FROM '._DB_PREFIX_.'pack';

        if (!$result = Db::getInstance()->executeS($sql)) {
            return false;
        }

        return $result;
    }

    /**
     **	Get all stock availible data for $this->generateStockAvailableData()
     **	@return array(ID's)
     **/
    public function getAllStockAvailableData()
    {
        $data = array();
        $sql = 'SELECT `id_stock_available`
				FROM '._DB_PREFIX_.'stock_available
				WHERE `id_shop` = '.$this->context->shop->id;
        if (!$result = Db::getInstance()->executeS($sql))
            return false;
        foreach ($result as $res)
            $data[] = $res['id_stock_available'];
        return $data;
    }

    public function getCustomersAddresses()
    {
        $sql = 'SELECT `id_address`, `active`
				FROM '._DB_PREFIX_.'address
				ORDER BY `id_address`';
        if (!$result = Db::getInstance()->executeS($sql)) {
            return false;
        }

        return $result;
    }

    public function getAliases()
    {
        $sql = 'SELECT * FROM '._DB_PREFIX_.'alias';

        if (!$result = Db::getInstance()->executeS($sql)) {
            return false;
        }

        return $result;
    }

    public function getAttachments()
    {
        $sql = 'SELECT * FROM '._DB_PREFIX_.'attachment';

        if (!$result = Db::getInstance()->executeS($sql)) {
            return false;
        }
        return $result;
    }

    /**
     ** transform multilevel array to one level for $this->generateCarrierData();
     ** @return array
     **/
    public function oneL($array)
    {
        $result = array();
        foreach ($array as $key => $value) {
            $result[$key] = $value['id_group'];
        }

        return $result;
    }

    /**
     ** Get all carriers zones by id
     ** @return array
     **/
    public function getCarrierZones($id_carrier)
    {
        $data = array();
        $sql = 'SELECT `id_zone`
				FROM '._DB_PREFIX_.'carrier_zone
				WHERE `id_carrier` = '.$id_carrier;
        if (!$result = Db::getInstance()->executeS($sql)) {
            return false;
        }
        foreach ($result as $res) {
            $data[] = $res['id_zone'];
        }

        return $data;
    }

    public function getRangePriceByCarrier($range, $id_carrier)
    {
        $data = array();
        $sql = 'SELECT `delimiter1`, `delimiter2`
				FROM '._DB_PREFIX_.$range.'
				WHERE `id_carrier` = '.$id_carrier;
        if (!$result = Db::getInstance()->executeS($sql)) {
            return false;
        }

        foreach ($result as $res) {
            $data[] = $res['delimiter1'].'-'.$res['delimiter2'];
        }

        return $data;
    }

    public function getCartRules()
    {
        $sql = 'SELECT `id_cart_rule` FROM '._DB_PREFIX_.'cart_rule';

        if (!$result = Db::getInstance()->executeS($sql)) {
            return false;
        }

        return $result;
    }

    public function getDeliveries()
    {
        $sql = 'SELECT `id_delivery` FROM '._DB_PREFIX_.'delivery';
        if (!$result = Db::getInstance()->executeS($sql)) {
            return false;
        }

        return $result;
    }

    /**
     **	Get all specific prices for $this->generateSpecificPriceData()
     **	@return array (IDs)
     **/
    public function getAllSpecificPrices()
    {
        $sql = 'SELECT `id_specific_price` FROM '._DB_PREFIX_.'specific_price';

        if (!$result = Db::getInstance()->executeS($sql)) {
            return false;
        }

        return $result;
    }

    /**
     **	Get all specific prices rule for $this->generateSpecificPriceRuleData()
     **	@return array (IDs)
     **/
    public function getAllSpecificPriceRules()
    {
        $sql = 'SELECT `id_specific_price_rule` FROM '._DB_PREFIX_.'specific_price_rule';

        if (!$result = Db::getInstance()->executeS($sql)) {
            return false;
        }

        return $result;
    }

    /**
     **	Get all tax rules for $this->generateTaxRuleData()
     ** @return array (ID's)
     **/
    public function getAllTaxRules()
    {
        $data = array();
        $sql  = 'SELECT `id_tax_rule` FROM '._DB_PREFIX_.'tax_rule';

        if (!$result = Db::getInstance()->executeS($sql)) {
            return false;
        }
        foreach ($result as $res) {
            $data[] = $res['id_tax_rule'];
        }

        return $data;
    }

    /**
     **	Get all infos for $this->generateInfosData()
     **	@retun array (ID's)
     **/
    public function getAllCustomText()
    {
        $data = array();

        $sql = 'SELECT ti.`id_info`
				FROM '._DB_PREFIX_.'info ti
				LEFT JOIN '._DB_PREFIX_.'info_shop tis
				ON(ti.`id_info`=tis.`id_info`)
				WHERE tis.`id_shop` = '.$this->context->shop->id;

        if (!$result = Db::getInstance()->executeS($sql)) {
            return false;
        }

        foreach ($result as $res) {
            $data[] = $res['id_info'];
        }

        return $data;
    }

    /**
     **	Get all home slides for $this->generateHomeSlidesData()
     **	@retun array (ID's)
     **/
    public function getAllHomeSlides()
    {
        $data = array();

        $sql = 'SELECT `id_homeslider_slides`
				FROM '._DB_PREFIX_.'homeslider
				WHERE `id_shop` = '.$this->context->shop->id;

        if (!$result = Db::getInstance()->executeS($sql)) {
            return false;
        }

        foreach ($result as $res) {
            $data[] = $res['id_homeslider_slides'];
        }

        return $data;
    }

    public function getAllConfigurations()
    {
        $sql = 'SELECT `name`, `value` FROM '._DB_PREFIX_.'configuration';

        if (!$result = Db::getInstance()->executeS($sql)) {
            return false;
        }

        return $result;
    }

    public function getWidgetLinks()
    {
        $sql = 'SELECT `id_link_block` FROM '._DB_PREFIX_.'link_block';

        return Db::getInstance()->executeS($sql);
    }
}
