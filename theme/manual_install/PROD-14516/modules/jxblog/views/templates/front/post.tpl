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

{block name='head_seo_description'}{$post.meta_description}{/block}
{block name='head_seo_keywords'}{$post.meta_keyword}{/block}

{block name='content'}
  <section id="main">
    {if $post}

      {block name='blog_post_header'}
        <h1>{$post.name}</h1>
      {/block}

      <p class="post-meta{if !$displayAuthor} author-hidden{/if}">
        {l s='Posted on %date% at %time% [0]by [1"]%link%["1]%name%[/1][/0]'
        sprintf=[
        '%date%' => {$post.date_start|date_format},
        '%time%' => {$post.date_start|date_format:"%H:%M"},
        '%link%' => {url entity='module' name='jxblog' controller='author' params = ['author' => $post.id_author]},
        '%name%' => {$post.author},
        '[0]' => '<span class="post-author">',
        '[/0]' => '</span>',
        '[1"]' => '<a href="',
        '["1]' => '">',
        '[/1]' => '</a>'
        ]
        d='Shop.Theme.Global'
        }{if $displayViews && $post.views} - <small class="post-views">{l s='Views' mod='jxblog'} ({$post.views})</small>{/if}
      </p>

      <div class="post-image mb-2">
        <img class="img-fluid" src="{JXBlogImageManager::getImage('post', $post.id_jxblog_post, 'post_default')}" alt="{$post.name}"/>
      </div>

      {if $post.description}
        <div class="post-description">
          {$post.description nofilter}
        </div>
      {/if}

      {if $tags}
        <hr>
        <div class="post-tags">
          {l s='Tagged' mod='jxblog'}
          {foreach from=$tags item='tag' name="tag"}
            <a href="{$link->getModuleLink('jxblog', 'tag', ['id_jxblog_tag' => $tag.id_jxblog_tag])}">{$tag.tag}</a>{if !$smarty.foreach.tag.last},{/if}
          {/foreach}
        </div>
      {/if}

      <hr>

      {hook h="displayJXBlogPostFooter" post=$post}

    {else}
      <p>{l s='Post doesn\'t exist or you don\'t have permissions to access it.' mod='jxblog'}</p>
    {/if}

  </section>
{/block}
