{**
* 2017-2018 Zemez
*
* JX Mega Layout
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
*  @author    Zemez (Alexander Grosul & Alexander Pervakov)
*  @copyright 2017-2018 Zemez
*  @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
*}

{extends file="helpers/form/form.tpl"}
{block name="field"}
  {if $input.type == 'multilang_file'}
    <div class="row">
      {foreach from=$languages item=language}
      {if $languages|count > 1}
        <div class="translatable-field lang-{$language.id_lang|escape:'htmlall':'UTF-8'}" {if $language.id_lang != $defaultFormLanguage}style="display:none"{/if}>
          {/if}
          <div class="col-lg-6">
            {if isset($fields_value['img'][$language.id_lang]) && $fields_value['img'][$language.id_lang]}
              <img src="{$image_baseurl|escape:'htmlall':'UTF-8'}{$fields_value['img'][$language.id_lang]}" class="img-thumbnail" />
            {/if}
            <div class="dummyfile input-group">
              <input id="{$input.name|escape:'htmlall':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}" type="file" name="{$input.name|escape:'htmlall':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}" class="hide-file-upload" />
              <span class="input-group-addon"><i class="icon-file"></i></span>
              <input id="{$input.name|escape:'htmlall':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}-name" type="text" class="disabled" name="filename" readonly />
                <span class="input-group-btn">
                  <button id="{$input.name|escape:'htmlall':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}-selectbutton" type="button" name="submitAddAttachments" class="btn btn-default">
                    <i class="icon-folder-open"></i> {l s='Choose a file' mod='jxmegalayout'}
                  </button>
                </span>
            </div>
          </div>
          {if $languages|count > 1}
            <div class="col-lg-2">
              <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
                {$language.iso_code|escape:'htmlall':'UTF-8'}
                <span class="caret"></span>
              </button>
              <ul class="dropdown-menu">
                {foreach from=$languages item=lang}
                  <li><a href="javascript:hideOtherLanguage({$lang.id_lang|escape:'htmlall':'UTF-8'});" tabindex="-1">{$lang.name|escape:'htmlall':'UTF-8'}</a></li>
                {/foreach}
              </ul>
            </div>
          {/if}
          {if $languages|count > 1}
        </div>
      {/if}
        <script>
          $(document).ready(function(){
            $('#{$input.name|escape:"htmlall":"UTF-8"}_{$language.id_lang|escape:"htmlall":"UTF-8"}-selectbutton').click(function(e){
              $('#{$input.name|escape:"htmlall":"UTF-8"}_{$language.id_lang|escape:"htmlall":"UTF-8"}').trigger('click');
            });
            $('#{$input.name|escape:"htmlall":"UTF-8"}_{$language.id_lang|escape:"htmlall":"UTF-8"}').change(function(e){
              var val = $(this).val();
              var file = val.split(/[\\/]/);
              $('#{$input.name|escape:"htmlall":"UTF-8"}_{$language.id_lang|escape:"htmlall":"UTF-8"}-name').val(file[file.length-1]);
            });
          });
        </script>
      {/foreach}
    </div>
  {/if}
  {if $input.type == 'multilang_video'}
    <div class="row">
      {foreach from=$languages item=language}
      {if $languages|count > 1}
        <div class="translatable-field lang-{$language.id_lang|escape:'htmlall':'UTF-8'}" {if $language.id_lang != $defaultFormLanguage}style="display:none"{/if}>
          {/if}
          <div class="col-lg-3">
            {if isset($fields_value['url'][$language.id_lang])}
              <div class="videowrapper">
                <iframe type="text/html"
                        src="{$fields_value['url'][$language.id_lang]}?enablejsapi=1&version=3&html5=1&wmode=transparent"
                        frameborder="0"
                        wmode="Opaque"></iframe>
              </div>
            {else}
              {l s='No video yet.' mod='jxmegalayout'}
            {/if}
          </div>
          {if $languages|count > 1}
            <div class="col-lg-2">
              <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
                {$language.iso_code|escape:'htmlall':'UTF-8'}
                <span class="caret"></span>
              </button>
              <ul class="dropdown-menu">
                {foreach from=$languages item=lang}
                  <li><a href="javascript:hideOtherLanguage({$lang.id_lang|escape:'htmlall':'UTF-8'});" tabindex="-1">{$lang.name|escape:'htmlall':'UTF-8'}</a></li>
                {/foreach}
              </ul>
            </div>
          {/if}
          {if $languages|count > 1}
        </div>
      {/if}
        <script>
          $(document).ready(function(){
            $('#{$input.name|escape:"htmlall":"UTF-8"}_{$language.id_lang|escape:"htmlall":"UTF-8"}-selectbutton').click(function(e){
              $('#{$input.name|escape:"htmlall":"UTF-8"}_{$language.id_lang|escape:"htmlall":"UTF-8"}').trigger('click');
            });
            $('#{$input.name|escape:"htmlall":"UTF-8"}_{$language.id_lang|escape:"htmlall":"UTF-8"}').change(function(e){
              var val = $(this).val();
              var file = val.split(/[\\/]/);
              $('#{$input.name|escape:"htmlall":"UTF-8"}_{$language.id_lang|escape:"htmlall":"UTF-8"}-name').val(file[file.length-1]);
            });
          });
        </script>
      {/foreach}
    </div>
  {/if}
  {if $input.type == 'slides_wizard'}
    <div id="extra-content-slider-new-slide" class="col-lg-4">
      <div class="row">
        <div class="col-lg-4">
          <select id="extra-content-types-list" name="extra_content_types_list">
            {foreach from=$content_types item='type'}
               <option value="{$type}">{$type}</option>
            {/foreach}
          </select>
        </div>
        <div class="col-lg-6">
          {foreach from=$content_lists key='type' item='content' name='loop'}
            {if $type != 'product' && $type != 'post'}
              <select id="extra-content-types-list-{$type}" class="extra-content-types-list{if $smarty.foreach.loop.iteration > 1} hidden{/if}" name="{$type}">
                {if $content}
                  <option value="">--</option>
                  {foreach from=$content key=$type item='item'}
                    <option value="{$item.id}">({l s='ID:' mod='jxmegalayout'}{$item.id}) - {$item.name}</option>
                  {/foreach}
                {else}
                  <option disabled="disabled">{l s='No items to select' mod='jxmegalayout'}</option>
                {/if}
              </select>
            {else}
              <input type="text" name="{$type}" class="extra-content-types-list{if $smarty.foreach.loop.iteration > 1} hidden{/if}" value="" placeholder="{l s='Numbers only' mod='jxmegalayout'}" />
            {/if}
          {/foreach}
        </div>
        <div class="col-lg-2">
        <a href="#" class="btn btn-success add-extra-content-slider disabled">
          <i class="icon icon-check"></i>
        </a>
      </div>
      </div>
    </div>
    <div class="col-lg-4 col-lg-offset-3">
      <div class="row" id="extra-content-slider-slides">
        {if $slides && $slides|count}
          {foreach from=$slides item='slide'}
            <div class="item col-lg-12">
              <div class="row">
                <span class="type col-lg-2">{$slide.entity.type}</span>
                <span class="name col-lg-8">({l s='ID:' mod='jxmegalayout'}{$slide.entity.id_content}) - {$slide.info.name}</span>
                <span class="button col-lg-2">
                  <a href="#" class="btn btn-danger remove-extra-content-slider">
                    <i class="icon icon-remove"></i>
                  </a>
                </span>
                <input type="hidden" name="slides[]" value="{$slide.entity.type}-{$slide.entity.id_content}" />
              </div>
            </div>
          {/foreach}
        {/if}
      </div>
    </div>
  {/if}
  {$smarty.block.parent}
{/block}