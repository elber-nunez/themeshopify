{*
* 2017-2018 Zemez
*
* JX Blog Post Posts
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

{if $posts}
  <section class="related-posts">
    <h2 class="h3">{l s='Related posts' mod='jxblogpostposts'}</h2>
    {include file="module:jxblog/views/templates/front/_partials/post-miniature.tpl"}
  </section>
{/if}

