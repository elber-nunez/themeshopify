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

{if $categories}
  <amp-accordion disable-session-states>
    {foreach from=$categories item='category'}
      {include file='module:jxamp/views/templates/front/amp/blocks/_partials/category_tree_branch.tpl' category=$category depth=$depth + 1}
    {/foreach}
  </amp-accordion>
{/if}
