<?php
/**
 * 2017-2018 Zemez
 *
 * JX Wishlist
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

class JxWishlistWishlistsModuleFrontController extends ModuleFrontController
{
    public $products = '';

    public function initContent()
    {
        parent::initContent();
        $action = Tools::getValue('action');

        if (!Tools::isSubmit('myajax')) {
            $this->assign();
        } elseif (!empty($action) && method_exists($this, 'ajaxProcess'.Tools::toCamelCase($action))) {
            $this->{'ajaxProcess' . Tools::toCamelCase($action)}();
        } else {
            die(Tools::jsonEncode(array('error' => 'method doesn\'t exist')));
        }
    }

    public function assign()
    {
        $this->errors = array();
        $context = Context::getContext();

        if ($this->context->customer->isLogged()) {
            if (Tools::isSubmit('submitWishlists')) {
                $name = Tools::getValue('name');

                if (empty($name)) {
                    $this->errors = Tools::displayError($this->module->l('You must specify a name.'));
                } elseif (ClassJxWishlist::isExistsByNameForUser($name)) {
                    $this->errors = $this->module->l('This name is already used by another list.');
                }

                if (!count($this->errors)) {
                    $wishlists = new ClassJxWishlist();
                    $wishlists->id_shop = $this->context->shop->id;
                    $wishlists->name = $name;
                    $wishlists->id_customer = (int)$this->context->customer->id;
                    $wishlists->token = Tools::strtoupper(Tools::substr(sha1(uniqid(rand(), true)._COOKIE_KEY_.$this->context->customer->id), 0, 16));
                    $wishlists->add();
                    $confirmation_add = true;
                    $confirmation_name = $name;
                }
            }
            if (Tools::isSubmit('changeWishlist')) {
                $name = Tools::getValue('name');

                if (empty($name)) {
                    $this->errors = Tools::displayError($this->module->l('You must specify a name.'));
                }

                $id_wishlist = Tools::getValue('id_wishlist');

                if (!count($this->errors)) {
                    $wishlists = new ClassJxWishlist($id_wishlist);
                    $wishlists->name = $name;

                    if (!$wishlists->update()) {
                        $this->errors = Tools::displayError($this->module->l('This name no change'));
                    } else {
                        $confirmation_change = true;
                    }
                }

            }

            if (isset($confirmation_change)) {
                $this->context->smarty->assign('confirmation_change', $confirmation_change);
            }

            if (isset($confirmation_add)) {
                $this->context->smarty->assign('confirmation_add', $confirmation_add);
                $this->context->smarty->assign('confirmation_name', $confirmation_name);
            }

            $this->context->smarty->assign('wishlists', ClassJxWishlist::getByIdCustomer($this->context->customer->id));



            $context->smarty->assign(array(
                'img_path' => _PS_BASE_URL_ . __PS_BASE_URI__ . 'modules/jxwishlist/views/tmp/',
                'id_lang' => $context->language->id,
                'jx_wishlist_app_id' => Configuration::get('JX_WISHLIST_APP_ID')
            ));

        } else {
            Tools::redirect('index.php?controller=authentication&back=' . urlencode($this->context->link->getModuleLink('jxwishlist', 'mywishlists')));
        }

        $this->context->smarty->assign(array(
            'id_customer' => (int)$this->context->customer->id,
        ));

        $this->setTemplate('module:jxwishlist/views/templates/front/wishlists.tpl');
    }

    /**
     * Delete wishlist
     * @return array
     */
    public function ajaxProcessDeleteList()
    {
        if (!$this->context->customer->isLogged()) {
            die(Tools::jsonEncode(array('success' => false, 'error' => $this->module->l('You aren\'t logged in'))));
        }

        $id_wishlist = Tools::getValue('id_wishlist');
        $wishlist = new ClassJxWishlist((int)$id_wishlist);

        if (Validate::isLoadedObject($wishlist) && $wishlist->id_customer == $this->context->customer->id) {
            $wishlist->delete();
        } else {
            die(Tools::jsonEncode(array('success' => false, 'error' => $this->module->l('Cannot delete this wishlist'))));
        }

        die(Tools::jsonEncode(array('success' => true)));
    }

    /**
     * Edit wishlist name
     * @return array
     */
    public function ajaxProcessEditList()
    {
        if (!$this->context->customer->isLogged()) {
            die(Tools::jsonEncode(array('success' => false, 'error' => $this->module->l('You aren\'t logged in'))));
        }

        $id_wishlist = Tools::getValue('id_wishlist');
        $wishlist = new ClassJxWishlist((int)($id_wishlist));
        $name_wishlist = $wishlist->name;

        die(Tools::jsonEncode(array('success' => true, 'name_wishlist' => $name_wishlist, 'id_wishlist' => $id_wishlist)));
    }

    /**
     * Add product to wishlist
     * @return array
     */
    public function ajaxProcessAddProduct()
    {
        $context = Context::getContext();
        $action_add = Tools::getValue('action_add');
        $add = (!strcmp($action_add, 'action_add') ? 1 : 0);
        $id_wishlist = (int)Tools::getValue('id_wishlist');
        $id_product = (int)Tools::getValue('id_product');
        $id_product_attribute = (int)Tools::getValue('id_product_attribute');
        $quantity = (int)Tools::getValue('quantity');

        if (!$this->context->customer->isLogged()) {
            die(Tools::jsonEncode(array('success' => false, 'error' => $this->module->l('You aren\'t logged in'))));
        }

        if ($id_wishlist && ClassJxWishlist::exists($id_wishlist, $context->customer->id) === true) {
            $context->cookie->id_wishlist = (int)$id_wishlist;
        }

        if ((int)$context->cookie->id_wishlist > 0 && !ClassJxWishlist::exists($context->cookie->id_wishlist, $context->customer->id)) {
            $context->cookie->id_wishlist = '';
        }

        if (empty($context->cookie->id_wishlist) === true || $context->cookie->id_wishlist == false) {
            $context->smarty->assign('error', true);
        }

        if (!isset($context->cookie->id_wishlist) || $context->cookie->id_wishlist == '') {
            $wishlists = new ClassJxWishlist();
            $wishlists->id_shop = $context->shop->id;
            $mod_wishlists = new jxwishlist();
            $wishlists->name = $mod_wishlists->default_wishlist_name;
            $wishlists->id_customer = (int)$context->customer->id;
            list($us, $s) = explode(' ', microtime());
            srand($s * $us);
            $wishlists->token = Tools::strtoupper(Tools::substr(sha1(uniqid(rand(), true)._COOKIE_KEY_.$context->customer->id), 0, 16));
            $wishlists->add();
            $context->cookie->id_wishlist = (int)$wishlists->id;
        }

        if ($add && $quantity) {
            ClassJxWishlist::addProduct($context->cookie->id_wishlist, $this->context->customer->id, $id_product, $id_product_attribute, $quantity);
        } else {
            die(Tools::jsonEncode(array('success' => false, 'error' => $this->module->l('Cannot add this product'))));
        }

        die(Tools::jsonEncode(array('success' => true)));
    }

    /**
     * Delete product with wishlist
     * @return array
     */
    public function ajaxProcessDeleteProduct()
    {
        $context = Context::getContext();

        if ($context->customer->isLogged()) {
            $action = Tools::getValue('action');
            $id_wishlist = (int)Tools::getValue('id_wishlist');
            $id_product = (int)Tools::getValue('id_product');
            $id_product_attribute = (int)Tools::getValue('id_product_attribute');

            if (!strcmp($action, 'deleteproduct')) {
                ClassJxWishlist::removeProduct($id_wishlist, $id_product, $id_product_attribute);
            } else {
                die(Tools::jsonEncode(array('success' => false, 'error' => $this->module->l('Cannot delete this product'))));
            }
        }

        die(Tools::jsonEncode(array('success' => true)));
    }

    /**
     * Get product by id
     * @return array
     */
    public function ajaxProcessGetProductsById()
    {
        $context = Context::getContext();
        $id_wishlist = (int)Tools::getValue('id_wishlist');
        $products = ClassJxWishlist::getProductByIdWishlist($id_wishlist);
        $jxwishlist = new jxwishlist;

        foreach ($products as $k => $pr) {
            $product= new Product((int)($pr['id_product']), false, $context->language->id);
            if ($pr['id_product_attribute'] != 0) {
                $img_combination = $product->getCombinationImages($context->language->id);
                if (isset($img_combination[$pr['id_product_attribute']][0])) {
                    $products[$k]['cover'] = $product->id.'-'.$img_combination[$pr['id_product_attribute']][0]['id_image'];
                } else {
                    $cover = Product::getCover($product->id);
                    $products[$k]['cover'] = $product->id.'-'.$cover['id_image'];
                }
            } else {
                $images = $product->getImages($context->language->id);
                foreach ($images as $image) {
                    if ($image['cover']) {
                        $products[$k]['cover'] = $product->id.'-'.$image['id_image'];
                        break;
                    }
                }
            }
            if (!isset($products[$k]['cover'])) {
                $products[$k]['cover'] = $context->language->iso_code.'-default';
            }
        }

        $this->products .= $jxwishlist->getAjaxHtml('product', $context->smarty->assign(array('products' => $products)));

        die(Tools::jsonEncode(array('response' => $this->products)));
    }

    /**
     * Image path for create wish list image
     */
    public static function imagesPath()
    {
        return dirname(_PS_MODULE_DIR_ . 'jxwishlist/views/tmp/..');
    }

    /**
     * Get id for number item
     * @return array
     */
    public function getID($min, $max)
    {
        $numbers = range($min, $max);
        return array_slice($numbers, 0);
    }

    /**
     * Cut string
     *
     * @return result string
     */
    public function mbCutString($str, $length, $postfix = '...', $encoding = 'UTF-8')
    {
        if (mb_strlen($str, $encoding) <= $length) {
            return $str;
        }

        $tmp = mb_substr($str, 0, $length, $encoding);
        return mb_substr($tmp, 0, mb_strripos($tmp, ' ', 0, $encoding), $encoding) . $postfix;
    }

    /**
     * Get image by id wish list
     *
     * @return array
     */
    public function ajaxProcessGetImageById()
    {
        $id_wishlist = (int)Tools::getValue('id_wishlist');
        $name_wishlist = Tools::getValue('name_wishlist');
        $id_layout = (int)Tools::getValue('id_layout');
        $id_product = Tools::jsonDecode(Tools::getValue('id_product'));
        $products = array();
        $attributes = array();
        $prod = array();
        $attr = array();

        foreach ($id_product as $key => $value) {
            $attributes[$key]['id_product_attribute'] = explode("_", $value, 2);
            $products[$key]['id_product'] = explode("_", $value, 2);
            foreach ($products as $k => $product) {
                $prod[$k]["id_product"] = $product["id_product"][0];
            }
            foreach ($attributes as $t => $attribute) {
                $attr[$t] = $attribute["id_product_attribute"][1];
            }
        }

        $image_path = array();
        $name = array();
        $products = ClassJxWishlist::getProductByIdWishlist($id_wishlist);

        foreach ($products as $k => $product) {
            foreach ($prod as $p) {
                if ($product['id_product'] == $p['id_product']) {
                    $name[$k] = $product['name'];
                    $id_image = Product::getCover($product['id_product']);
                    if (sizeof($id_image) > 0) {
                        $image = new Image($id_image['id_image']);
                        $image_path[$k] = _PS_IMG_DIR_.'p/'.$image->getExistingImgPath().'.jpg';
                    }
                }
            }
        }
        
        ImageManager::resize(_PS_IMG_DIR_.Configuration::get('PS_LOGO'), _PS_MODULE_DIR_ . 'jxwishlist/views/tmp/logo.jpg', 140, 60);
        if (Configuration::get('PS_IMAGE_QUALITY') == 'jpg') {
            $logo = imagecreatefromjpeg(_PS_MODULE_DIR_ . 'jxwishlist/views/tmp/logo.jpg');
        } else {
            $logo = imagecreatefrompng(_PS_MODULE_DIR_ . 'jxwishlist/views/tmp/logo.jpg');
        }

        $dest = imagecreatetruecolor(487, 255);
        imagefill($dest, 0, 0, 0xFFFFFF);
        $color_black = imagecolorallocate($dest, 27, 27, 27);
        $color_grey_text = imagecolorallocate($dest, 100, 100, 100);
        $font_file_regular = _PS_MODULE_DIR_ . 'jxwishlist/views/fonts/OpenSans-Semibold.ttf';
        $font_file_semibold = _PS_MODULE_DIR_ . 'jxwishlist/views/fonts/OpenSans-Semibold.ttf';
        $name_product = array_values($name);
        $product_img_path = array_values($image_path);
        $product_img = array();

        if ($id_layout == 1) {
            if (file_exists(_PS_MODULE_DIR_ . 'jxwishlist/views/tmp/' . $id_wishlist . '-wishlist.jpg')) {
                unlink(_PS_MODULE_DIR_ . 'jxwishlist/views/tmp/' . $id_wishlist . '-wishlist.jpg');
            }
            ImageManager::resize(implode($image_path), _PS_MODULE_DIR_ . 'jxwishlist/views/tmp/image_1.jpg', 153, 208);

            $type_img = getimagesize(_PS_MODULE_DIR_ . 'jxwishlist/views/tmp/image_1.jpg');

            if ($type_img['mime'] == 'image/png') {
                $src_tmp = imagecreatefrompng(_PS_MODULE_DIR_ . 'jxwishlist/views/tmp/image_1.jpg');
                $w = imagesx($src_tmp);
                $h = imagesy($src_tmp);
                $src = imagecreatetruecolor($w, $h);
                imagefilledrectangle($src, 0, 0, $w, $h, imagecolorallocate($src, 255, 255, 255));
                imagecopyresampled($src, $src_tmp, 0, 0, 0, 0, $w, $h, $w, $h);
                imagejpeg($src, _PS_MODULE_DIR_ . 'jxwishlist/views/tmp/image_1.jpg');
            } elseif ($type_img['mime'] == 'image/jpeg') {
                $src = imagecreatefromjpeg(_PS_MODULE_DIR_ . 'jxwishlist/views/tmp/image_1.jpg');
            }

            $border_color = imagecolorallocate($src, 216, 216, 216);
            imageline($src, 0, 0, 0, imagesy($src), $border_color);
            imageline($src, 0, 0, imagesx($src), 0, $border_color);
            imageline($src, imagesx($src)-1, 0, imagesx($src)-1, imagesy($src)-1, $border_color);
            imageline($src, 0, imagesy($src)-1, imagesx($src)-1, imagesy($src)-1, $border_color);
            imagecopymerge($dest, $src, 20, 20, 0, 0, imagesx($src), imagesy($src), 100);
            imagecopy($dest, $logo, 250, 50, 0, 0, 140, 60);
            imagefttext($dest, 16, 0, 250, 150, $color_black, $font_file_semibold, $this->mbCutString($name_wishlist, 20));
            imagefttext($dest, 12, 0, 250, 180, $color_grey_text, $font_file_regular, $this->mbCutString(implode($name), 30));
            imagejpeg($dest, _PS_MODULE_DIR_ . 'jxwishlist/views/tmp/'.$id_wishlist.'-wishlist.jpg');
            imagedestroy($dest);
            unlink(_PS_MODULE_DIR_ . 'jxwishlist/views/tmp/image_1.jpg');
            unlink(_PS_MODULE_DIR_ . 'jxwishlist/views/tmp/logo.jpg');
            unlink(_PS_MODULE_DIR_ . 'jxwishlist/views/tmp/fileType');
        } elseif ($id_layout == 2) {
            $offset_img_width = 20;
            $offset_img_height = 20;
            if (file_exists(_PS_MODULE_DIR_ . 'jxwishlist/views/tmp/' . $id_wishlist . '-wishlist.jpg')) {
                unlink(_PS_MODULE_DIR_ . 'jxwishlist/views/tmp/' . $id_wishlist . '-wishlist.jpg');
            }
            foreach ($product_img_path as $key => $image) {
                if ($key == 0) {
                    ImageManager::resize($image, _PS_MODULE_DIR_ . 'jxwishlist/views/tmp/image_'.$key.'.jpg', 153, 208);
                } elseif ($key == 1) {
                    ImageManager::resize($image, _PS_MODULE_DIR_ . 'jxwishlist/views/tmp/image_'.$key.'.jpg', 101, 136);
                }
            }

            $dir_files = Tools::scandir($this->imagesPath(), 'jpg');

            foreach ($dir_files as $key => $result) {
                if ($result != 'logo.jpg') {
                    $product_img[] = _PS_MODULE_DIR_ . 'jxwishlist/views/tmp/' . $result;
                }
            }

            $ids_img = $this->getID(0, count($product_img));

            for ($i = 0; $i < count($product_img); $i++) {
                $index = $ids_img[$i];
                $type_img = getimagesize($product_img[$index]);

                if ($type_img['mime'] == 'image/png') {
                    $src_tmp = imagecreatefrompng($product_img[$index]);
                    $w = imagesx($src_tmp);
                    $h = imagesy($src_tmp);
                    $src = imagecreatetruecolor($w, $h);
                    imagefilledrectangle($src, 0, 0, $w, $h, imagecolorallocate($src, 255, 255, 255));
                    imagecopyresampled($src, $src_tmp, 0, 0, 0, 0, $w, $h, $w, $h);
                    imagejpeg($src, $product_img[$index]);
                } elseif ($type_img['mime'] == 'image/jpeg') {
                    $src = imagecreatefromjpeg($product_img[$index]);
                }

                $color_grey = imagecolorallocate($src, 216, 216, 216);
                imageline($src, 0, 0, 0, imagesy($src), $color_grey);
                imageline($src, 0, 0, imagesx($src), 0, $color_grey);
                imageline($src, imagesx($src)-1, 0, imagesx($src)-1, imagesy($src)-1, $color_grey);
                imageline($src, 0, imagesy($src)-1, imagesx($src)-1, imagesy($src)-1, $color_grey);
                imagecopymerge($dest, $src, $offset_img_width, $offset_img_height, 0, 0, imagesx($src), imagesy($src), 100);
                $offset_img_width = $offset_img_width + imagesx($src) + 20;
                $offset_img_height = $offset_img_height + 72;
            }

            foreach ($product_img as $key => $image) {
                unlink(_PS_MODULE_DIR_ . 'jxwishlist/views/tmp/image_'.$key.'.jpg');
            }

            $ids = $this->getID(0, count($name_product));
            $offset = 170;

            for ($i = 0; $i < count($name_product); $i++) {
                $index = $ids[$i];
                $src = $name_product[$index];
                imagefttext($dest, 10, 0, 320, $offset, $color_grey_text, $font_file_regular, $this->mbCutString($src, 25));
                $offset = $offset  + 20;
            }

            imagefttext($dest, 16, 0, 320, 140, $color_black, $font_file_semibold, $this->mbCutString($name_wishlist, 15));
            imagecopy($dest, $logo, 320, 20, 0, 0, 140, 60);
            imagejpeg($dest, _PS_MODULE_DIR_ . 'jxwishlist/views/tmp/'.$id_wishlist.'-wishlist.jpg');
            imagedestroy($dest);
            unlink(_PS_MODULE_DIR_ . 'jxwishlist/views/tmp/logo.jpg');
            unlink(_PS_MODULE_DIR_ . 'jxwishlist/views/tmp/fileType');
        } elseif ($id_layout == 3) {
            if (file_exists(_PS_MODULE_DIR_ . 'jxwishlist/views/tmp/' . $id_wishlist . '-wishlist.jpg')) {
                unlink(_PS_MODULE_DIR_ . 'jxwishlist/views/tmp/' . $id_wishlist . '-wishlist.jpg');
            }
            $offset_img_width = 20;

            foreach ($product_img_path as $key => $image) {
                ImageManager::resize($image, _PS_MODULE_DIR_ . 'jxwishlist/views/tmp/image_'.$key.'.jpg', 97, 136);
            }

            $dir_files = Tools::scandir($this->imagesPath(), 'jpg');

            foreach ($dir_files as $key => $result) {
                if ($result != 'logo.jpg') {
                    $product_img[] = _PS_MODULE_DIR_ . 'jxwishlist/views/tmp/' . $result;
                }
            }

            $ids_img = $this->getID(0, count($product_img));

            for ($i = 0; $i < count($product_img); $i++) {
                $index = $ids_img[$i];
                $type_img = getimagesize($product_img[$index]);

                if ($type_img['mime'] == 'image/png') {
                    $src_tmp = imagecreatefrompng($product_img[$index]);
                    $w = imagesx($src_tmp);
                    $h = imagesy($src_tmp);
                    $src = imagecreatetruecolor($w, $h);
                    imagefilledrectangle($src, 0, 0, $w, $h, imagecolorallocate($src, 255, 255, 255));
                    imagecopyresampled($src, $src_tmp, 0, 0, 0, 0, $w, $h, $w, $h);
                    imagejpeg($src, $product_img[$index]);
                } elseif ($type_img['mime'] == 'image/jpeg') {
                    $src = imagecreatefromjpeg($product_img[$index]);
                }
                $color_grey = imagecolorallocate($src, 216, 216, 216);
                imageline($src, 0, 0, 0, imagesy($src), $color_grey);
                imageline($src, 0, 0, imagesx($src), 0, $color_grey);
                imageline($src, imagesx($src)-1, 0, imagesx($src)-1, imagesy($src)-1, $color_grey);
                imageline($src, 0, imagesy($src)-1, imagesx($src)-1, imagesy($src)-1, $color_grey);
                imagecopymerge($dest, $src, $offset_img_width, 92, 0, 0, imagesx($src), imagesy($src), 100);
                $offset_img_width = $offset_img_width + imagesx($src) + 20;
            }

            foreach ($product_img as $key => $image) {
                unlink(_PS_MODULE_DIR_ . 'jxwishlist/views/tmp/image_'.$key.'.jpg');
            }

            imagecopy($dest, $logo, 325, 15, 0, 0, 140, 60);
            unlink(_PS_MODULE_DIR_ . 'jxwishlist/views/tmp/logo.jpg');
            unlink(_PS_MODULE_DIR_ . 'jxwishlist/views/tmp/fileType');
            imagefttext($dest, 16, 0, 20, 55, $color_black, $font_file_semibold, $name_wishlist);
            imagejpeg($dest, _PS_MODULE_DIR_ . 'jxwishlist/views/tmp/'.$id_wishlist.'-wishlist.jpg');
            imagedestroy($dest);
        } elseif ($id_layout == 4) {
            if (file_exists(_PS_MODULE_DIR_ . 'jxwishlist/views/tmp/' . $id_wishlist . '-wishlist.jpg')) {
                unlink(_PS_MODULE_DIR_ . 'jxwishlist/views/tmp/' . $id_wishlist . '-wishlist.jpg');
            }
            $name_product = array_values($name);
            $product_img_path = array_values($image_path);

            foreach ($product_img_path as $key => $image) {
                ImageManager::resize($image, _PS_MODULE_DIR_ . 'jxwishlist/views/tmp/image_'.$key.'.jpg', 75, 95);
            }

            $dir_files = Tools::scandir($this->imagesPath(), 'jpg');

            foreach ($dir_files as $key => $result) {
                if ($result != 'logo.jpg') {
                    $product_img[] = _PS_MODULE_DIR_ . 'jxwishlist/views/tmp/' . $result;
                }
            }

            $ids_img = $this->getID(0, count($product_img));
            $offset_img_width = 295;
            $offset_img_height = 20;

            for ($i = 0; $i < count($product_img); $i++) {
                $index = $ids_img[$i];
                $type_img = getimagesize($product_img[$index]);

                if ($type_img['mime'] == 'image/png') {
                    $src_tmp = imagecreatefrompng($product_img[$index]);
                    $w = imagesx($src_tmp);
                    $h = imagesy($src_tmp);
                    $src = imagecreatetruecolor($w, $h);
                    imagefilledrectangle($src, 0, 0, $w, $h, imagecolorallocate($src, 255, 255, 255));
                    imagecopyresampled($src, $src_tmp, 0, 0, 0, 0, $w, $h, $w, $h);
                    imagejpeg($src, $product_img[$index]);
                } elseif ($type_img['mime'] == 'image/jpeg') {
                    $src = imagecreatefromjpeg($product_img[$index]);
                }
                $colorGrey = imagecolorallocate($src, 216, 216, 216);
                imageline($src, 0, 0, 0, imagesy($src), $colorGrey);
                imageline($src, 0, 0, imagesx($src), 0, $colorGrey);
                imageline($src, imagesx($src)-1, 0, imagesx($src)-1, imagesy($src)-1, $colorGrey);
                imageline($src, 0, imagesy($src)-1, imagesx($src)-1, imagesy($src)-1, $colorGrey);
                imagecopymerge($dest, $src, $offset_img_width, $offset_img_height, 0, 0, imagesx($src), imagesy($src), 100);
                $offset_img_width = $offset_img_width + imagesx($src) + 20;
                if ($i == 1) {
                    $offset_img_width = $offset_img_width - 285;
                    $offset_img_height = $offset_img_height + imagesy($src) + 20;
                }
            }

            foreach ($product_img as $key => $image) {
                unlink(_PS_MODULE_DIR_ . 'jxwishlist/views/tmp/image_'.$key.'.jpg');
            }

            $ids = $this->getID(0, count($name_product));
            $offset = 70;

            for ($i = 0; $i < count($name_product); $i++) {
                $index = $ids[$i];
                $src = $name_product[$index];
                imagefttext($dest, 10, 0, 20, $offset, $color_grey_text, $font_file_regular, $this->mbCutString($src, 30));
                $offset = $offset  + 20;
            }

            imagefttext($dest, 16, 0, 20, 40, $color_black, $font_file_semibold, $name_wishlist);
            imagecopy($dest, $logo, 20, 170, 0, 0, 140, 60);
            imagejpeg($dest, _PS_MODULE_DIR_ . 'jxwishlist/views/tmp/'.$id_wishlist.'-wishlist.jpg');
            imagedestroy($dest);
            unlink(_PS_MODULE_DIR_ . 'jxwishlist/views/tmp/fileType');
            unlink(_PS_MODULE_DIR_ . 'jxwishlist/views/tmp/logo.jpg');
        }

        die(Tools::jsonEncode(array('status' => 'true')));
    }
}
