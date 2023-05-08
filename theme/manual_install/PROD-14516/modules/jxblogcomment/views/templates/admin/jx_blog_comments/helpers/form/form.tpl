{*
 * 2002-2017 Jetimpex
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
 * @author    Jetimpex (Alexander Grosul)
 * @copyright 2002-2017 Jetimpex
 * @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
*}

{extends file="helpers/form/form.tpl"}
{block name="field"}
  {if $input.type == 'attachment'}
    <div class="col-lg-6">
      {if $input.file}
        <img class="img-responsive" src="{$input.file|escape:'html':'UTF-8'}" alt="" />
      {else}
        --
      {/if}
    </div>
  {/if}
  {$smarty.block.parent}
{/block}