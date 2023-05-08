{*
* 2017-2018 Zemez
*
* JX Search
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
* @author     Zemez (Alexander Grosul)
* @copyright  2017-2018 Zemez
* @license    http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
*}

<div class="col-sm-6 col-lg-7 clearfix">
  <ul class="{if isset($search_blog_categories) && $search_blog_categories}nav nav-tabs{else}d-none{/if}" id="jxsearch-tab" role="tablist">
    <li class="nav-item">
      <a class="nav-link{if !isset($blog_search_query) || !$blog_search_query} active{/if}" id="catalog-tab" data-toggle="tab" href="#jxsearchbox" role="tab" aria-controls="catalog" aria-selected="true">{l s='Catalog' mod='jxsearch'}</a>
    </li>
    {if isset($search_blog_categories) && $search_blog_categories}
      <li class="nav-item">
        <a class="nav-link{if isset($blog_search_query) && $blog_search_query} active{/if}" id="blog-tab" data-toggle="tab" href="#jxsearchbox-blog" role="tab" aria-controls="blog" aria-selected="false">{l s='Blog' mod='jxsearch'}</a>
      </li>
    {/if}
  </ul>
  <div id="jxsearchblock" class="tab-content">
    <div id="jxsearchbox" class="tab-pane show {if !isset($blog_search_query) || !$blog_search_query} active{/if}" role="tabpanel" aria-labelledby="catalog-tab">
      <form method="get" action="{Jxsearch::getJXSearchLink('jxsearch')|escape:'htmlall':'UTF-8'}">
        {if !Configuration::get('PS_REWRITING_SETTINGS')}
          <input type="hidden" name="fc" value="module"/>
          <input type="hidden" name="controller" value="jxsearch"/>
          <input type="hidden" name="module" value="jxsearch"/>
        {/if}
        <div class="input-group">
          <span class="input-group-addon">
            <select name="search_categories" class="form-control">
              {foreach from=$search_categories item=category}
                <option {if isset($active_category) && $active_category == $category.id}selected="selected"{/if} value="{$category.id|escape:'htmlall':'UTF-8'}">{if $category.id == 2}{l s='All Categories' mod='jxsearch'}{else}{$category.name|escape:'htmlall':'UTF-8'}{/if}</option>
              {/foreach}
            </select>
          </span>
          <input class="jx_search_query form-control" type="text" id="jx_search_query" name="search_query" placeholder="{l s='Search' mod='jxsearch'}" value="{if isset($search_query)}{$search_query|escape:'htmlall':'UTF-8'|stripslashes}{/if}"/>
          <span class="input-group-addon">
            <button type="submit" name="jx_submit_search" class="btn btn-default button-search">
              <span>{l s='Search' mod='jxsearch'}</span>
            </button>
          </span>
        </div>
      </form>
    </div>
    {if isset($search_blog_categories) && $search_blog_categories}
      <div id="jxsearchbox-blog" class="tab-pane {if isset($blog_search_query) && $blog_search_query} active{/if}" role="tabpanel" aria-labelledby="blog-tab">
        <form method="get" action="{Jxsearch::getJXBlogSearchLink()|escape:'htmlall':'UTF-8'}">
          <div class="input-group">
            <span class="input-group-addon">
              <select  name="search_blog_categories" class="form-control">
                <option value="0">{l s='All Categories' mod='jxsearch'}</option>
                {foreach from=$search_blog_categories item='blog_category'}
                  <option {if isset($active_blog_category) && $active_blog_category == $blog_category.id_jxblog_category}selected="selected"{/if} value="{$blog_category.id_jxblog_category}">{$blog_category.name}</option>
                {/foreach}
              </select>
            </span>
            <input class="jx_blog_search_query form-control" type="text" id="jx_blog_search_query" name="blog_search_query" placeholder="{l s='Search through the blog' mod='jxsearch'}" value="{if isset($blog_search_query)}{$blog_search_query}{/if}"/>
            <span class="input-group-addon">
              <button type="submit" name="jx_blog_submit_search" class="btn btn-default button-search">
                <span>{l s='Search' mod='jxsearch'}</span>
              </button>
            </span>
          </div>
        </form>
      </div>
    {/if}
  </div>
</div>