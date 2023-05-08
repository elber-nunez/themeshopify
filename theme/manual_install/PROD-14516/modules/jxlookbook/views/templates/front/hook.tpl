{**
* 2017-2018 Zemez
*
* JX Look Book
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
*  @author    Zemez
*  @copyright 2017-2018 Zemez
*  @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
*}

{extends file=$layout}
{block name='breadcrumb'}
    <nav class="breadcrumb hidden-sm-down">
        <ol itemscope itemtype="http://schema.org/BreadcrumbList">
            <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                <a href="{$link->getModuleLink('jxlookbook', 'jxlookbook')|escape:'html':'UTF-8'}">
                    <span itemprop="name">{l s='All LookBooks' mod='jxlookbook'}</span>
                </a>
            </li>
            <li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
          <span>
            <span itemprop="name">{$name}</span>
          </span>
            </li>
        </ol>
    </nav>
{/block}
{block name="content"}
    {$content nofilter}
{/block}