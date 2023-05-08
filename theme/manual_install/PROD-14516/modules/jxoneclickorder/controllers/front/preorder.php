<?php
/**
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
 */
require_once('../../../../config/config.inc.php');
require_once('../../../../init.php');
require_once('../../jxoneclickorder.php');

if (Tools::getValue('preorderForm')) {
    preorderForm();
} elseif (Tools::getValue('preorderSubmit')) {
    preorderSubmit();
}
exit;
/**
 * Get preorder form
 */
function preorderForm()
{
    $jxoneclickorder = new Jxoneclickorder();
    if (!$form = $jxoneclickorder->renderPreorderForm()) {
        die(json_encode(['status' => false]));
    }
    die(json_encode(['status' => true, 'form' => $form]));
}

/**
 * Send a notification e-mail to the store owner
 */
function notifyOwner($customer)
{
    $jxoneclickorder = new Jxoneclickorder();
    $id_lang = $jxoneclickorder->id_lang;
    $iso = Language::getIsoById($id_lang);
    $template_vars = [];
    $template_vars['{name}'] = isset($customer->name) ? $customer->name : '';
    $template_vars['{number}'] = isset($customer->number) ? $customer->number : '';
    $template_vars['{address}'] = isset($customer->address) ? $customer->address : '';
    $template_vars['{message}'] = isset($customer->message) ? $customer->message : '';
    $template_vars['{email}'] = isset($customer->email) ? $customer->email : '';
    $template_vars['{from}'] = isset($customer->datetime->date_from) ? $customer->datetime->date_from : '';
    $template_vars['{to}'] = isset($customer->datetime->date_to) ? $customer->datetime->date_to : '';
    $id_shop = $jxoneclickorder->id_shop;
    $dir = (file_exists(dirname(__FILE__).'/../../mails/'.$iso.'/notification.txt')
            && file_exists(dirname(__FILE__).'/../../mails/'.$iso.'/notification.html')) ? dirname(__FILE__).'/../../mails/' : false;
    if ($dir) {
        Mail::Send(
            $id_lang,
            'notification',
            Mail::l('New order placed'),
            $template_vars,
            Configuration::get('PS_SHOP_EMAIL'),
            null,
            Configuration::get('PS_SHOP_EMAIL'),
            Configuration::get('PS_SHOP_NAME'),
            null,
            null,
            $dir,
            null,
            $id_shop
        );
    }
}

/**
 * On preorder submit
 */
function preorderSubmit()
{
    $jxoneclickorder = new Jxoneclickorder();
    $context = Context::getContext();
    $customer = json_decode(Tools::getValue('customer'), true);

    if (!$jxoneclickorder->validateCustomerInfo($customer)) {
        die(json_encode(
            [
                'status' => false,
                'errors' => $jxoneclickorder->getErrors(true)
            ]
        ));
    }
    $id_cart = $context->cookie->id_cart;
    $products = [];
    if (Tools::getValue('page_name') != 'cart') {
        $products = [json_decode(Tools::getValue('product'), true)];
        $id_cart = false;
    }
    if (!$jxoneclickorder->createPreorder($customer, $id_cart, $products)) {
        die(json_encode(
            [
                'status' => false
            ]
        ));
    }
    if (ConfigurationCore::get('JXONECLICKORDER_NOTIFY_OWNER')) {
        notifyOwner($customer);
    }
    $content = ConfigurationCore::get('JXONECLICKORDER_SUCCESS_DESCRIPTION', $jxoneclickorder->id_lang);
    die(json_encode(
        [
            'status' => true,
            'content' => $content
        ]
    ));
}
