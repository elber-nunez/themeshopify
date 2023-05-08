{**
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
*}

{if $message}
  {if $message.type == 'layoutRemoveConf'}
    <h2 class="popup-heading">{l s='Remove layout?' mod='jxmegalayout'}</h2>
    <div class="form-group popup-content">
      <p class="alert alert-warning">{l s='Are you sure you want delete this layout?' mod='jxmegalayout'}</p>
    </div>
    <div class="popup-btns">
      <a data-layout-id="{$message.id_layout|escape:'htmlall':'UTF-8'}" class="remove-layout-confirm btn btn-success" href="#">{l s='Remove layout' mod='jxmegalayout'}</a>
    </div>
  {elseif $message.type == 'layoutRenameConf'}
    <h2 class="popup-heading">{l s='Layout' mod='jxmegalayout'}</h2>
    <div class="form-group popup-content">
      <label>{l s='Enter layout name' mod='jxmegalayout'}</label>
      <input type="text" class="form-control" value="{$message.text|escape:'htmlall':'UTF-8'}" name="layout_name"/>
    </div>
    <div class="popup-btns">
      <a data-layout-id="{$message.id_layout|escape:'htmlall':'UTF-8'}" class="edit-layout-confirm btn btn-success" href="#">{l s='Rename layout' mod='jxmegalayout'}</a>
    </div>
  {elseif $message.type == 'addLayout'}
    <h2 class="popup-heading">{l s='Layout' mod='jxmegalayout'}</h2>
    <div class="form-group popup-content">
      <label for="wrapper-classes">{l s='Enter layout name' mod='jxmegalayout'}</label>
      <input name="layout_name" value="" class="form-control">
    </div>
    <div class="popup-btns">
      <a href="#" class="save-layout btn btn-success" data-hook-name="{$message.id_layout|escape:'htmlall':'UTF-8'}">{l s='Save' mod='jxmegalayout'}</a>
    </div>
  {/if}
{/if}