{*
* 2017-2018 Zemez
*
* JX Header Account
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
{if $f_status}
    <a href="{$link->getModuleLink('jxheaderaccount', 'facebooklink', [], true)}" title="{l s='Facebook Login Manager' mod='jxheaderaccount'}" class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
      <span class="link-item">
        <i class="fa fa-facebook" aria-hidden="true"></i>
        {if !$facebook_id}{l s='Connect With Facebook' mod='jxheaderaccount'}{else}{l s='Facebook Login Manager' mod='jxheaderaccount'}{/if}
      </span>
    </a>
{/if}
{if $g_status}
    <a {if isset($back) && $back}href="{$link->getModuleLink('jxheaderaccount', 'googlelogin', ['back' => $back], true)}" {else}href="{$link->getModuleLink('jxheaderaccount', 'googlelogin', [], true)}"{/if} title="{l s='Google Login Manager' mod='jxheaderaccount'}" class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
      <span class="link-item">
        <i class="fa fa-google-plus" aria-hidden="true"></i>
        {if !$google_id}{l s='Connect With Google' mod='jxheaderaccount'}{else}{l s='Google Login Manager' mod='jxheaderaccount'}{/if}
      </span>
    </a>
{/if}
{if $vk_status}
    <a {if isset($back) && $back}href="{$link->getModuleLink('jxheaderaccount', 'vklogin', ['back' => $back], true)}" {else}href="{$link->getModuleLink('jxheaderaccount', 'vklogin', [], true)}"{/if} title="{l s='VK Login Manager' mod='jxheaderaccount'}" class="col-lg-4 col-md-6 col-sm-6 col-xs-12">
      <span class="link-item">
        <i class="fa fa-vk" aria-hidden="true"></i>
        {if !$vkcom_id}{l s='Connect With VK' mod='jxheaderaccount'}{else}{l s='VK Login Manager' mod='jxheaderaccount'}{/if}
      </span>
    </a>
{/if}
