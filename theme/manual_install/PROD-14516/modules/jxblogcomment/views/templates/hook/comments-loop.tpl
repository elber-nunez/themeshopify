{*
* 2017 Zemez
*
* JX Blog Comment
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
*  @copyright 2017 Zemez
*  @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
*}

<section id="blog-comments" data-post-id="{$post.id_jxblog_post}">
  <h3 class="h3">{l s='Post comments' mod='jxblogcomment'}</h3>
  <div class="card">
    <div class="card-block">
      {if $readingDisabled}
        {if !$isLogged}
          <p class="alert alert-warning" role="alert">{l s='Only authorized members can read comments' mod='jxblogcomment'}</p>
        {/if}
      {else}
        {if $commentingDisabled}
          {if $isLogged}
            <p class="alert alert-warning" role="alert">{l s='Commenting is disabled in current moment' mod='jxblogcomment'}</p>
          {else}
            <p class="alert alert-warning" role="alert">{l s='Only authorized members can post comments' mod='jxblogcomment'}</p>
          {/if}
        {/if}
        <div id="blog-comments-container"></div>
      {/if}
    </div>
  </div>
</section>
