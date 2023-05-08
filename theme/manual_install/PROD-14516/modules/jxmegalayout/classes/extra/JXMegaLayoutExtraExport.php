<?php
/**
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
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class JXMegaLayoutExtraExport
{
    protected $temporaryPath;
    protected $webPath;
    protected $context;
    protected $defaultLanguage;
    protected $languages;

    public function __construct($path, $webPath)
    {
        $this->context = Context::getContext();
        $this->languages = Language::getLanguages(true);
        $this->defaultLanguage = Language::getLanguage(Configuration::get('PS_LANG_DEFAULT'));
        $this->temporaryPath = $path;
        $this->webPath = $webPath;
    }

    /**
     * Initialization of extra content generation
     *
     * @return string a href to a generated archive
     */
    public function export()
    {
        $extraContent = array();
        // result href is empty by default
        $href = '';
        // check out if a temporary path exists before generation has begun
        // if not then create the path and continue a generation
        // if yes then clear all old files before new ones generation
        if ($this->checkTemporaryPath()) {
            // assemble all extra content information to one array
            $extraContent = $this->collectExtraContent();
        }
        // if any extra content exists then begin to generate json files with all related information
        if ($extraContent) {
            // if files were generated successfully then check if media files exist and copy them
            if ($this->generateExtraContentExportData($extraContent)) {
                $this->exportExtraContentMedia();
            }

            // generate an archive with necessary data and get its path
            $href = $this->prepareArchive();
        }

        return $href;
    }

    /**
     * Check if temporary folder has is already exists
     * if yes clear it if not create it
     *
     * @return string
     */
    protected function checkTemporaryPath()
    {
        $temp_folder = $this->temporaryPath.'export/temp/';

        if (!file_exists($temp_folder)) {
            mkdir($temp_folder, 0777);
        } else {
            Jxmegalayout::cleanFolder($temp_folder);
        }

        return $temp_folder;
    }

    /**
     * Get information about all extra content and collect it into an one archive
     * First level of the archive is a content type(videos, html etc.)
     * Second level is an element id
     * Third level is a default item information or "LANGUAGES" if there are more then one
     * If "LANGUAGES" exists then next level is a language ISO code with an item information related to the language
     *
     * @return array an array that contain all extra content information
     */
    protected function collectExtraContent()
    {
        $collectedExtraContent = array();
        if ($extraHtml = JXMegaLayoutExtraHtml::getList($this->context->language->id)) {
            foreach ($extraHtml as $html) {
                $collectedExtraContent['html'][$html['id_extra_html']]['default'] = JXMegaLayoutExtraHtml::getItem($html['id_extra_html'], $this->defaultLanguage['id_lang']);
                if (count($this->languages) > 1) {
                    foreach ($this->languages as $language) {
                        $collectedExtraContent['html'][$html['id_extra_html']]['languages'][$language['iso_code']] = JXMegaLayoutExtraHtml::getItem($html['id_extra_html'], $language['id_lang']);
                    }
                }
            }
        }
        if ($extraBanners = JXMegaLayoutExtraBanner::getList($this->context->language->id)) {
            foreach ($extraBanners as $banner) {
                $collectedExtraContent['banners'][$banner['id_extra_banner']]['default'] = JXMegaLayoutExtraBanner::getItem($banner['id_extra_banner'], $this->defaultLanguage['id_lang']);
                if (count($this->languages) > 1) {
                    foreach ($this->languages as $language) {
                        $collectedExtraContent['banners'][$banner['id_extra_banner']]['languages'][$language['iso_code']] = JXMegaLayoutExtraBanner::getItem($banner['id_extra_banner'], $language['id_lang']);
                    }
                }
            }
        }
        if ($extraVideos = JXMegaLayoutExtraVideo::getList($this->context->language->id)) {
            foreach ($extraVideos as $video) {
                $collectedExtraContent['videos'][$video['id_extra_video']]['default'] = JXMegaLayoutExtraVideo::getItem($video['id_extra_video'], $this->defaultLanguage['id_lang']);
                if (count($this->languages) > 1) {
                    foreach ($this->languages as $language) {
                        $collectedExtraContent['videos'][$video['id_extra_video']]['languages'][$language['iso_code']] = JXMegaLayoutExtraVideo::getItem($video['id_extra_video'], $language['id_lang']);
                    }
                }
            }
        }
        if ($extraSliders = JXMegaLayoutExtraSlider::getList($this->context->language->id)) {
            foreach ($extraSliders as $slider) {
                $collectedExtraContent['sliders'][$slider['id_extra_slider']]['default'] = JXMegaLayoutExtraSlider::getItem($slider['id_extra_slider'], $this->defaultLanguage['id_lang']);
                if (count($this->languages) > 1) {
                    foreach ($this->languages as $language) {
                        $collectedExtraContent['sliders'][$slider['id_extra_slider']]['languages'][$language['iso_code']] = JXMegaLayoutExtraSlider::getItem($slider['id_extra_slider'], $language['id_lang']);
                    }
                }
                if ($slides = JXMegaLayoutExtraSlider::getSliderSlides($slider['id_extra_slider'])) {
                    $collectedExtraContent['sliders'][$slider['id_extra_slider']]['slides'] = $slides;
                }
            }
        }

        return $collectedExtraContent;
    }

    /**
     * Create and fill extra content data files based on extra content information
     *
     * @param array $extraContentArray
     *
     * @return bool
     */
    protected function generateExtraContentExportData(array $extraContentArray)
    {
        $path = $this->temporaryPath.'export/temp/';

        foreach ($extraContentArray as $key => $item) {
            $file = fopen($path.$key.'.json', 'w');
            fwrite($file, json_encode($item, JSON_UNESCAPED_UNICODE)); // JSON_UNESCAPED_UNICODE is used to avoid encoding issues
            fclose($file);
        }

        return true;
    }

    /**
     * Copy extra content media files to a source archive
     *
     * @return bool
     */
    protected function exportExtraContentMedia()
    {
        $mediaPath = $this->temporaryPath.'extracontent/';
        $tempPath = $this->temporaryPath.'export/temp/media/';

        if (!file_exists($tempPath)) {
            mkdir($tempPath, 0777);
        }

        $media = array_merge(
            Tools::scandir($mediaPath, 'jpg'),
            Tools::scandir($mediaPath, 'png'),
            Tools::scandir($mediaPath, 'gif'),
            Tools::scandir($mediaPath, 'jpeg')
        );
        if ($media) {
            foreach ($media as $item) {
                copy($mediaPath.$item, $tempPath.$item);
            }
        }

        return true;
    }

    /**
     * Create an archive internal structure
     *
     * @param        $path
     * @param        $ZipArchiveObj
     * @param string $zip_path
     */
    protected function archiveFolders($path, $ZipArchiveObj, $zip_path = '')
    {
        $files = scandir($path);

        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                if (is_dir($path . $file)) {
                    $ZipArchiveObj->addEmptyDir($zip_path . $file);
                    $new_zip_path = $zip_path . $file . '/';
                    $new_path = $path . $file . '/';
                    $this->archiveFolders($new_path, $ZipArchiveObj, $new_zip_path);
                } else {
                    $ZipArchiveObj->addFile($path . $file, $zip_path . $file);
                }
            }
        }
    }

    /**
     * Make export archive
     *
     * @return string path to export archive
     */
    protected function prepareArchive()
    {
        $fileName = 'extracontent.zip';
        $path = $this->temporaryPath.'export/';
        if (file_exists($path.$fileName)) {
            unlink($path.$fileName);
        }

        $zip = new ZipArchive();

        if ($zip->open($path.$fileName, ZipArchive::OVERWRITE | ZipArchive::CREATE) !== true) {
            $this->errors = $this->displayError(sprintf($this->l('cannot open %s'), $fileName));
        }

        $this->archiveFolders($path.'temp/', $zip);

        $zip->close();

        return $this->webPath.'export/'.$fileName;
    }
}
