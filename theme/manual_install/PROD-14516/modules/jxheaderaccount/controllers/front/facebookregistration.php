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
require_once(dirname(__FILE__).'/../../src/entities/JXHeaderAccountEntity.php');

class JxHeaderAccountFacebookRegistrationModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        if ($this->context->customer->isLogged()) {
            Tools::redirect('index.php?controller=my-account');
        }

        $user_id = Tools::getValue('user_id');
        $user_name = Tools::getValue('user_name');
        $email = Tools::getValue('email');
        $given_name = Tools::getValue('given_name');
        $family_name = Tools::getValue('family_name');
        $gender = Tools::getValue('gender');
        $profile_url = Tools::getValue('profile_url');
        $profile_image_url = 'https://graph.facebook.com/'.Tools::getValue('user_id').'/picture?width=300&height=300';

        $email = trim($email);

        if (empty($email)) {
                $this->errors[] = Tools::displayError('An email address required.');
        } elseif (!Validate::isEmail($email)) {
                $this->errors[] = Tools::displayError('Invalid email address.');
        }
            
        if (Customer::customerExists($email)) {
            $customer = new Customer();
            $customer->getByEmail($email);

            $headeraccount = new HeaderAccount();
            $facebook_id = $headeraccount->getSocialIdByRest('facebook', $customer->id);

            if ($facebook_id) {
                if ($facebook_id == (int)$user_id) {
                    Tools::redirect($this->context->link->getModuleLink('jxheaderaccount', 'facebooklogin', array(), false, $this->context->language->id));
                } else {
                    $this->errors[] = Tools::displayError('An error occurred while linking your Facebook account.');
                }
            } else {
                $new_customer = new HeaderAccount();
                $new_customer->id_customer = (int)$customer->id;
                $new_customer->social_id = $user_id;
                $new_customer->avatar_url = $profile_image_url;
                $new_customer->social_type = 'facebook';
                $new_customer->id_shop = (int)$this->context->getContext()->shop->id;
                if ($new_customer->add()) {
                    $this->errors[] = Tools::displayError('an error occurred while linking your Facebook account.');
                }

                $customer->active = 1;
                $customer->deleted = 0;
                $this->context->cookie->id_customer = (int)$customer->id;
                $this->context->cookie->customer_lastname = $customer->lastname;
                $this->context->cookie->customer_firstname = $customer->firstname;
                $this->context->cookie->logged = 1;
                $this->context->cookie->passwd = $customer->passwd;
                $this->context->cookie->email = $customer->email;

                if (Configuration::get('PS_CART_FOLLOWING')
                    && (empty($this->context->cookie->id_cart)
                    || Cart::getNbProducts($this->context->cookie->id_cart) == 0)) {
                    $this->context->cookie->id_cart = (int)Cart::lastNoneOrderedCart((int)$customer->id);
                }

                Hook::exec('authentication');

                // redirection: if cart is not empty : redirection to the cart
                if (count($this->context->cart->getProducts(true)) > 0) {
                    Tools::redirect('index.php?controller=order&multi-shipping='.(int)Tools::getValue('multi-shipping'));
                // else : redirection to the account
                } else {
                    Tools::redirect('index.php?controller='.(($this->authRedirection !== false) ? urlencode($this->authRedirection) : 'my-account'));
                }
            }
        }

        if (Tools::getValue('done') && !sizeof($this->errors)) {
            $customer = new Customer();

            if ($gender == 'male') {
                $_POST['id_gender'] = 1;
            } elseif ($gender == 'female') {
                $_POST['id_gender'] = 2;
            } else {
                $_POST['id_gender'] = 0;
            }

            $customer->lastname = $_POST['lastname'] = Tools::getValue('family_name');
            $customer->firstname = $_POST['firstname'] = Tools::getValue('given_name');
            $customer->password = $_POST['passwd'] = Tools::passwdGen();
            $customer->email  = $_POST['email'] = Tools::getValue('email');
            $this->errors = $customer->validateController();

            if (!sizeof($this->errors)) {
                $customer->active = 1;
                if (!$customer->add()) {
                    $this->errors[] = Tools::displayError('an error occurred while creating your account');
                } else {
                    $new_customer = new HeaderAccount();
                    $new_customer->id_customer = (int)$customer->id;
                    $new_customer->social_id = $user_id;
                    $new_customer->avatar_url = $profile_image_url;
                    $new_customer->social_type = 'facebook';
                    $new_customer->id_shop = (int)$this->context->getContext()->shop->id;
                    if (!$new_customer->add()) {
                        $this->errors[] = Tools::displayError('an error occurred while linking your Facebook account.');
                    }

                    $this->context->smarty->assign('confirmation', 1);
                    $this->context->cookie->id_customer = (int)$customer->id;
                    $this->context->cookie->customer_lastname = $customer->lastname;
                    $this->context->cookie->customer_firstname = $customer->firstname;
                    $this->context->cookie->passwd = $customer->passwd;
                    $this->context->cookie->logged = 1;
                    $this->context->cookie->email = $customer->email;

                    Hook::exec('createAccount', array(
                        '_POST' => $_POST,
                        'newCustomer' => $customer
                    ));

                        // redirection: if cart is not empty : redirection to the cart
                    if (count($this->context->cart->getProducts(true)) > 0) {
                        Tools::redirect('index.php?controller=order&multi-shipping='.(int)Tools::getValue('multi-shipping'));
                        // else : redirection to the account
                    } else {
                        Tools::redirect('index.php?controller='.(($this->authRedirection !== false) ? urlencode($this->authRedirection) : 'my-account'));
                    }
                }
            }
        }

        $this->context->smarty->assign(array(
            'user_id' => $user_id,
            'user_name' => $user_name,
            'email' => $email,
            'given_name' => $given_name,
            'family_name' => $family_name,
            'gender' => $gender,
            'profile_url' => $profile_url,
            'profile_image_url' => $profile_image_url,
            'error' => $this->errors,
            'id_module' => $this->module->id
        ));

        $this->setTemplate('module:jxheaderaccount/views/templates/front/facebookregistration.tpl');
    }
}
