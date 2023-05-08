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

{if isset($error)}
  {$error|escape:'quotes':'UTF-8'}
{elseif isset($compatibility) && $compatibility}
  {$compatibility|escape:'quotes':'UTF-8'}
{else}
  <div class="layout-preview-box">
    {l s='Layout name:' mod='jxmegalayout'}
    <span class="layout_name {if isset($check_name) && $check_name == false}layout_name_check{/if}">{$layout_name|escape:'htmlall':'UTF-8'}</span><br>
    {if isset($check_name) && $check_name == false}
      {l s='Add new name for preset:' mod='jxmegalayout'}
      <input type="text" value="" id="new_name_layout" class="form-control" name="new_name_layout" autocomplete="off"/>
    {/if}
    {l s='Hook:' mod='jxmegalayout'}
    <span class="hook_name">{$hook_name|escape:'htmlall':'UTF-8'}</span><br>
    {l s='Assigned pages:' mod='jxmegalayout'}
    <div class="jxmegalayout-admin container">
      {if isset($pages) && $pages}{$pages|escape:'quotes':'UTF-8'}{else}-{/if}
    </div>
    {l s='Preview:' mod='jxmegalayout'}
    <div class="jxmegalayout-admin container">
      {$layout_preview|escape:'quotes':'UTF-8'}
    </div>
  </div>
  <button class="btn btn-default center-block {if isset($check_name) && $check_name == false}hide{/if}" id="importLayoutArchive">{l s='Import' mod='jxmegalayout'}</button>
  <script type="text/javascript">
    $('#new_name_layout').keypress(function() {
      setTimeout(function() {
        if ($('#new_name_layout').val().length > 0) {
          $('#importLayoutArchive').removeClass('hide');
        } else {
          $('#importLayoutArchive').addClass('hide');
        }
      }, 1);
    });
  </script>
{/if}
 