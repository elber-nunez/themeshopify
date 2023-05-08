{**
* 2017-2018 Zemez
*
* JX Product Zoomer
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

{extends file="helpers/form/form.tpl"}
{block name="field"}
    {if $input.type == 'jxproductzoomer_sample_image'}
		<div class="row">
            <div class="col-lg-9 col-lg-offset-3">
                <img src="{$image_path|escape:'html':'UTF-8'}/window-positions.png" alt="" />
                <p class="help-block">{l s='Once positioned, use zoomWindowOffsetx and zoomWindowOffsety to adjust
                Possible values: 1-16' mod='jxproductzoomer'}</p>
			</div>
		</div>
	{/if}
	{$smarty.block.parent}
{/block}