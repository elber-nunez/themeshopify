<?php
/**
 * 2017-2019 Zemez
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
 *  @copyright 2017-2019 Zemez
 *  @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
 */

class SampledatainstallTabManager
{
    private $module;
    /**
     * @var array main JX Modules tab
     */
    private $jxMainTab = array(
        'name' => 'JX Modules',
        'class_name' => 'AdminJxModules'
    );
    /**
     * @var array the main module tab
     */
    private $moduleMainTab = array(
        'name'              => 'Sample Data Install',
        'class_name'        => 'AdminSampleDataInstall',
        'icon'              => 'unarchive',
        'parent_class_name' => 'AdminJxModules'
    );
    /**
     * @var array related module's tabs
     */
    private $moduleTabs = array(
        array(
            'name'       => 'Import Data',
            'class_name' => 'AdminSampleDataInstallImport',
            'parent_class_name' => 'AdminSampleDataInstall'
        ),
        array(
            'name'       => 'Export Data',
            'class_name' => 'AdminSampleDataInstallExport',
            'parent_class_name' => 'AdminSampleDataInstall'
        )
    );

    /**
     * TabManager constructor.
     *
     * @param $module the module's name
     */
    public function __construct()
    {
        $this->module = 'sampledatainstall';
    }

    /**
     * Install common JX modules tab if it doesn't already exist and install all module's tabs
     *
     * @return bool
     */
    public function installTabs()
    {
        if ($mainTabId = $this->installMainTab($this->jxMainTab, false)) {
            if ($this->installModuleTab($this->moduleMainTab, $mainTabId)) {
                if ($this->installChildrenTabs()) {
                    return true;
                }
            }
            return true;
        }

        return false;
    }

    /**
     * Install top JX Modules tab. Check if it already exists and install it if not
     *
     * @param      $tab determination of tab
     * @param bool $module is the tab related to certain module or it is common
     *
     * @return bool|int main tab id if everything ok or error if errors occurred
     */
    public function installMainTab($tab, $module = true)
    {
        if ($tabId = \Tab::getIdFromClassName($tab['class_name'])) {
            return $tabId;
        } else {
            return $this->installTab($tab, $module);
        }
    }

    /**
     * Install the module main tab related to the top tab
     * @param $tab determination of tab
     * @param $id_parent id of the top tab
     *
     * @return bool|int
     */
    public function installModuleTab($tab, $id_parent)
    {
        $tab = array_merge($tab, array('id_parent' => $id_parent));
        return $this->installTab($tab);
    }

    /**
     * Install all related module's tabs.
     * @param $id_parent id of the module top tab
     *
     * @return bool|int
     */
    public function installChildrenTabs()
    {
        $result = true;
        foreach ($this->moduleTabs as $tab) {
            $id_parent = Tab::getIdFromClassName($tab['parent_class_name']);
            $result &= $this->installModuleTab($tab, $id_parent);
        }

        return $result;
    }

    /**
     * Install tab
     *
     * @param      $tab
     * @param bool $module
     *
     * @return bool|int
     */
    private function installTab($tab, $module = true)
    {
        $t = new \Tab();
        if ($module) {
            $t->module = $this->module;
        }
        $t->class_name = $tab['class_name'];
        if (isset($tab['icon'])) {
            $t->icon = $tab['icon'];
        }
        if (isset($tab['id_parent'])) {
            $t->id_parent = $tab['id_parent'];
        }
        foreach (\Language::getLanguages(false) as $language) {
            $t->name[$language['id_lang']] = $tab['name'];
        }

        if (!$t->save()) {
            return false;
        }

        return $t->id;
    }

    /**
     * Remove tab if it doesn't contain any tabs related to other modules
     *
     * @return bool
     */
    public function removeTab()
    {
        $id_tab = \Tab::getIdFromClassName('AdminJxModules');
        if ($id_tab && \Tab::getNbTabs($id_tab) !== 1) {
            return true;
        } else {
            $tab = new \Tab($id_tab);
            if (!$tab->delete()) {
                return false;
            }
        }

        return true;
    }
}
