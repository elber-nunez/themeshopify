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

<p class="errors-box alert alert-danger">
  {l s='An error occurred during the HTML saving' mod='jxmegalayout'}
</p>
{if $buttons}
  <a href="#" id="add_extra_content" class="btn btn-default" data-content-type="{$content_type}">{l s='Make one more attempt' mod='jxmegalayout'}</a>
  <p class="extra-content-return-btn"><a href="#" class="return-btn btn-link">{l s='<- Return to the extra content page' mod='jxmegalayout'}</a></p>
{/if}