{*
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

<div class="bootstrap confirm-data-container">
  <h3>{l s='Select what you want to load' mod='sampledatainstall'}</h3>
  <div class="selected-type active">
    <span class="icon icon-download"></span>
    <span class="text">{l s='Load all content' mod='sampledatainstall'}</span>
    <input class="hidden" name="all" type="checkbox" checked="checked" />
    </div>
  <ul id="download-type">
    <li class="disabled active">
      <span class="icon icon-thumbs-up"></span>
      <span class="text">{l s='Load logo' mod='sampledatainstall'}</span>
      <input class="hidden" name="logos" type="checkbox" checked="checked" />
    </li>
    <li class="disabled active">
      <span class="icon icon-tags"></span>
      <span class="text">{l s='Load content' mod='sampledatainstall'}</span>
      <input class="hidden" name="content" type="checkbox" checked="checked" />
      </li>
    <li class="disabled active">
      <span class="icon icon-th-large"></span>
      <span class="text">{l s='Load modules' mod='sampledatainstall'}</span>
      <input class="hidden" name="modules" type="checkbox" checked="checked" />
      </li>
    </ul>
  <div class="btn-container">
    <button id="confirm-data-installation" class="btn btn-default">{l s='Confirm' mod='sampledatainstall'}</button>
  </div>
</div>