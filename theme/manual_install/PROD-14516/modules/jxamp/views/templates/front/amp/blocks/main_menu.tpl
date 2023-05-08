{**
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
*}

<amp-sidebar
        on="sidebarOpen:btn-sidebar-open.hide,btn-sidebar-close.show;sidebarClose:btn-sidebar-open.show,btn-sidebar-close.hide"
        id="sidebar"
        layout="nodisplay"
        side="left">
  <div class="sidebar-memu">
    <div class="sidebar-menu-categories">
      {include file='module:jxamp/views/templates/front/amp/blocks/_partials/category_tree.tpl' categories=$main_menu.categories depth=0}
    </div>
  </div>
</amp-sidebar>