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

{block name='head_seo_description'}{$page.meta.description}{/block}
{block name='head_seo_keywords'}{$page.meta.keywords}{/block}

{block name='content'}
  <section id="main">
    <div id="blog-category-{$category.id_jxblog_category}" class="blog-category">
      <div class="blog-category-image mb-2">
        <img class="img-fluid" src="{JXBlogImageManager::getImage('category', $category.id_jxblog_category, 'category_info')}" alt="{$category.name}">
      </div>
      <div class="blog-category-info">
        <h1>{$category.name} <span class="badge badge-primary">{$category.badge}</span></h1>
        {if $category.short_description && $category.description}
          <button type="button" class="btn btn-secondary btn-sm" data-toggle="button" aria-pressed="false" autocomplete="off">
            <span>{l s='Read more' d='Shop.Theme.Actions'}</span>
            <span>{l s='Hide' d='Shop.Theme.Actions'}</span>
          </button>
          <div class="blog-category-description-short text-muted">
            {$category.short_description nofilter}
          </div>
          <div class="blog-category-description text-muted">
            {$category.description nofilter}
          </div>
        {elseif $category.short_description}
          <div class="blog-category-description-short text-muted">
            {$category.short_description nofilter}
          </div>
        {elseif $category.description}
          <div class="blog-category-description text-muted">
            {$category.description nofilter}
          </div>
        {/if}
      </div>
    </div>
    {if $posts}
      <hr class="my-3">
      {include file="module:jxblog/views/templates/front/_partials/post-miniature.tpl"}
      {if $pagination}
        {include file="module:jxblog/views/templates/front/_partials/pagination.tpl"}
      {/if}
    {else}
      <p>{l s='There are no posts in the category' mod='jxblog'}</p>
    {/if}
  </section>
{/block}
