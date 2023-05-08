<?php
/**
 * 2017-2018 Zemez
 *
 * JX Mega Layout
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
 *  @author    Zemez (Alexander Grosul & Alexander Pervakov)
 *  @copyright 2017-2018 Zemez
 *  @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class JXMegaLayoutExtraSlider extends ObjectModel
{
    public $name;
    public $content;
    public $specific_class;
    public $visible_items;
    public $items_scroll;
    public $margin;
    public $speed;
    public $auto_scroll;
    public $pause;
    public $loop;
    public $pager;
    public $controls;
    public $auto_height;
    public static $definition = array(
        'table'     => 'jxmegalayout_extra_slider',
        'primary'   => 'id_extra_slider',
        'multilang' => true,
        'fields'    => array(
            'name'           => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'lang' => true),
            'content'        => array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 4000),
            'specific_class' => array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 128),
            'visible_items'  => array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true),
            'items_scroll'   => array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true),
            'margin'         => array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true),
            'speed'          => array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true),
            'auto_scroll'    => array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true),
            'pause'          => array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true),
            'loop'           => array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true),
            'pager'          => array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true),
            'controls'       => array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true),
            'auto_height'    => array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true)
        ),
    );

    /**
     * Extend method in order to remove all related slides
     *
     * @return bool
     */
    public function delete()
    {
        $this->removeSliderSlides();
        return parent::delete();
    }

    /**
     * Remove all related slides
     * @return bool
     */
    private function removeSliderSlides()
    {
        return Db::getInstance()->delete('jxmegalayout_extra_slider_item', '`id_extra_slider` = '.(int)$this->id);
    }

    /**
     * Update all slides after each slider modification
     * At start remove every old relation and than add new ones
     *
     * @param $slides - list of new related slides
     *
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    public function updateSlides($slides)
    {
        $result = true;
        $result &= $this->removeSliderSlides();
        if (count($slides)) {
            foreach ($slides as $slide) {
                $info = explode('-', $slide);
                $result &= Db::getInstance()->insert(
                    'jxmegalayout_extra_slider_item',
                    array('id_extra_slider' => (int)$this->id, 'type' => pSQL($info[0]), 'id_content' => (int)$info[1])
                );
            }
        }

        return $result;
    }

    /**
     * Get the list of all available sliders
     *
     * @param $id_lang
     *
     * @return array|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public static function getList($id_lang)
    {
        return Db::getInstance()->executeS(
            'SELECT *, jes.`id_extra_slider` as `id`
            FROM '._DB_PREFIX_.'jxmegalayout_extra_slider jes
            LEFT JOIN '._DB_PREFIX_.'jxmegalayout_extra_slider_lang jesl
            ON(jes.`id_extra_slider` = jesl.`id_extra_slider`)
            WHERE jesl.`id_lang` = '.(int)$id_lang
        );
    }

    /**
     * Get all related to slider items, with information about them
     *
     * @param $id_slider
     * @param $id_lang
     * @param $front
     *
     * @return array
     * @throws PrestaShopDatabaseException
     */
    public static function getSlides($id_slider, $id_lang, $front = false)
    {
        $jxmegalayout = new Jxmegalayout();
        $result = array();
        $slides = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'jxmegalayout_extra_slider_item WHERE `id_extra_slider` = '.(int)$id_slider);
        if ($slides) {
            foreach ($slides as $key => $slide) {
                $result[$key]['entity'] = $slide;
                if ($slide['type'] != 'product' && $slide['type'] != 'post') {
                    $result[$key]['info'] = Db::getInstance()->getRow(
                        'SELECT t.*, tl.*, t.`id_extra_'.pSQL($slide['type']).'` as `id`
                     FROM '._DB_PREFIX_.'jxmegalayout_extra_'.$slide['type'].' t
                     LEFT JOIN '._DB_PREFIX_.'jxmegalayout_extra_'.$slide['type'].'_lang tl
                     ON(t.`id_extra_'.pSQL($slide['type']).'` = tl.`id_extra_'.pSQL($slide['type']).'`)
                     WHERE tl.`id_lang` = '.(int)$id_lang.'
                     AND t.`id_extra_'.pSQL($slide['type']).'` = '.(int)$slide['id_content']
                    );
                } else if ($slide['type'] == 'product') {
                    if (!$front) {
                        $result[$key]['info'] = array('id' => $slide['id_content'], 'name' => $slide['id_content']);
                    } else {
                        $result[$key]['info'] = $jxmegalayout->assembleProduct($slide['id_content']);
                    }
                } else if ($slide['type'] == 'post') {
                    if (!$front) {
                        $result[$key]['info'] = array('id' => $slide['id_content'], 'name' => $slide['id_content']);
                    } else {
                        if ($jxmegalayout->checkModuleStatus('jxblog')) {
                            $result[$key]['info'] = false;
                        } else {
                            $result[$key]['info'] = JXBlogPost::getPost(
                                $slide['id_content'],
                                Context::getContext()->language->id,
                                Context::getContext()->shop->id,
                                Context::getContext()->customer->id_default_group
                            );
                        }
                    }
                }
            }
        }

        return $result;
    }

    public static function getItem($id_item, $id_lang, $front = false)
    {
        $result = Db::getInstance()->getRow('
            SELECT jes.*, jesl.*
            FROM '._DB_PREFIX_.'jxmegalayout_extra_slider jes
            LEFT JOIN '._DB_PREFIX_.'jxmegalayout_extra_slider_lang jesl
            ON(jes.`id_extra_slider` = jesl.`id_extra_slider`)
            WHERE jes.`id_extra_slider` = '.(int)$id_item.'
            AND jesl.`id_lang` = '.(int)$id_lang);

        if ($result) {
            $result['slides'] = self::getSlides($id_item, $id_lang, $front);
        }

        return $result;
    }

    /**
     * Get all slides related to a slider
     *
     * @param $id_slider
     *
     * @return array|false|mysqli_result|null|PDOStatement|resource
     * @throws PrestaShopDatabaseException
     */
    public static function getSliderSlides($id_slider)
    {
        return Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'jxmegalayout_extra_slider_item WHERE `id_extra_slider` = '.(int)$id_slider);
    }

    public function addImportedSliderSlide($id_slide, $id_slider, $type, $id_content, $position)
    {
        return Db::getInstance()->insert(
            'jxmegalayout_extra_slider_item',
            array(
                'id_item' => (int)$id_slide,
                'id_extra_slider' => (int)$id_slider,
                'type' => pSQL($type),
                'id_content' => (int)$id_content,
                'position' => (int)$position
            )
        );
    }
}
