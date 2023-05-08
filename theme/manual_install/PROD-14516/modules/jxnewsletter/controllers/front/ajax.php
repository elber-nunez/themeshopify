<?php
/**
 * 2017-2018 Zemez
 *
 * JX Blog Comment
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
 * @author    Zemez (Alexander Grosul)
 * @copyright 2017-2018 Zemez
 * @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
 */

class JXNewsletterAjaxModuleFrontController extends ModuleFrontController
{
    public $jxnewsletter;

    public function initContent()
    {
        $action = Tools::getValue('action');
        $action = str_replace(' ', '', ucwords(str_replace('-', ' ', $action)));
        if (!empty($action) && method_exists($this, 'ajaxProcess'.$action)) {
            $this->{'ajaxProcess'.$action}();
        } else {
            die(json_encode(array('error' => 'method doesn\'t exist')));
        }
    }

    public function ajaxProcessUpdatedate()
    {
        $this->module->updateDate((int)Tools::getValue('status'));
    }

    public function ajaxProcessGetNewsletterTemplate()
    {
        $result = $this->module->getNewsletterTemplate(Tools::getValue('type'));
        if (!$result && Tools::isEmpty($result)) {
            die(Tools::jsonEncode(array('content' => false)));
        }
        die(Tools::jsonEncode(array('content' => $result)));
    }

    public function ajaxProcessSendemail()
    {
        $email = Tools::getValue('email');
        $status = Tools::getValue('status');
        $is_logged = (int)Tools::getValue('is_logged');

        if ($is_logged) {
            $is_guest = 0;
        } else {
            $is_guest = 1;
        }

        if (Validate::isEmail($email)) {
            if ($result = $this->module->newsletterRegistration($email, $is_guest)) {
                $this->module->updateDate((int)$status);
                die(Tools::jsonEncode(array('success_status' => $result)));
            }
            die(Tools::jsonEncode(array('error_status' => 'Something went wrong!')));
        }
        die(Tools::jsonEncode(array('error_status' => 'Something went wrong!')));
    }
}
