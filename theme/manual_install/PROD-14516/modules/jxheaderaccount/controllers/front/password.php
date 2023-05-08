<?php
/**
 * 2017-2018 Zemez
 *
 * JX Header Account
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
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class JxHeaderAccountPasswordModuleFrontController extends PasswordController
{
    public function postProcess()
    {
        $this->setTemplate('customer/password-email');

        if (Tools::isSubmit('email')) {
            $this->sendRenewPasswordLink();
        }
        if (Tools::isSubmit('retrievePassword')) {
            $this->retrievePassword();
        }
    }

    protected function retrievePassword()
    {
        $return = array(
            'hasError' => !empty($this->errors),
            'errors' => $this->errors,
            'token' => Tools::getToken(false),
            'confirm' => $this->success
        );
        die(json_encode($return));
    }
}
