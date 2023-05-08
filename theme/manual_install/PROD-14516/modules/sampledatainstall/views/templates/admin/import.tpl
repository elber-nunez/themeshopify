{**
* 2017-2018 Zemez
*
* Sampledatainstall
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

<div class="bootstrap" id="data_location_message">
  <div class="alert alert-danger">
    <p>{l s='Please note! Intalling demo store data will completely replace your store content. All products, module settings, pages and configurations will be replaced. Make sure to create store database backup before sample data installation.' mod='sampledatainstall'}</p>
  </div>
  <div class="alert alert-warning">
    <p>{l s='Theme sample data files are located in sources/sample_data.zip archive of your template directory' mod='sampledatainstall'}</p>
  </div>
</div>
<div class="panel">
  <!-- drag drop form -->
  <form enctype="multipart/form-data" method="post" action="" id="upload_files">
    <div id="area-drag-drop">
      <div class="drag-drop-inside">
        <div class="drop_icon"></div>
        <h6 class="drop_heading">{l s='Please Drop all needed files here to import sample data' mod='sampledatainstall'}</h6>
        <p>{l s='or' mod='sampledatainstall'}</p>
        <p class="drag-drop-buttons">
          <button class="upload-files new-btn btn btn-danger btn-lg" type="button" class="button">{l s='Browse local files' mod='sampledatainstall'}</button>
          <input id="upload_files_html5" style="visibility: hidden; width: 0; height: 0; overflow: hidden; margin:0;" type="file" multiple>
        </p>
        {if $actions.uploaded_data}
          <p>{l s='or install already uploaded data' mod='sampledatainstall'}</p>
          <button class="install-uploaded-data new-btn btn btn-danger btn-lg" type="button" class="button">{l s='Install' mod='sampledatainstall'}</button>
        {/if}
        <p class="max-upload-size">
          {l s='Maximum file size:' mod='sampledatainstall'}{$actions.max_file_size_text} <br />
          {l s='You can change it in your server settings.' mod='sampledatainstall'}
        </p>
        {if ($actions.output)}
          <div class="alert alert-warning">
            {l s='Caution! Some of your server settings do not meet the requirements for installing the sample data. Please, consult with your hosting provider on how to increase the required values.' mod='sampledatainstall'}
          </div>
          <table id="server_settings" class='table text-left'>
            <thead>
            <tr>
              <th>{l s='Server Settings' mod='sampledatainstall'}</th>
              <th class="text-center">{l s='Current' mod='sampledatainstall'}</th>
              <th class="text-center">{l s='Required' mod='sampledatainstall'}</th>
            </tr>
            </thead>
            <tbody>
            {$actions.output}
            </tbody>
          </table>
        {/if}
      </div>
    </div>
  </form>

  <!-- end drag drop form -->
  <div id="import_step_2" class="hidden_ell">
    <!-- file_list -->
    <div id="file_list_holder">
      <div id="file_list">
        <div id="file_list_header">
          <div class='row'>
            <div class="column_1">{l s='File name' mod='sampledatainstall'}</div>
            <div class="column_2">{l s='File size' mod='sampledatainstall'}</div>
            <div class="column_3 text-center">{l s='Uploaded file:' mod='sampledatainstall'}
              <span id="upload_counter">
                <b>0</b>
              </span>
              <span class="items_name">{l s='item' mod='sampledatainstall'}</span>
            </div>
          </div>
        </div>
        <div id="file_list_body"></div>
      </div>
    </div>
    <div id="import_xml_status" class="hidden_ell">
      <div id="progress-list" class="row">
        <ul class="col-xs-12 col-lg-3">
          <li data-item="1"></li>
          <li data-item="2"></li>
          <li data-item="3"></li>
          <li data-item="4"></li>
          <li data-item="5"></li>
          <li data-item="6"></li>
          <li data-item="7"></li>
          <li data-item="8"></li>
          <li data-item="9"></li>
          <li data-item="10"></li>
          <li data-item="11"></li>
          <li data-item="12"></li>
          <li data-item="13"></li>
          <li data-item="14"></li>
          <li data-item="15"></li>
        </ul>
        <ul class="col-xs-12 col-lg-3">
          <li data-item="16"></li>
          <li data-item="17"></li>
          <li data-item="18"></li>
          <li data-item="19"></li>
          <li data-item="20"></li>
          <li data-item="21"></li>
          <li data-item="22"></li>
          <li data-item="23"></li>
          <li data-item="24"></li>
          <li data-item="25"></li>
          <li data-item="26"></li>
          <li data-item="27"></li>
          <li data-item="28"></li>
          <li data-item="29"></li>
          <li data-item="30"></li>
        </ul>
        <ul class="col-xs-12 col-lg-3">
          <li data-item="31"></li>
          <li data-item="32"></li>
          <li data-item="33"></li>
          <li data-item="34"></li>
          <li data-item="35"></li>
          <li data-item="36"></li>
          <li data-item="37"></li>
          <li data-item="38"></li>
          <li data-item="39"></li>
          <li data-item="40"></li>
          <li data-item="41"></li>
          <li data-item="42"></li>
          <li data-item="43"></li>
          <li data-item="44"></li>
          <li data-item="45"></li>
        </ul>
      </div>
      <div id="status_log">
        <p><i class ="loader"></i>{l s='Installing demo store. You\'ll like it!' mod='sampledatainstall'}<span class="loader-info">{l s='It can take some time. You can drink some tea and eat a croissant.' mod='sampledatainstall'}</span></p>
      </div>
    </div>
    <!-- end file_list -->
    <div id="import_status" class="clearfix">
      <div id='upload_status'>
        <div class="loader_bar">
          <strong class="loaded-text">0%</strong>
          <span class='transition_2'></span>
        </div>
      </div>
      <div id="info_holder" class="hidden_ell">
        <p>
          <span class="upload_status_text">0%</span>
          <a class="upload-files btn btn-info" href="#">{l s='Add More Files' mod='sampledatainstall'}</a>
          <a class="button button-primary not_active next_step btn btn-success" href="javascript:void(0);" id="submit_install">{l s='Continue Install' mod='sampledatainstall'}</a>
          <span id="not_load_file" class="hidden_ell">{l s='Missing dump.sql file.' mod='sampledatainstall'}</span>
        </p>
      </div>
      <div id="warning_holder"></div>
    </div>
  </div>
</div>
<div class="alert alert-success hidden_ell" id="success_install">
  <p class="clearfix">{l s='Installation is now complete! For correct work of store you need to go to regenerate thumbnails and select the option to create thumbnails.' mod='sampledatainstall'}
    {l s='To jump, click on the button below' mod='sampledatainstall'}
    <button type="submit" onclick="window.location ='{$actions.regenerateDir}'" class="btn btn-primary pull-right" id="regenerate_go">{l s='Go to regenerate page' mod='sampledatainstall'}</button></p>
</div>