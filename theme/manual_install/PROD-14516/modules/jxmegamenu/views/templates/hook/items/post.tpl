{*
* 2017-2018 Zemez
*
* JX Mega Menu
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

{if isset($jxpost) && $jxpost}
  <li class="post-thumbnail">
    <div class="post-container">
      <h3 class="post-name">{$jxpost->name}</h3>
      <div class="post-image">
        <a href="{$url}"><img class="img-fluid" src="{JXBlogImageManager::getImage('post_thumb', $jxpost->id, 'post_listing')}" alt="{$jxpost->name}" /></a>
      </div>
      <div class="post-description">
        {$jxpost->short_description}
      </div>
    </div>
  </li>
{/if}
