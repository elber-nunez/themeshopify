<?php
/**
* 2017-2018 Zemez
*
* JX Mega Menu
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

class AdminJXMegaMenuController extends ModuleAdminController
{
    public $styles = '';

    public function ajaxProcessTabupdate()
    {
        $jxmegamenu = new Jxmegamenu();
        $id_tab = Tools::getValue('id_tab');
        $megamenu = new MegaMenu($id_tab);
        if (Tools::isEmpty(Tools::getValue('data'))) {
            $data = 'empty'; // send if menu is empty for remove it from databese
        } else {
            $data = Tools::getValue('data');
        }

        if (!$megamenu->addMenuItem($data)) {
            die(json_encode(array('error_status' => $this->l('Update Fail'))));
        }
        $jxmegamenu->clearCache();
        die(json_encode(array('success_status' => $this->l('Update Success !'), 'error' => false)));
    }

    public function ajaxProcessUpdatePosition()
    {
        $jxmegamenu = new Jxmegamenu();
        $items = Tools::getValue('item');
        $total = count($items);
        $id_shop = (int)$this->context->shop->id;
        $success = true;
        for ($i = 1; $i <= $total; $i++) {
            $success &= Db::getInstance()->update(
                'jxmegamenu',
                array('sort_order' => $i),
                '`id_item` = '.preg_replace('/(item_)([0-9]+)/', '${2}', $items[$i - 1]).'
                AND `id_shop` ='.$id_shop
            );
        }
        if (!$success) {
            die(json_encode(array('error' => 'Update Fail')));
        }
        $jxmegamenu->clearCache();
        die(json_encode(array('success' => 'Update Success !', 'error' => false)));
    }

    public function ajaxProcessGenerateStyles()
    {
        $gdata = Tools::getValue('data');
        $gcssname = Tools::getValue('cssname');
        $hookname = Tools::getValue('hookname');
        $jxmegamenu = new Jxmegamenu();
        $result = true;
        foreach ($gdata as $data) {
            $data = explode('|', $data);
            // check if class has value
            if (!Tools::isEmpty(trim($data[1]))) {
                $this->styles .= '.'.$hookname.' .jxmegamenu_item.'.$data[0].' {';
                $data_values = explode('^,', $data[1]);
                foreach ($data_values as $value) {
                    $val = explode(':', str_replace('^', '', $value));
                    if (isset($val[1]) && !Tools::isEmpty($val[1])) {
                        $this->styles .= str_replace('^', '', $value).';';
                    }
                }
                $this->styles .= "}\n";
            }
        }
        // check is something to write in css
        if (!Tools::isEmpty($this->styles)) {
            $file = fopen(Jxmegamenu::stylePath().$gcssname.'.css', 'w');
            fwrite($file, $this->styles);
            $result &= fclose($file);
            $result &= $jxmegamenu->generateUniqueStyles();
        }
        if ($result) {
            die(json_encode(array('status' => 'success', 'message' => $this->l('Update Success !'))));
        }
        die(json_encode(array('status' => 'error', 'message' => $this->l('Update Fail'))));
    }

    public function ajaxProcessResetStyles()
    {
        $gcssname = Tools::getValue('cssname');
        $jxmegamenu = new Jxmegamenu();
        $result = true;

        if (file_exists(Jxmegamenu::stylePath().$gcssname.'.css')) {
            $result &= @unlink(Jxmegamenu::stylePath().$gcssname.'.css');
            $result &= $jxmegamenu->generateUniqueStyles();
        }

        if ($result) {
            die(json_encode(array('status' => 'success', 'message' => $this->l('Reset success !'))));
        }
        die(json_encode(array('status' => 'error', 'message' => $this->l('Reset Fail'))));
    }

    public function ajaxProcessGetSettingsModal()
    {
        $hookname = Tools::getValue('hookname');
        $jxmegamenu = new Jxmegamenu();
        $settings = new MegaMenuSettings();
        $getsettingsid = $settings->getIdByShop((int)$this->context->shop->id, $hookname);

        die(Tools::jsonEncode(array('status' => 'success', 'content' => $jxmegamenu->getSettingsModal($getsettingsid))));
    }

    public function ajaxProcessSaveSettings()
    {
        $hookname = Tools::getValue('hookname');
        $settings = new MegaMenuSettings();
        $getsettingsid = $settings->getIdByShop((int)$this->context->shop->id, $hookname);
        if ($getsettingsid) {
            $settings = new MegaMenuSettings($getsettingsid);
        }

        $settings->id_shop = (int)$this->context->shop->id;
        $settings->googleapi = Tools::getValue('googleapi');
        $settings->hook_name = $hookname;
        if (!$settings->save()) {
            die(Tools::jsonEncode(array('status' => 'error', 'message' => $this->l('An error occurred during settings saving!'))));
        } else {
            die(Tools::jsonEncode(array('status' => 'success', 'message' => $this->l('Settings successfully saved!'))));
        }
    }
}
