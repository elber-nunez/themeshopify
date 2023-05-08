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

class AdminJXMegaLayoutController extends ModuleAdminController
{
    public function ajaxProcessUpdateLayoutItem()
    {
        $errors = array();
        $item_data = Tools::getValue('data');
        $id_item = Tools::getValue('id_item');
        if ($id_item != 'false') {
            $item = new JXMegaLayoutItems($id_item);
        } else {
            $item = new JXMegaLayoutItems();
            $item->id_unique = 'it_'.Tools::passwdGen(12, 'NO_NUMERIC');
        }
        $item->id_layout = $item_data['id_layout'];
        $item->id_parent = $item_data['id_parent'];
        $item->sort_order = $item_data['sort_order'];
        $item->col = $item_data['col'];
        $item->col_xs = $item_data['col'];
        $item->col_sm = $item_data['col_sm'];
        $item->col_md = $item_data['col_md'];
        $item->col_lg = $item_data['col_lg'];
        $item->col_xl = $item_data['col_xl'];
        $item->col_xxl = $item_data['col_xxl'];
        $item->module_name = $item_data['module_name'];
        $item->specific_class = $item_data['specific_class'];
        $item->extra_css = $item_data['extra_css'];
        if ($item->module_name == 'logo' || $item->module_name == 'copyright' || $item->module_name == 'tabs') {
            $item->type = 'block';
        } else {
            if (!empty($item_data['origin_hook'])) {
                $item->origin_hook = $item_data['origin_hook'];
            }
            $item->type = $item_data['type'];
        }
        if ($id_item == 'false') {
            if (!$item->add()) {
                $errors[] = $this->l('Error occurred while adding an item!');
            }
        } else {
            if (!$item->update()) {
                $errors[] = $this->l('Error occurred while saving an item!');
            }
        }
        if (count($errors)) {
            die(Tools::jsonEncode(
                array('status' => 'false', 'response_msg' => $this->l('Oops...something went wrong!'))
            ));
        }
        $item->id_item = $item->id;
        $jxmegalayout = new Jxmegalayout();
        $item_content = null;
        switch ($item_data['type']) {
            case 'module':
                $this->context->smarty->assign(
                    array(
                        'elem'     => get_object_vars($item),
                        'preview'  => false,
                        'position' => ''
                    )
                );
                $item_content = $jxmegalayout->display(
                    $jxmegalayout->getLocalPath(),
                    '/views/templates/admin/layouts/module.tpl'
                );
                break;
            case 'wrapper':
                $this->context->smarty->assign(
                    array(
                        'elem'     => get_object_vars($item),
                        'preview'  => false,
                        'position' => ''
                    )
                );
                $item_content = $jxmegalayout->display(
                    $jxmegalayout->getLocalPath(),
                    '/views/templates/admin/layouts/wrapper.tpl'
                );
                break;
            case 'row':
                $this->context->smarty->assign(
                    array(
                        'elem'     => get_object_vars($item),
                        'preview'  => false,
                        'position' => ''
                    )
                );
                $item_content = $jxmegalayout->display(
                    $jxmegalayout->getLocalPath(),
                    '/views/templates/admin/layouts/row.tpl'
                );
                break;
            case 'col':
                $class = $item_data['col'].' '.$item_data['col_xs'].' '.$item_data['col_sm'].' '.$item_data['col_md'].' '.$item_data['col_lg'].' '.$item_data['col_xl'].' '.$item_data['col_xxl'].' ';
                $this->context->smarty->assign(
                    array(
                        'elem'     => get_object_vars($item),
                        'preview'  => false,
                        'position' => '',
                        'class'    => $class
                    )
                );
                $item_content = $jxmegalayout->display(
                    $jxmegalayout->getLocalPath(),
                    '/views/templates/admin/layouts/col.tpl'
                );
                break;
            case 'block':
                $this->context->smarty->assign(
                    array(
                        'elem'     => get_object_vars($item),
                        'preview'  => false,
                        'position' => '',
                    )
                );
                $item_content = $jxmegalayout->display(
                    $jxmegalayout->getLocalPath(),
                    '/views/templates/admin/layouts/module.tpl'
                );
                break;
            case 'content':
                $this->context->smarty->assign(
                    array(
                        'elem'     => get_object_vars($item),
                        'preview'  => false,
                        'position' => '',
                        'info'     => '',
                    )
                );
                $item_content = $jxmegalayout->display(
                    $jxmegalayout->getLocalPath(),
                    '/views/templates/admin/layouts/content.tpl'
                );
                break;
        }
        die(Tools::jsonEncode(
            array('status' => 'true', 'id_item' => $item->id, 'id_unique' => $item->id_unique, 'response_msg' => $this->l(
                'Changes were saved successfully'
            ), 'content'   => $item_content)
        ));
    }

    public function ajaxProcessDeleteLayoutItem()
    {
        $id_items = Tools::getValue('id_item');
        if (count($id_items) < 1) {
            die(Tools::jsonEncode(array('status' => 'error', 'response_msg' => $this->l('Bad ID value'))));
        }
        foreach ($id_items as $id_item) {
            if (Tools::isEmpty($id_item)) {
                continue;
            }
            $item = new JXMegaLayoutItems($id_item);
            if (!$item->delete()) {
                die(Tools::jsonEncode(array('status' => 'error', 'response_msg' => $this->l('Can\'t delete item(s)'))));
            }
        }
        die(Tools::jsonEncode(
            array('status' => 'true', 'response_msg' => $this->l('Item(s) was/were deleted successfully'))
        ));
    }

    public function ajaxProcessUpdateLayoutItemsOrder()
    {
        $data = Tools::getValue('data');
        if (count($data) > 1) {
            foreach ($data as $id_item => $sort_order) {
                $item = new JXMegaLayoutItems($id_item);
                if (!Validate::isLoadedObject($item)) {
                    die(Tools::jsonEncode(array('status' => 'error', 'response_msg' => $this->l('Bad ID value'))));
                }
                $item->sort_order = $sort_order;
                if (!$item->update()) {
                    die(Tools::jsonEncode(
                        array('status' => 'error', 'response_msg' => $this->l(
                            'Sort order changes were not saved successfully'
                        ))
                    ));
                }
            }
            die(Tools::jsonEncode(
                array('status' => 'true', 'response_msg' => $this->l('Changes were saved successfully'))
            ));
        }
    }

    public function ajaxProcessLayoutPreview()
    {
        $id_layout = Tools::getValue('id_layout');
        $item = new Jxmegalayout();
        die(Tools::jsonEncode(array('status' => 'true', 'msg' => $item->getLayoutAdmin($id_layout, true))));
    }

    public function ajaxProcessLayoutExport()
    {
        $id_layout = Tools::getValue('id_layout');
        $obj = new JXMegalayoutExport();
        $href = $obj->init($id_layout);
        die(Tools::jsonEncode(array('status' => true, 'href' => $href)));
    }

    public function ajaxProcessLoadTool()
    {
        $tool_name = Tools::getValue('tool_name');
        $tools = new Jxmegalayout();
        $tool_content = $tools->renderToolContent($tool_name);
        die(Tools::jsonEncode(array('status' => 'true', 'content' => $tool_content)));
    }

    public function ajaxProcessGetItemStyles()
    {
        $id_unique = Tools::getValue('id_unique');
        $tools = new Jxmegalayout();
        $styles = $tools->getItemStyles($id_unique);
        die(Tools::jsonEncode(array('status' => 'true', 'content' => $styles)));
    }

    public function ajaxProcessSaveItemStyles()
    {
        $id_unique = Tools::getValue('id_unique');
        $data = Tools::getValue('data');
        if (!$data || Tools::isEmpty($data)) {
            die(Tools::jsonEncode(array('status' => 'true', 'message' => $this->l('Nothing to save'))));
        }
        $tools = new Jxmegalayout();
        if ($tools->saveItemStyles($id_unique, $data)) {
            die(Tools::jsonEncode(array('status' => 'true', 'message' => $this->l('Saved'))));
        }
        die(Tools::jsonEncode(array('status' => 'false', 'message' => $this->l('Some errors occurred'))));
    }

    public function ajaxProcessClearItemStyles()
    {
        $id_unique = Tools::getValue('id_unique');
        $tools = new Jxmegalayout();
        if ($tools->deleteItemStyles($id_unique)) {
            die(Tools::jsonEncode(array('status' => 'true', 'message' => $this->l('Item styles are removed'))));
        }
        die(Tools::jsonEncode(
            array('status' => 'false', 'message' => $this->l('Some errors occurred while removing styles'))
        ));
    }

    public function ajaxProcessAddLayoutForm()
    {
        $hook_name = Tools::getValue('hook_name');
        $layout = new Jxmegalayout();
        if (!$result = $layout->showMessage($hook_name, 'addLayout')) {
            die(Tools::jsonEncode(array('status' => 'false', 'message' => $this->l('Some errors occurred'))));
        }
        die(Tools::jsonEncode(array('status' => 'true', 'response_msg' => $result)));
    }

    public function ajaxProcessAddLayout()
    {
        $hook_name = Tools::getValue('hook_name');
        $layout_name = Tools::getValue('layout_name');
        $layout = new Jxmegalayout();
        if ((bool)JXMegaLayoutLayouts::getLayoutByName($layout_name)) {
            die(Tools::jsonEncode(
                array('status' => 'false', 'type' => 'popup', 'message' => $this->l('You have preset with this name'))
            ));
        }
        if (!$id_layout = $layout->addLayout($hook_name, $layout_name)) {
            die(Tools::jsonEncode(array('status' => 'false', 'message' => $this->l('Some errors occurred'))));
        }
        $new_layouts = Tools::jsonEncode(JXMegaLayoutLayouts::getLayoutsForHook($hook_name, $this->context->shop->id));
        die(Tools::jsonEncode(
            array('status'   => 'true', 'id_layout' => $id_layout, 'message' => $this->l(
                'The layout is successfully added.'
            ), 'new_layouts' => $new_layouts)
        ));
    }

    public function ajaxProcessAddModuleConfirmation()
    {
        $hook_name = Tools::getValue('hook_name');
        $id_layout = (int)Tools::getValue('id_layout');
        $jxmegalayout = new Jxmegalayout();
        if (!$form = $jxmegalayout->addModuleForm($hook_name, $id_layout)) {
            die(Tools::jsonEncode(array('status' => 'false', 'message' => $this->l('Some error occurred'))));
        }
        die(Tools::jsonEncode(array('status' => 'true', 'message' => $form)));
    }

    public function ajaxProcessLoadLayoutContent()
    {
        $id_layout = Tools::getValue('id_layout');
        $layout = new Jxmegalayout();
        if (!$result = $layout->renderLayoutContent($id_layout)) {
            die(Tools::jsonEncode(array('status' => 'false', 'message' => $this->l('Some errors occurred'))));
        }
        die(Tools::jsonEncode(array('status' => 'true', 'layout' => $result[0], 'layout_buttons' => $result[1])));
    }

    public function ajaxProcessGetLayoutRemoveConfirmation()
    {
        $id_layout = Tools::getValue('id_layout');
        $layout = new Jxmegalayout();
        if (!$result = $layout->showMessage($id_layout, 'layoutRemoveConf')) {
            die(Tools::jsonEncode(array('status' => 'false', 'message' => $this->l('Some errors occurred'))));
        }
        die(Tools::jsonEncode(array('status' => 'true', 'message' => $result)));
    }

    public function ajaxProcessRemoveLayout()
    {
        $id_layout = (int)Tools::getValue('id_layout');
        $layouts = JXMegaLayoutItems::getItems($id_layout);
        $jxmegalayout = new Jxmegalayout();
        $jxmegalayout->deleteFilesOfLayout($id_layout);
        if ($layouts && count($layouts) > 0) {
            foreach ($layouts as $layout) {
                $item = new JXMegaLayoutItems($layout['id_item']);
                if (!$item->delete()) {
                    die(Tools::jsonEncode(
                        array(
                            'status' => 'false',
                            'message' => $this->l('Some error occurred. Can\'t delete layout item').$item->id
                        )
                    ));
                }
            }
        }
        $tab = new JXMegaLayoutLayouts($id_layout);
        if (!$tab->delete() || !$tab->dropLayoutFromPages()) {
            die(Tools::jsonEncode(array('status' => 'false', 'message' => 'Can\'t delete layout')));
        }
        $hook_name = Tools::getValue('hook_name');
        $new_layouts = Tools::jsonEncode(JXMegaLayoutLayouts::getLayoutsForHook($hook_name, $this->context->shop->id));
        die(Tools::jsonEncode(
            array('status'   => 'true', 'message' => $this->l(
                'Layout is successfully removed'
            ), 'new_layouts' => $new_layouts)
        ));
    }

    public function ajaxProcessGetLayoutRenameConfirmation()
    {
        $id_layout = Tools::getValue('id_layout');
        $layout = new Jxmegalayout();
        $tab = new JXMegaLayoutLayouts($id_layout);
        if (!$result = $layout->showMessage($id_layout, 'layoutRenameConf', $tab->layout_name)) {
            die(Tools::jsonEncode(array('status' => 'false', 'message' => $this->l('Some errors occurred'))));
        }
        die(Tools::jsonEncode(array('status' => 'true', 'message' => $result)));
    }

    public function ajaxProcessRenameLayout()
    {
        $id_layout = (int)Tools::getValue('id_layout');
        $layout_name = Tools::getValue('layout_name');
        $jxmegalayout = new Jxmegalayout();
        if ((bool)JXMegaLayoutLayouts::getLayoutByName($layout_name)) {
            die(Tools::jsonEncode(
                array('status' => 'false', 'type' => 'popup', 'message' => $this->l('You have preset with this name'))
            ));
        } else {
            if (!$jxmegalayout->renameFilesOfLayout($id_layout, $layout_name)) {
                die(Tools::jsonEncode(
                    array('status' => 'false', 'message' => $this->l('Can\'t rename a layouts files'))
                ));
            }
        }
        $tab = new JXMegaLayoutLayouts($id_layout);
        $tab->layout_name = $layout_name;
        if (!$tab->update()) {
            die(Tools::jsonEncode(array('status' => 'false', 'message' => $this->l('Can\'t update a layout name'))));
        }
        die(Tools::jsonEncode(array('status' => 'true', 'message' => $this->l('Layout name is successfully changed'))));
    }

    public function ajaxProcessDisableLayout()
    {
        $id_layout = (int)Tools::getValue('id_layout');
        $jxmegalayout = new Jxmegalayout();
        $tab = new JXMegaLayoutLayouts($id_layout);
        $tab->status = 0;
        if (!$tab->update() || !$tab->disableLayoutForAllPages()) {
            die(Tools::jsonEncode(array('status' => 'false', 'message' => $this->l('Can\'t disable layout'))));
        }
        if (!$jxmegalayout->combineAllItemsStyles()) {
            die(Tools::jsonEncode(
                array('status' => 'false', 'message' => $this->l('Can\'t regenerate layout styles'))
            ));
        }
        die(Tools::jsonEncode(array('status' => 'true', 'message' => $this->l('Layout is disabled'))));
    }

    public function ajaxProcessEnableLayout()
    {
        $id_layout = (int)Tools::getValue('id_layout');
        $hook_name = Tools::getValue('hook_name');
        $pages = Tools::getValue('pages'); // pages list if assigned not for all
        $status = Tools::getValue('layout_status'); // current status of layout
        $type = ''; // set type for different responses after updating
        $jxmegalayout = new Jxmegalayout();
        // do if assigned for all pages
        if (!$pages) {
            $tabs = JXMegaLayoutLayouts::getLayoutsForHook($hook_name, $this->context->shop->id);
            if ($tabs) {
                foreach ($tabs as $layout) {
                    $tab = new JXMegaLayoutLayouts($layout['id_layout']);
                    $tab->status = 0;
                    if (!$tab->update()) {
                        die(Tools::jsonEncode(
                            array('status' => 'false', 'message' => $this->l('Can\'t disable previous layout'))
                        ));
                    }
                }
            }
            $item = new JXMegaLayoutLayouts($id_layout);
            $item->status = 1;
            if (!$item->update() || !$item->dropLayoutFromPages()) {
                die(Tools::jsonEncode(array('status' => 'false', 'message' => $this->l('Can\'t enable this layout'))));
            }
            $type = 'all'; // set type for response
            $response_pages = $this->l('All pages');
        } else {
            $item = new JXMegaLayoutLayouts($id_layout);
            // do if layout was just cleared but not enabled/disabled
            if ($pages == 'empty') {
                if ($item->dropLayoutFromPages()) {
                    die(Tools::jsonEncode(
                        array('status' => 'true', 'clear', 'message' => $this->l('Layout is saved'))
                    ));
                }
            }
            $item->status = 0;
            if (!$item->update()) {
                die(Tools::jsonEncode(array('status' => 'false', 'message' => $this->l('Can\'t enable this layout'))));
            }
            $item->setLayoutToPage($pages, $hook_name, $status);
            $response_pages = implode(', ', $pages);
            if ($status) {
                $type = 'sub';// set type for response
            }
        }
        if (!$jxmegalayout->combineAllItemsStyles()) {
            die(Tools::jsonEncode(
                array('status' => 'false', 'message' => $this->l('Can\'t regenerate layout styles'))
            ));
        }
        // do if different pages assigned
        if ($pages) {
            $relations = $this->l('assigned');
            if ($status) {
                $relations = $this->l('enabled');
            }
            die(Tools::jsonEncode(
                array(
                    'status'  => 'true',
                    'type'    => $type,
                    'message' => sprintf(
                        $this->l('Layout(s) is(are) %s for %s'),
                        $relations,
                        $response_pages
                    )
                )
            ));
        } else {
            die(Tools::jsonEncode(
                array(
                    'status'  => 'true',
                    'type'    => $type,
                    'message' => $this->l('Layout is enabled for All Pages')
                )
            ));
        }
    }

    public function ajaxProcessGetImportInfo()
    {
        $import = new JXMegaLayoutImport();
        $import_path = new Jxmegalayout();
        Jxmegalayout::cleanFolder($import_path->getLocalPath().'import/');
        $file_name = basename($_FILES['file']['name']);
        $upload_file = $import_path->getLocalPath().'import/'.$file_name;
        move_uploaded_file($_FILES['file']['tmp_name'], $upload_file);
        $preview = $import->layoutPreview($import_path->getLocalPath().'import/', $file_name);
        die(Tools::jsonEncode(array('status' => 'true', 'preview' => $preview)));
    }

    public function ajaxProcessImportLayout()
    {
        $import = new JXMegaLayoutImport();
        $import_path = new Jxmegalayout();
        Jxmegalayout::cleanFolder($import_path->getLocalPath().'import/');
        $file_name = basename($_FILES['file']['name']);
        $upload_file = $import_path->getLocalPath().'import/'.$file_name;
        move_uploaded_file($_FILES['file']['tmp_name'], $upload_file);
        $name_layout = Tools::getValue('name_layout');
        if ((bool)JXMegaLayoutLayouts::getLayoutByName($name_layout)) {
            die(Tools::jsonEncode(
                array('status' => 'false', 'type' => 'popup', 'message' => $this->l('You have preset with this name'))
            ));
        }
        $import->importLayout($import_path->getLocalPath().'import/', $file_name, false, $name_layout);
        die(Tools::jsonEncode(array('status' => 'true', 'response_msg' => $this->l('Successful import'))));
    }

    public function ajaxProcessLoadLayoutTab()
    {
        $layout_list = new Jxmegalayout();
        $hook_name = Tools::getValue('tab_name');
        $old_layouts = Tools::jsonDecode(Tools::getValue('old_layouts'), true);
        $new_layouts = Tools::jsonEncode($layout_list->checkNewLayouts($hook_name, $old_layouts));
        die(Tools::jsonEncode(
            array('status' => 'true', 'new_layouts' => $new_layouts, 'old_layouts' => Tools::jsonEncode(
                JXMegaLayoutLayouts::getLayoutsForHook($hook_name, $this->context->shop->id)
            ))
        ));
    }

    public function ajaxProcessAfterImport()
    {
        $import_path = new Jxmegalayout();
        $path = $import_path->getLocalPath().'import/';
        Jxmegalayout::cleanFolder($path);
        die(Tools::jsonEncode(array('status' => 'true')));
    }

    public function ajaxProcessAfterExport()
    {
        die(Tools::jsonEncode(array('status' => 'true')));
    }

    public function ajaxProcessResetToDefault()
    {
        // get all tabs for this store
        $layouts = JXMegaLayoutLayouts::getShopLayoutsIds();
        if ($layouts) {
            foreach ($layouts as $layout) {
                // if no layouts for this tab delete it immediately
                if (!$items = JXMegaLayoutItems::getItems($layout['id_layout'])) {
                    $current_layout = new JXMegaLayoutLayouts($layout['id_layout']);
                    if (!$current_layout->dropLayoutFromPages() || !$current_layout->delete()) {
                        die(Tools::jsonEncode(
                            array(
                                'status' => 'false',
                                'message' => $this->l('Some error occurred. Can\'t delete layout').$current_layout->id
                            )
                        ));
                    }
                    // if there is layouts for this tab delete all and delete tab after
                } else {
                    foreach ($items as $item) {
                        $current_item = new JXMegaLayoutItems($item['id_item']);
                        if (!$current_item->delete()) {
                            die(Tools::jsonEncode(
                                array(
                                    'status' => 'false',
                                    'message' => $this->l('Some error occurred. Can\'t delete layout item').$current_item->id
                                )
                            ));
                        }
                    }
                    $current_layout = new JXMegaLayoutLayouts($layout['id_layout']);
                    if (!$current_layout->dropLayoutFromPages() || !$current_layout->delete()) {
                        die(Tools::jsonEncode(
                            array(
                                'status' => 'false',
                                'message' => $this->l('Some error occurred. Can\'t delete layout item').$current_layout->id
                            )
                        ));
                    }
                }
            }
        }
        $jxmegalayout = new Jxmegalayout();
        // install default layouts from "default" folder
        if (!$jxmegalayout->installDefLayouts() || !$jxmegalayout->setDefaultPageInfoLayout()) {
            die(Tools::jsonEncode(array('status' => 'false', 'message' => $this->l('Can\'t load default layouts'))));
        }
        die(Tools::jsonEncode(array('status' => 'true', 'message' => $this->l('All data is successfully removed'))));
    }

    public function ajaxProcessReloadTab()
    {
        $tab_name = Tools::getValue('tab_name');
        $tab = new Jxmegalayout();
        $layouts_list = Tools::jsonEncode(JXMegaLayoutLayouts::getLayoutsForHook($tab_name, $this->context->shop->id));
        if (!$tab_content = $tab->renderLayoutTab($tab_name)) {
            die(Tools::jsonEncode(array('status' => 'false')));
        }
        die(Tools::jsonEncode(
            array('status' => 'true', 'tab_content' => $tab_content, 'layouts_list' => $layouts_list)
        ));
    }

    public function ajaxProcessUpdateOptionOptimize()
    {
        $optimize = new JXMegaLayoutOptimize();
        $optimize->optimize();
        Configuration::updateValue('JXMEGALAYOUT_OPTIMIZE', true);
        die(Tools::jsonEncode(array('status' => 'true', 'response_msg' => $this->l('Optimization is complete'))));
    }

    public function ajaxProcessUpdateOptionDeoptimize()
    {
        $optimize = new JXMegaLayoutOptimize();
        $optimize->deoptimize();
        Configuration::updateValue('JXMEGALAYOUT_OPTIMIZE', false);
        die(Tools::jsonEncode(array('status' => 'true', 'response_msg' => $this->l('Optimization is disabled'))));
    }

    public function ajaxProcessOptimizeMessage()
    {
        $optimize = new JXMegaLayoutOptimize();
        $optimize->deoptimize();
        Configuration::updateValue('JXMEGALAYOUT_OPTIMIZE', false);
        die(Tools::jsonEncode(array('status' => 'true')));
    }

    public function ajaxProcessShowMessages()
    {
        $value = Configuration::get('JXMEGALAYOUT_SHOW_MESSAGES');
        if ((bool)$value) {
            $optimize = new JXMegaLayoutOptimize();
            $optimize->deoptimize();
            Configuration::updateValue('JXMEGALAYOUT_OPTIMIZE', false);
            Configuration::updateValue('JXMEGALAYOUT_SHOW_MESSAGES', false);
            die(Tools::jsonEncode(array('status' => 'false', 'response_msg' => $this->l('Optimization is disabled'))));
        } else {
            $optimize = new JXMegaLayoutOptimize();
            $optimize->optimize();
            Configuration::updateValue('JXMEGALAYOUT_OPTIMIZE', true);
            Configuration::updateValue('JXMEGALAYOUT_SHOW_MESSAGES', true);
            die(Tools::jsonEncode(array('status' => 'true', 'response_msg' => $this->l('Optimization is complete'))));
        }
    }

    public function ajaxProcessUpdateProductInfoPage()
    {
        $theme_name = Tools::getValue('theme_name');
        if (Configuration::updateValue('jxmegalayout_poduct_layout', $theme_name)) {
            die(Tools::jsonEncode(
                array('status' => 'true', 'response_msg' => $this->l('Template successfully changed'))
            ));
        }
        die(Tools::jsonEncode(array('status' => 'false', 'response_msg' => $this->l('Error'))));
    }

    public function ajaxProcessGetCssClassesByUnique()
    {
        $jxmegalayout = new Jxmegalayout();
        $classes = $jxmegalayout->getModuleExtraCss(Tools::getValue('unique_id'));
        $active = JXMegaLayoutItems::getItemCssByUniqueId(Tools::getValue('unique_id'));
        die(Tools::jsonEncode(array('status' => 'true', 'classes' => $classes, 'active' => $active)));
    }

    /**
     * Load the extra content tabs with a data included
     */
    public function ajaxProcessLoadExtraContent()
    {
        $item_type = Tools::getValue('item_type');
        $id_item = Tools::getValue('id_item');
        $tools = new Jxmegalayout();
        $extra_content = $tools->renderToolExtraContent($item_type, $id_item);
        die(Tools::jsonEncode(array('status' => 'true', 'content' => $extra_content)));
    }

    /**
     * Attempt to save extra content
     */
    public function ajaxProcessSaveExtraItem()
    {
        $item_type = Tools::getValue('extra_content_type');
        $tools = new Jxmegalayout();
        $extra_content = $tools->saveExtraContent($item_type, Tools::getAllValues());
        die(json_encode(array('status' => $extra_content['status'], 'content' => $extra_content['report'])));
    }

    /**
     * Attempt to remove extra content
     */
    public function ajaxProcessRemoveExtraItem()
    {
        $item_type = Tools::getValue('item_type');
        $id_item = Tools::getValue('id_extra_item');
        $tools = new Jxmegalayout();
        $extra_content = $tools->removeExtraContent($item_type, $id_item);
        die(Tools::jsonEncode(array('status' => 'true', 'content' => $extra_content)));
    }

    public function ajaxProcessAddExtraContentConfirmation()
    {
        $hook_name = Tools::getValue('hook_name');
        $id_layout = (int)Tools::getValue('id_layout');
        $jxmegalayout = new Jxmegalayout();
        if (!$form = $jxmegalayout->addExtraContentForm($hook_name, $id_layout)) {
            die(Tools::jsonEncode(array('status' => 'false', 'message' => $this->l('Some error occurred'))));
        }
        die(Tools::jsonEncode(array('status' => 'true', 'message' => $form)));
    }

    /**
     * Start process of extra content generation
     */
    public function ajaxProcessExportExtraContent()
    {
        $megalayout = new Jxmegalayout();
        $export = new JXMegaLayoutExtraExport($megalayout->getLocalPath(), $megalayout->getWebPath());
        die(Tools::jsonEncode(array('status' => true, 'href' => $export->export())));
    }

    /**
     * Load extra content selection form to the back-end
     */
    public function ajaxProcessImportExtraContentForm()
    {
        $jxmegalayout = new Jxmegalayout();
        die(Tools::jsonEncode(
            array('status' => true, 'content' => $jxmegalayout->display(
                $jxmegalayout->getLocalPath(),
                '/views/templates/admin/tools/extra/extra-content-import.tpl'
            ))
        ));
    }

    /**
     * Start to import extra content after a valid archive was added
     */
    public function ajaxProcessImportExtraContent()
    {
        $jxmegalayout = new Jxmegalayout();
        $fileName = basename($_FILES['file']['name']);
        $upload_file = $jxmegalayout->getLocalPath().'import/'.$fileName;
        if (!move_uploaded_file($_FILES['file']['tmp_name'], $upload_file)) {
            die(Tools::jsonEncode(array('status' => false, 'error' => $this->l('An error occurred during an archive uploading'))));
        }

        $extraContentImport = new JXMegaLayoutExtraImport($jxmegalayout->getLocalPath());
        $extraContentImport->import();
    }

    public function ajaxProcessLoadThemesContent()
    {
        $themeBuilder = new JXMegalayoutThemeBuilder();
        $jxmegalayout = new Jxmegalayout();
        $theme = Tools::getValue('theme');
        $childTheme = Tools::getValue('child_theme');
        $action = Tools::getValue('theme_action');
        if (!Validate::isThemeName($theme)) {
            die(Tools::jsonEncode(array('status' => false, 'error' => $this->l('It seems that you attempt to load an invalid theme!'))));
        }
        $parentTheme = $themeBuilder->setParentTheme($theme);
        $this->context->smarty->assign('current_theme', $theme);
        $this->context->smarty->assign('has_update', $parentTheme->checkParentThemeUpdates());
        $this->context->smarty->assign('has_library_update', $parentTheme->checkParentThemeLibraryUpdates());
        switch ($action) {
            case 'load_parent':
                $this->context->smarty->assign('children_themes', $parentTheme->getChildren());
                $content = $jxmegalayout->display($jxmegalayout->getLocalPath(), '/views/templates/admin/tools/themebuilder/theme-builder-manage-themes.tpl');
            break;
            case 'add_new_theme':
                $this->context->smarty->assign('theme_library', $parentTheme->loadParentThemeLibrary());
                $this->context->smarty->assign('theme_library_previews', $parentTheme->loadParentThemeLibraryPreviews());
                $content = $jxmegalayout->display($jxmegalayout->getLocalPath(), '/views/templates/admin/tools/themebuilder/theme-builder-new-theme.tpl');
                break;
            case 'load_child':
                $this->context->smarty->assign('theme_library', $parentTheme->loadParentThemeLibrary());
                $this->context->smarty->assign('theme_library_previews', $parentTheme->loadParentThemeLibraryPreviews());
                $this->context->smarty->assign('current_child_theme', $themeBuilder->loadChildThemeInfo($childTheme));
                $content = $jxmegalayout->display($jxmegalayout->getLocalPath(), '/views/templates/admin/tools/themebuilder/theme-builder-new-theme.tpl');
                break;
        }

        die(Tools::jsonEncode(array('status' => true, 'content' => $content)));
    }

    public function ajaxProcessSaveBuilderTheme()
    {
        $themeBuilder = new JXMegalayoutThemeBuilder();
        $jxmegalayout = new Jxmegalayout();
        $parentTheme = Tools::getValue('parent_theme');
        $themeName = Tools::getValue('theme_name');
        $themePublicName = Tools::getValue('theme_public_name');
        $layouts = Tools::getValue('layouts');

        if (!$themeName && $errors = $jxmegalayout->validateThemeNames($themeName, $themePublicName)) {
            die(Tools::jsonEncode(array('status' => false, 'content' => $errors)));
        }

        if (!$layouts) {
            die(Tools::jsonEncode(array('status' => false, 'content' => $this->l('No layouts were selected. Please select at least one layout to create a new child theme.'))));
        }
        $this->context->smarty->assign('current_theme', $parentTheme);
        if (!$errors = $themeBuilder->buildChildTheme($parentTheme, $themeName, $themePublicName, $layouts)) {
            $this->context->smarty->assign('message', $jxmegalayout->displayConfirmation($this->l('All setting were successfully saved.')));
            die(Tools::jsonEncode(array('status' => true, 'content' => $jxmegalayout->display($jxmegalayout->getLocalPath(), '/views/templates/admin/tools/themebuilder/message/theme-builder-message.tpl'))));
        }
        switch ($errors) {
            case 1:
                $message = $this->l('An error occurred during files copying.');
                break;
            case 2:
                $message = $this->l('An error occurred during settings file creation.');
                break;
            case 3:
                $message = $this->l('An error occurred during preview image copying.');
                break;
            case 4:
                $message = $this->l('An error occurred during child theme settings file creation.');
                break;
        }

        die(Tools::jsonEncode(array('status' => false, 'content' => $jxmegalayout->displayError($message))));
    }

    public function ajaxProcessRemoveBuilderTheme()
    {
        $themeBuilder = new JXMegalayoutThemeBuilder();
        $jxmegalayout = new Jxmegalayout();
        $themeName = Tools::getValue('theme_name');
        $parentTheme = Tools::getValue('parent_theme');
        if (!$themeName) {
            die(Tools::jsonEncode(array('status' => false, 'content' => $jxmegalayout->displayError('The theme was not found.'))));
        }

        if ($error = $themeBuilder->remove($themeName)) {
            switch ($error) {
                case 1:
                    $message = $this->l('You cannot delete a theme while it is used by any of your stores. Please, switch all stores themes to another one and try again.');
                    break;
                case 2:
                    $message = $this->l('It seems that cannot delete a theme. Probably you don\'t have enough do to process this');
                    break;
            }
            die(Tools::jsonEncode(array('status' => false, 'content' => $jxmegalayout->displayError($message))));
        }
        $this->context->smarty->assign('current_theme', $parentTheme);
        $this->context->smarty->assign('message', $jxmegalayout->displayConfirmation($this->l('Theme successfully deleted.')));
        die(Tools::jsonEncode(array('status' => true, 'content' => $jxmegalayout->display($jxmegalayout->getLocalPath(), '/views/templates/admin/tools/themebuilder/message/theme-builder-message.tpl'))));
    }

    public function ajaxProcessUpdateParentTheme()
    {
        $jxmegalayoutBuilder = new JXMegalayoutThemeBuilder();
        $jxmegalayout = new Jxmegalayout();
        $themeName = Tools::getValue('parent_theme');
        $parentTheme = $jxmegalayoutBuilder->setParentTheme($themeName);
        if ($parentTheme->updateParentTheme()) {
            die(Tools::jsonEncode(array('status' => true, 'content' => $jxmegalayout->displayConfirmation('Parent theme successfully updated'))));
        }

        die(Tools::jsonEncode(array('status' => false, 'content' => $jxmegalayout->displayError('An error occurred during theme updating'))));
    }

    public function ajaxProcessUpdateParentThemeLibrary()
    {
        $jxmegalayoutBuilder = new JXMegalayoutThemeBuilder();
        $jxmegalayout = new Jxmegalayout();
        $themeName = Tools::getValue('parent_theme');
        $parentTheme = $jxmegalayoutBuilder->setParentTheme($themeName);
        if ($parentTheme->updateParentThemeLibrary()) {
            die(Tools::jsonEncode(array('status' => true, 'content' => $jxmegalayout->displayConfirmation('Parent theme library successfully updated'))));
        }

        die(Tools::jsonEncode(array('status' => false, 'content' => $jxmegalayout->displayError('An error occurred during theme\'s library updating'))));
    }
}
