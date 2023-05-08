{*
* 2017-2018 Zemez
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
*  @author    Zemez (Alexander Grosul)
*  @copyright 2017-2018 Zemez
*  @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
*}


{extends file=$layout}

{block name='content'}
  <section id="main">

    {block name='blog_categories_header'}
      <h1>{l s='Blog categories' mod='jxblog'}</h1>
    {/block}

    {if $categories}
      <section class="blog-categories row">
        {foreach from=$categories item='category'}
          {block name='blog_category_miniature'}
            <article class="bc-miniature col-sm-6 col-lg-4">
              <div class="bc-miniature-container card">
                <div class="bc-thumbnail">
                  <a href="{url entity='module' name='jxblog' controller='category' params = ['id_jxblog_category' => $category.id_jxblog_category, 'rewrite' => $category.link_rewrite]}">
                    <img class="img-fluid" src="{JXBlogImageManager::getImage('category_thumb', $category.id_jxblog_category, 'category_listing')}" alt="{$category.name}">
                  </a>
                </div>
                <div class="card-body p-1">
                  <h1 class="h4 bc-name">
                    <a href="{url entity='module' name='jxblog' controller='category' params = ['id_jxblog_category' => $category.id_jxblog_category, 'rewrite' => $category.link_rewrite]}">
                      {$category.name}
                    </a>
                  </h1>
                  {if $category.short_description}
                    <div class="bc-short-description">
                      {$category.short_description nofilter}
                    </div>
                  {/if}
                </div>
              </div>
            </article>
          {/block}
        {/foreach}
      </section>
      {if $pagination}
        {include file="module:jxblog/views/templates/front/_partials/pagination.tpl"}
      {/if}

    {else}
      {l s='There are no categories in the blog yet' mod='jxblog'}
    {/if}

  </section>
{/block}
