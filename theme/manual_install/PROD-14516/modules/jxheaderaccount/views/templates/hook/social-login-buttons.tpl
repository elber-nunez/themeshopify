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
* @author     Zemez
* @copyright  2017-2018 Zemez
* @license    http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
*}

{if $f_status}
  <a title="Login with your Facebook Account" class="button_large btn btn-login-facebook" href="{$link->getModuleLink('jxheaderaccount', 'facebooklogin', [], true)}">
    {l s='Facebook Login' mod='jxheaderaccount'}
  </a>
{/if}
{if $g_status}
  <a title="Login with your Google Account" class="button_large btn btn-login-google" {if isset($back) && $back}href="{$link->getModuleLink('jxheaderaccount', 'googlelogin', ['back' => $back], true)}" {else}href="{$link->getModuleLink('jxheaderaccount', 'googlelogin', [], true)}"{/if}>
    {l s='Google Login' mod='jxheaderaccount'}
  </a>
{/if}
{if $vk_status}
  <a title="Login with your VK Account" class="button_large btn btn-login-vk" {if isset($back) && $back}href="{$link->getModuleLink('jxheaderaccount', 'vklogin', ['back' => $back], true)}" {else}href="{$link->getModuleLink('jxheaderaccount', 'vklogin', [], true)}"{/if}>
    {l s='VK Login' mod='jxheaderaccount'}
  </a>
{/if}