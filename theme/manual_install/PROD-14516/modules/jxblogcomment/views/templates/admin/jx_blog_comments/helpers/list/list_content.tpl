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

{extends file="helpers/list/list_content.tpl"}
{block name="td_content"}
  {if $params.type == 'image_field'}
    {if $tr.image_name}
      <img style="max-height:100px; max-width: 150px;" src="{$params.img_path|escape:'html':'UTF-8'}{$tr.image_name|escape:'html':'UTF-8'}" alt=""/>
    {/if}
  {/if}
  {$smarty.block.parent}
{/block}
{block name="default_field"}
  {if $params.type == 'image_field' && $tr.image_name}{else}
    --
  {/if}
{/block}