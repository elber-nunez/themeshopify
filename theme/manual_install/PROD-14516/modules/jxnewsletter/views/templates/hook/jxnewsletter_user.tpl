{*
* 2017-2018 Zemez
*
* JX Newsletter
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
* @author   Zemez (Alexander Grosul)
* @copyright  2017-2018 Zemez
* @license  http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
*}

<div id="newsletter_popup" class="jxnewsletter jxnewsletter-autorized modal">
  <div class="jxnewsletter-inner">
    <div class="jxnewsletter-close icon"></div>
    <div class="jxnewsletter-header">
      <h4>{$title|escape:'htmlall':'UTF-8'}</h4>
    </div>
    <div class="jxnewsletter-content">
      <div class="status-message"></div>
      <div class="description">{$content|escape:'quotes':'UTF-8'}</div>
      <div class="form-group">
        <label>{l s='Your E-Mail' mod='jxnewsletter'}</label>
        <input class="form-control" placeholder="{l s='Enter your e-mail'  mod='jxnewsletter'}" type="email" name="email" />
      </div>
      {if isset($id_module)}
        {hook h='displayGDPRConsent' mod='psgdpr' id_module=$id_module}
      {/if}
    </div>
    <div class="jxnewsletter-footer">
      <div class="custom-checkbox checkbox">
        <input type="checkbox" name="disable_popup" />
        <span>
          <i class="material-icons rtl-no-flip checkbox-checked psgdpr_consent_icon"></i>
        </span>
        <span> {l s='Do not show again' mod='jxnewsletter'}</span>
      </div>
      <button class="btn btn-default jxnewsletter-close">{l s='Close' mod='jxnewsletter'}</button>
      <button class="btn btn-default jxnewsletter-submit">{l s='Subscribe' mod='jxnewsletter'}</button>
    </div>
  </div>
</div>