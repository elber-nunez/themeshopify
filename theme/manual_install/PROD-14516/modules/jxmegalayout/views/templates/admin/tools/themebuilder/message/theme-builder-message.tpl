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

{if isset($message)}
  {$message}
{/if}
<div class="row">
  <div class="col-lg-12">
    <a href="#" id="manage-theme" data-action="load_parent" data-theme-name="{$current_theme}" class="btn btn-default">{l s='Go back' mod='jxmegalayout'}</a>
  </div>
</div>