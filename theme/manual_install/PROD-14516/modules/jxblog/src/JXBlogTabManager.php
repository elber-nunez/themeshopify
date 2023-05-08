<?php
/**
 * 2017-2018 Zemez
 *
 * JX Blog
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

class JXBlogTabManager
{
    private $module;
    private $mainTab;
    private $settingsTab;
    private $tabs;
    private $settingsSubTabs;

    public function __construct($module, $tabs, $settingsSubTabs)
    {
        $this->module = $module;
        $this->mainTab = array(
            'class_name' => 'AdminJXBlog',
            'module'     => $this->module->name,
            'name'       => 'JX Blog'
        );
        $this->settingsTab = array(
            'class_name' => 'AdminJXBlogSettings',
            'module'     => $this->module->name,
            'name'       => 'Settings'
        );
        $this->tabs = $tabs;
        $this->settingsSubTabs = $settingsSubTabs;
    }

    /**
     * Add related to module tabs to the main navigation menu
     *
     * @return bool
     */
    public function addTabs()
    {
        $tabId = TabCore::getIdFromClassName($this->mainTab['class_name']);

        foreach ($this->tabs as $item) {
            $this->addTab($item, $tabId);
        }

        $idSettingsTab = TabCore::getIdFromClassName($this->settingsTab['class_name']);
        if (!$idSettingsTab) {
            $idSettingsTab = $this->addTab($this->settingsTab, $tabId);
        }

        foreach ($this->settingsSubTabs as $newSubTab) {
            $this->addTab($newSubTab, $idSettingsTab);
        }

        return true;
    }

    protected function addTab($tab, $parent)
    {
        $t = new Tab();
        $t->class_name = $tab['class_name'];
        $t->id_parent = $parent;
        $t->module = $tab['module'];

        foreach ($this->module->languages as $lang) {
            $t->name[$lang['id_lang']] = $this->module->l($tab['name']);
        }

        if (!$t->save()) {
            return false;
        }

        return $t->id;
    }

    /**
     * Remove related to module tabs to the main navigation menu
     *
     * @return bool
     */
    public function removeTabs()
    {
        foreach (array_merge($this->tabs, $this->settingsSubTabs) as $t) {
            if ($t) {
                $t = new Tab(TabCore::getIdFromClassName($t['class_name']));
                if (!$t->delete()) {
                    return false;
                }
            }
        }

        return true;
    }
}
