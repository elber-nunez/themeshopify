{**
* 2017-2018 Zemez
*
* JX One Click Order
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
<script>
    $(document).ready(function(){
        var $d = $(this),
            tabs = $('#one-click-order-settings .page-head-tabs');

        tabs.appendTo('.page-head');
        $('.page-head').addClass('with-tabs');
        $('.bootstrap').addClass('with-tabs');
    });
</script>
<div id="one-click-order-settings">
    <div id="head_tabs" class="page-head-tabs">
        <ul class="nav">
            <li><a class="current" data-toggle="tab" href="#order_settings_template">{l s='Preorder template' mod='jxoneclickorder'}</a></li>
            <li><a data-toggle="tab" href="#order_success_message">{l s='Preorder success message' mod='jxoneclickorder'}</a></li>
            <li><a data-toggle="tab" href="#order_settings">{l s='Settings' mod='jxoneclickorder'}</a></li>
        </ul>
    </div>

    <div class="tab-content">
        <div id="order_settings_template" class="tab-pane fade in active">
            <div class="row">
                <div class="col-sm-12">
                    <div class="fields">
                        {if count($fields) > 0 }
                            {foreach from=$fields item=field}
                                {include './_partials/field.tpl'}
                            {/foreach}
                        {else}
                            <div class="no-fields">
                                {l s='No fields added' mod='jxoneclickorder'}
                            </div>
                        {/if}
                    </div>
                    <div class="btn-wrapper">
                        <a href="#" class="add-field btn btn-default">{l s='Add new field' mod='jxoneclickorder'}</a>
                    </div>
                </div>
            </div>
        </div>
        <div id="order_success_message" class="tab-pane fade">
            <div class="row">
                <div class="col-sm-12">
                    {include './_partials/message.tpl'}
                </div>
            </div>
        </div>
        <div id="order_settings" class="tab-pane fade">
            <div class="row">
                <div class="col-sm-12">
                    {include './_partials/options.tpl'}
                </div>
            </div>
        </div>
    </div>
</div>