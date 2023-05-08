{*
* 2017-2018 Zemez
*
* JX Blog
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
*  @author    Zemez (Alexander Grosul)
*  @copyright 2017-2018 Zemez
*  @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
*}

{extends file="helpers/form/form.tpl"}
{block name="field"}
  {if $input.type == 'multilingual_image'}
    <div class="row">
      {foreach from=$languages item=language}
        {if $languages|count > 1}
          <div class="translatable-field lang-{$language.id_lang|escape:'htmlall':'UTF-8'}" {if $language.id_lang != $defaultFormLanguage}style="display:none"{/if}>
        {/if}
        <div class="col-lg-6">
          {if isset($input.images[$language.id_lang]) && $input.images[$language.id_lang]}
            <img
                    src="{$input.images[$language.id_lang]|escape:'html':'UTF-8'}"
                    class="img-thumbnail" />
            <input class="hidden-image-name" type="hidden" name="old_image_{$input.images[$language.id_lang]|escape:'htmlall':'UTF-8'}" value="{$input.images[$language.id_lang]|escape:'html':'UTF-8'}" />
          {/if}
          <div class="dummyfile input-group">
            <input id="{$input.name|escape:'htmlall':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}" type="file" name="{$input.name|escape:'htmlall':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}" class="hide-file-upload"/>
            <span class="input-group-addon"><i class="icon-file"></i></span>
            <input id="{$input.name|escape:'htmlall':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}-name" type="text" class="disabled" name="{$input.name|escape:'htmlall':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}" readonly/>
							<span class="input-group-btn">
								<button id="{$input.name|escape:'htmlall':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}-selectbutton" type="button" name="submitAddAttachments" class="btn btn-default">
                  <i class="icon-folder-open"></i> {l s='Choose a file' mod='jxblog'}
                </button>
							</span>
          </div>
          <p class="help-block">{if isset($input.description) && $input.description}{$input.description|escape:'htmlall':'UTF-8'}{/if}</p>
        </div>
        {if $languages|count > 1}
          <div class="col-lg-2">
            <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
              {$language.iso_code|escape:'htmlall':'UTF-8'}
              <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
              {foreach from=$languages item=lang}
                <li>
                  <a href="javascript:hideOtherLanguage({$lang.id_lang|escape:'htmlall':'UTF-8'});" tabindex="-1">{$lang.name|escape:'htmlall':'UTF-8'}</a>
                </li>
              {/foreach}
            </ul>
          </div>
        {/if}
        {if $languages|count > 1}
          </div>
        {/if}
        <script>
          $(document).ready(function() {
            $('#{$input.name|escape:"htmlall":"UTF-8"}_{$language.id_lang|escape:"htmlall":"UTF-8"}-selectbutton').click(function(e) {
              $('#{$input.name|escape:"htmlall":"UTF-8"}_{$language.id_lang|escape:"htmlall":"UTF-8"}').trigger('click');
            });
            $('#{$input.name|escape:"htmlall":"UTF-8"}_{$language.id_lang|escape:"htmlall":"UTF-8"}').change(function(e) {
              var val  = $(this).val();
              var file = val.split(/[\\/]/);
              $('#{$input.name|escape:"htmlall":"UTF-8"}_{$language.id_lang|escape:"htmlall":"UTF-8"}-name').val(file[file.length - 1]);
            });
          });
        </script>
      {/foreach}
    </div>
  {/if}
  {$smarty.block.parent}
{/block}