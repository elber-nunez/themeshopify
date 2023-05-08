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

<div class="modal fade bs-example-modal-lg" id="myModalSettings" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">{l s='Menu\'s general settings' mod='jxmegamenu'}</h4>
      </div>
      <div class="modal-body">
        <div class="form-wrapper">
          <form class="form-horizontal">
            <div class="form-group">
              <label class="control-label col-lg-2">{l s='Google Map API' mod='jxmegamenu'}</label>
              <div class="col-lg-10">
                <div class="form-group no-indent">
                  <div class="col-lg-6">
                    <input data-name="googleapi" class="form-control" name="googleapi" value="{if isset($settings->googleapi) && $settings->googleapi}{$settings->googleapi|escape:'html':'UTF-8'}{/if}" />
                    <p class="help-block no-indent">{l s='Enter your Google Map API code here' mod='jxmegamenu'}</p>
                  </div>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
      <div class="modal-footer clearfix">
        <button id="save-settings" class="btn btn-sm btn-success" >{l s='Save settings' mod='jxmegamenu'}</button>
      </div>
    </div>
    <div class="modal-loader"><span class="loader-gif"></span></div>
  </div>
</div>