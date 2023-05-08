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

{if isset($post) && $post}
  <div class="jxsearch-inner-row" data-href="{$post.url}">
    <img class="img-fluid" src="{JXBlogImageManager::getImage('post_thumb', $post.info.id_jxblog_post, 'post_search')}" alt="{$post.info.name}"/>
    <span class="reference date-add">{$post.info.date_upd|date_format}</span>
    <span class="quantity views"><i class="fa fa-eye"></i> {$post.info.views}</span>
    <span class="name">{$post.info.name}</span>
    <div class="description-short">{$post.info.short_description nofilter}</div>
  </div>
{/if}