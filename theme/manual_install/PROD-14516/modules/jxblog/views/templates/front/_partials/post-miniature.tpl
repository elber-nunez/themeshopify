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

<section class="blog-posts row">
  {foreach from=$posts item='post'}
    {block name='blog_post_miniature'}
      <article class="bp-miniature col-sm-6 col-lg-4 mb-3">
        <div class="bp-miniature-container card">
          <div class="bp-thumbnail">
            <a href="{url entity='module' name='jxblog' controller='post' params = ['id_jxblog_post' => $post.id_jxblog_post, 'rewrite' => $post.link_rewrite]}">
              <img class="img-fluid" src="{JXBlogImageManager::getImage('post_thumb', $post.id_jxblog_post, 'post_listing')}" alt="{$post.name}">
            </a>
          </div>
          <div class="card-body p-1">
            <h1 class="h4 bp-name">
              <a href="{url entity='module' name='jxblog' controller='post' params = ['id_jxblog_post' => $post.id_jxblog_post, 'rewrite' => $post.link_rewrite]}">
                {$post.name}
              </a>
            </h1>
            {if $post.short_description}
              <div class="bp-short-description">
                {$post.short_description nofilter}
              </div>
            {/if}
            <a class="btn btn-primary" href="{url entity='module' name='jxblog' controller='post' params = ['id_jxblog_post' => $post.id_jxblog_post, 'rewrite' => $post.link_rewrite]}">
              {l s='Read more' d='Shop.Theme.Actions'}
            </a>
          </div>
        </div>
      </article>
    {/block}
  {/foreach}
</section>
