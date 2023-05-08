<?php
/**
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
 * @author    Zemez (Alexander Grosul)
 * @copyright 2017-2018 Zemez
 * @license   http://opensource.org/licenses/GPL-2.0 General Public License (GPL 2.0)
 */

class JXBlogImageManager
{
    public $module;
    public $languages;

    public function __construct($module = false)
    {
        $this->module = $module;
        $this->languages = Language::getLanguages(false);
    }

    /**
     * Upload an image and generate all related images from this one if auto regeneration option is enabled
     *
     * @param $id post/category to which an image is related to
     * @param $file an image file information $_FILE
     * @param $type typo of image category/post/user
     *
     * @return bool|mixed|string
     */
    public function uploadImage($id, $file, $type)
    {
        if (isset($file['name']) && isset($file['tmp_name']) && !Tools::isEmpty($file['tmp_name'])) {
            if ($error = ImageManager::validateUpload($file, Tools::getMaxUploadSize(), array('jpg'))) {
                return $error;
            } else {
                $ext = Tools::substr($file['name'], strrpos($file['name'], '.') + 1);
                $file_name = $id.'.'.$ext;
                if ($type== 'default') {
                    $path = $this->module->modulePath.'img/';
                } else {
                    $path = $this->module->modulePath.'img/'.$this->module->imageTypes[$type][0];
                }
                if (!is_dir($path)) {
                    mkdir($path, 0777, true);
                }
                if (!move_uploaded_file($file['tmp_name'], $path.$file_name)) {
                    return Context::getContext()->getTranslator()->trans('Error while uploading image.', array($file['error']), 'Modules.JXBlog.Admin');
                }
                // if auto regeneration option is enabled generate all image's types related to this content type
                if ($this->module->imagesAutoRegenerate && !$this->regenerateTypeImages($type, $id)) {
                    return Context::getContext()->getTranslator()->trans('Error while generating images.', array($file['error']), 'Modules.JXBlog.Admin');
                }
            }
        }

        return false;
    }

    /**
     * Remove the main image and all related images by id and content type
     *
     * @param $id content element
     * @param $type content type (category, category_thumb, post etc.)
     *
     * @return bool
     */
    public function removeImages($id, $type)
    {
        // remove main image if exists
        if (file_exists($this->module->modulePath.'img/'.$this->module->imageTypes[$type][0].$id.'.jpg')) {
            unlink($this->module->modulePath.'img/'.$this->module->imageTypes[$type][0].$id.'.jpg');
        }
        $categoryImages = JXBlogImage::getImageTypesByCategory($type);
        // remove related images if exists
        if ($categoryImages) {
            foreach ($categoryImages as $categoryImage) {
                if (file_exists($this->module->modulePath.'img/'.$this->module->imageTypes[$type][0].$id.'-'.$categoryImage['name'].'.jpg')) {
                    unlink($this->module->modulePath.'img/'.$this->module->imageTypes[$type][0].$id.'-'.$categoryImage['name'].'.jpg');
                }
            }
        }

        return true;
    }

    /**
     * Regenerate type images.
     * If only a content type is defined, regenerate all images(all types related to a content type) for all content's items(all categories/posts etc.).
     * If a content type and an item id are defined, regenerate all images(all types related to a content type) but only for this item (category/post etc.)
     * If all parameters are defined, regenerate only this image type for only this item (category/post etc.)
     * @param string $type type of a content
     * @param bool   $id an item id from content (category/post etc.)
     * @param bool   $image_type an image type (category_small/post_small etc.)
     *
     * @return bool
     */
    public function regenerateTypeImages($type = 'category', $id = false, $image_type = false)
    {
        $result = array();
        // if image type isn't predefined find all related types in database else use the predefined image type
        // check if any image type exists for this kind of data, if not return success
        if (!$imageTypes = JXBlogImage::getImageTypesByCategory($type, $image_type)) {
            return true;
        }

        // select all available items from db to which images can be related to
        $data = $this->getItemsToRegenerate($type, $id);

        if ($data) {
            foreach ($data as $item) {
                if (file_exists($this->module->modulePath.'img/'.$this->module->imageTypes[$type][0].$item['id'].'.jpg')) {
                    $result[$item['id']]['source'] = $this->module->modulePath.'img/'.$this->module->imageTypes[$type][0].$item['id'].'.jpg';
                    $result[$item['id']]['name'] = $item['id'];
                    $result[$item['id']]['path'] = $this->module->modulePath.'img/'.$this->module->imageTypes[$type][0];
                    $result[$item['id']]['file'] = $item['id'].'.jpg';
                }
            }
        }
        // add languages images if it is the regeneration for all categories images
        if (!$id) {
            $result = array_merge($this->getLanguagesImages($this->module->modulePath.'img/'.$this->module->imageTypes[$type][0]), $result);
        }
        if (!$result) {
            return true;
        }
        if (!$this->regenerateImages($imageTypes, $result)) {
            return false;
        }

        return true;
    }

    /**
     * Select all available items from db to which images can be related to, or return id if it is defined
     * @param      $type
     * @param bool $id
     *
     * @return array|false|mysqli_result|null|PDOStatement|resource
     */
    public function getItemsToRegenerate($type, $id = false)
    {
        if (!$id) {
            if ($type == 'category' || $type == 'category_thumb') {
                $data = JXBlogCategory::getAllCategories();
            } elseif ($type == 'post' || $type == 'post_thumb') {
                $data = JXBlogPost::getAllPosts();
            } else {
                $data = array();
            }
        } else {
            $data[]['id'] = $id;
        }

        return $data;
    }

    /**
     * Get default images for each language in case if elements don't have their own images these will be display
     * @param $path
     *
     * @return array
     */
    public function getLanguagesImages($path)
    {
        $result = array();
        foreach ($this->languages as $language) {
            if (file_exists($path.$language['iso_code'].'.jpg')) {
                $result[$language['iso_code']]['source'] = $path.$language['iso_code'].'.jpg';
                $result[$language['iso_code']]['name'] = $language['iso_code'];
                $result[$language['iso_code']]['path'] = $path;
                $result[$language['iso_code']]['file'] = $language['iso_code'].'.jpg';
            }
        }

        return $result;
    }

    /**
     * Regenerate images by types list and images' paths list
     * @param $types list of images' types
     * @param $images list of paths to images
     *
     * @return bool
     */
    public function regenerateImages($types, $images)
    {
        $result = true;
        foreach ($types as $type) {
            foreach ($images as $source) {
                $result &= ImageManager::resize($source['source'], $source['path'].$source['name'].'-'.$type['name'].'.jpg', $type['width'], $type['height'], 'jpg', true);
            }
        }

        return $result;
    }

    /**
     * Remove all images related the to current image type if the type is removing,
     * or remove only that images which are related to the type category
     * if these images no more related to this type
     *
     * @param      $id_jxblog_image id of the image type
     * @param bool $image_category  name of the content type
     *
     * @return bool
     */
    public function removeImageTypeImages($id_jxblog_image, $image_category = false)
    {
        $imageType = new JXBlogImage((int)$id_jxblog_image);
        $data = array();
        if (!$image_category) {
            foreach (array_keys($this->module->imageTypes) as $type) {
                if ($type == 'default' || ($type != 'default' && $imageType->$type)) {
                    $data[$type] = $this->getItemsToRegenerate($type);
                }
            }
        } else {
            $data[$image_category] = $this->getItemsToRegenerate($image_category);
        }
        if (!$data) {
            return true;
        }

        if (!$imagesToRemove = $this->buildImagesListToRemove($data, $imageType->name)) {
            return true;
        }

        return $this->removeImagesByList($imagesToRemove);
    }

    /**
     * Build full list of images which are suitable for removing
     *
     * @param $list list of all content which is related to the image type
     * @param $typeName name of the image type
     *
     * @return array
     */
    public function buildImagesListToRemove($list, $typeName)
    {
        $result = array();
        foreach ($list as $type => $items) {
            // check and add image if it exists for different content
            foreach ($items as $item) {
                if (file_exists($this->module->modulePath.'img/'.$this->module->imageTypes[$type][0].$item['id'].'-'.$typeName.'.jpg')) {
                    $result[] = $this->module->modulePath.'img/'.$this->module->imageTypes[$type][0].$item['id'].'-'.$typeName.'.jpg';
                }
            }
            // check and add image if it exists for different languages
            foreach ($this->languages as $language) {
                if (file_exists($this->module->modulePath.'img/'.$this->module->imageTypes[$type][0].$language['iso_code'].'-'.$typeName.'.jpg')) {
                    $result[] = $this->module->modulePath.'img/'.$this->module->imageTypes[$type][0].$language['iso_code'].'-'.$typeName.'.jpg';
                }
            }
        }

        return $result;
    }

    /**
     * Remove images by paths list
     * @param $list
     *
     * @return bool
     */
    public function removeImagesByList($list)
    {
        if (!is_array($list)) {
            return false;
        }
        $result = true;
        foreach ($list as $item) {
            $result &= unlink($item);
        }

        return $result;
    }

    public static function getImage($type, $id, $imageType = 'default')
    {
        $module = new JXBlog();
        if (!$type || !isset($module->imageTypes[$type])) {
            return false;
        }

        if (!$id) {
            $id = Context::getContext()->language->iso_code;
        }
        $path = $module->modulePath.'img/';
        $lnk = $module->_link.'img/';

        if (file_exists($path.$module->imageTypes[$type][0].$id.'-'.$imageType.'.jpg')) {
            return $lnk.$module->imageTypes[$type][0].$id.'-'.$imageType.'.jpg';
        }

        if (file_exists($path.$module->imageTypes[$type][0].$id.'.jpg')) {
            return $lnk.$module->imageTypes[$type][0].$id.'.jpg';
        }

        if (file_exists($path.$module->imageTypes[$type][0].Context::getContext()->language->iso_code.'-'.$imageType.'.jpg')) {
            return $lnk.$module->imageTypes[$type][0].Context::getContext()->language->iso_code.'-'.$imageType.'.jpg';
        }

        if (file_exists($path.$module->imageTypes[$type][0].$module->defaultLanguage->iso_code.'-'.$imageType.'.jpg')) {
            return $lnk.$module->imageTypes[$type][0].$module->defaultLanguage->iso_code.'-'.$imageType.'.jpg';
        }

        if (file_exists($path.$module->imageTypes[$type][0].Context::getContext()->language->iso_code.'.jpg')) {
            return $lnk.$module->imageTypes[$type][0].Context::getContext()->language->iso_code.'.jpg';
        }

        if (file_exists($path.Context::getContext()->language->iso_code.'.jpg')) {
            return $lnk.Context::getContext()->language->iso_code.'.jpg';
        }

        if (file_exists($path.$module->defaultLanguage->iso_code.'.jpg')) {
            return $lnk.$module->defaultLanguage->iso_code.'.jpg';
        }

        return false;
    }
}
