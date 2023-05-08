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

<div class="extra-content-import">
  <form id="import_extra_content_form" class="defaultForm form-horizontal" method="post" enctype="multipart/form-data" novalidate="">
    <p class="alert alert-danger hidden">{l s='Wrong file! Only extracontent.zip file is allowed.' mod='jxmegalayout'}</p>
    <div class="form-wrapper">
      <div class="form-group">
        <label class="control-label col-lg-3">
          {l s='Zip file' mod='jxmegalayout'}
        </label>
        <div class="col-lg-5">
          <div class="form-group">
            <div class="col-sm-12">
              <input id="extraContentArchive" type="file" name="extraContentArchive" class="hide">
              <div class="dummyfile input-group">
                <span class="input-group-addon"><i class="icon-file"></i></span>
                <input id="extraContentArchiveName" type="text" name="filename" readonly="">
              <span class="input-group-btn">
                <button id="selectExtraContentArchive" type="button" name="submitAddExtraContent" class="btn btn-default">{l s='Add file' mod='jxmegalayout'}</button>
              </span>
              </div>
            </div>
          </div>
          <p class="help-block text-center">
            <p class="alert alert-warning">{l s='Be aware that only archives generted by this module are suported. Do not try to use any other content it would cause issues!' mod='jxmegalayout'}</p>
            {l s='Browse your computer files and select the Zip file with your Extra Content data.' mod='jxmegalayout'}<br>
            {l s='Maximum file size:' mod='jxmegalayout'}{Jxmegalayout::getMaxFileSize()}.<br>
            {l s='You can change it in your server settings.' mod='jxmegalayout'}
          </p>
        </div>
      </div>
    </div>
  </form>
</div>