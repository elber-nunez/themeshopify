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

class JXMegaLayoutExtraImport
{
    protected $context;
    protected $temporaryPath;

    public function __construct($path)
    {
        $this->context = Context::getContext();
        $this->temporaryPath = $path;
    }

    /**
     * Init extra content import after a valid archive was uploaded
     */
    public function import()
    {
        // assure that an archive is still in a correct path
        if (!$this->checkExtraContentArchive()) {
            return die(Tools::jsonEncode(array('status' => false, 'error' => 'Fail to find an archive!')));
        }
        // extract an archive into a temporary path and remove it after extraction
        if (!$this->extractExtraContentArchive()) {
            return die(Tools::jsonEncode(array('status' => false, 'error' => 'Fail to extract an archive! Be sure that an archive is valid and contain allowed data.')));
        }
        // clear all old extra content data to avoid a confusion
        if (!$this->clearOldExtraContentData()) {
            return die(Tools::jsonEncode(array('status' => false, 'error' => 'Fail to remove old extra content data!')));
        }
        // fill extra content with new data
        if (!$this->populateNewExtraContentData()) {
            return die(Tools::jsonEncode(array('status' => false, 'error' => 'Fail to populate new extra content data!')));
        }
        // import all related extra content media files
        if (!$this->importMediaFiles()) {
            return die(Tools::jsonEncode(array('status' => false, 'error' => 'Fail to import media files!')));
        }

        return die(Tools::jsonEncode(array('status' => true)));
    }

    /**
     * Check if an archive with extra content data exists
     *
     * @return bool
     */
    protected function checkExtraContentArchive()
    {
        if (!file_exists($this->temporaryPath.'import/extracontent.zip')) {
            return false;
        }

        return true;
    }

    /**
     * Extract an archive in a temporary path
     *
     * @return bool
     */
    protected function extractExtraContentArchive()
    {
        $file = $this->temporaryPath.'import/extracontent.zip';
        $zip = new ZipArchive;
        if ($zip->open($file) === true) {
            if (!file_exists($this->temporaryPath.'import/temp/')) {
                mkdir($this->temporaryPath.'import/temp/', 0777);
            }
            $zip->extractTo($this->temporaryPath.'import/temp/');
            $zip->close();
            unlink($file);

            return true;
        }

        return false;
    }

    /**
     * Delete all old extra content information
     *
     * @return bool
     */
    protected function clearOldExtraContentData()
    {
        $result = $this->clearExtraHtml();
        $result &= $this->clearExtraBanners();
        $result &= $this->clearExtraVideos();
        $result &= $this->clearExtraSliders();

        return $result;
    }

    private function clearExtraHtml()
    {
        $result = true;
        $html = JXMegaLayoutExtraHtml::getList($this->context->language->id);
        if ($html) {
            foreach ($html as $item) {
                $extraHtml = new JXMegaLayoutExtraHtml($item['id_extra_html']);
                $result &= $extraHtml->delete();
            }
        }

        return $result;
    }

    private function clearExtraBanners()
    {
        $result = true;
        $banners = JXMegaLayoutExtraBanner::getList($this->context->language->id);
        if ($banners) {
            foreach ($banners as $banner) {
                $extraBanner = new JXMegaLayoutExtraBanner($banner['id_extra_banner']);
                $result &= $extraBanner->delete();
            }
        }

        return $result;
    }

    private function clearExtraVideos()
    {
        $result = true;
        $videos = JXMegaLayoutExtraVideo::getList($this->context->language->id);
        if ($videos) {
            foreach ($videos as $video) {
                $extraVideo = new JXMegaLayoutExtraVideo($video['id_extra_video']);
                $result &= $extraVideo->delete();
            }
        }

        return $result;
    }

    private function clearExtraSliders()
    {
        $result = true;
        $sliders = JXMegaLayoutExtraSlider::getList($this->context->language->id);
        if ($sliders) {
            foreach ($sliders as $slider) {
                $extraSlider = new JXMegaLayoutExtraSlider($slider['id_extra_slider']);
                $result &= $extraSlider->delete();
            }
        }

        return $result;
    }

    /**
     * Populate new extra content data
     * @return bool
     */
    private function populateNewExtraContentData()
    {
        $result = true;
        $result &= $this->populateExtraContentHTML();
        $result &= $this->populateExtraContentBanners();
        $result &= $this->populateExtraContentVideos();
        $result &= $this->populateExtraContentSliders();

        return $result;
    }

    private function populateExtraContentHTML()
    {
        $htmlPath = $this->temporaryPath.'import/temp/html.json';
        if (!file_exists($htmlPath)) {
            return true;
        }

        $htmlData = Tools::jsonDecode(Tools::file_get_contents($htmlPath), true);
        if ($htmlData) {
            foreach ($htmlData as $id => $html) {
                $extraHtml = new JXMegaLayoutExtraHtml();
                $extraHtml->id = $id;
                $extraHtml->force_id = true;
                $extraHtml->specific_class = $html['default']['specific_class'];
                foreach (Language::getLanguages(false) as $language) {
                    if (isset($html['languages'][$language['iso_code']])) {
                        $extraHtml->name[$language['id_lang']] = $html['languages'][$language['iso_code']]['name'];
                        $extraHtml->content[$language['id_lang']] = $html['languages'][$language['iso_code']]['content'];
                    } else {
                        $extraHtml->name[$language['id_lang']] = $html['default']['name'];
                        $extraHtml->content[$language['id_lang']] = $html['default']['content'];
                    }
                }
                $extraHtml->add();
            }
        }

        return true;
    }

    private function populateExtraContentBanners()
    {
        $bannersPath = $this->temporaryPath.'import/temp/banners.json';
        if (!file_exists($bannersPath)) {
            return true;
        }

        $bannersData = Tools::jsonDecode(Tools::file_get_contents($bannersPath), true);

        if ($bannersData) {
            foreach ($bannersData as $id => $banner) {
                $extraBanner = new JXMegaLayoutExtraBanner();
                $extraBanner->id = $id;
                $extraBanner->force_id = true;
                $extraBanner->specific_class = $banner['default']['specific_class'];
                foreach (Language::getLanguages(false) as $language) {
                    if (isset($banner['languages'][$language['iso_code']])) {
                        $extraBanner->name[$language['id_lang']] = $banner['languages'][$language['iso_code']]['name'];
                        $extraBanner->img[$language['id_lang']] = $banner['languages'][$language['iso_code']]['img'];
                        $extraBanner->link[$language['id_lang']] = $banner['languages'][$language['iso_code']]['link'];
                        $extraBanner->content[$language['id_lang']] = $banner['languages'][$language['iso_code']]['content'];
                    } else {
                        $extraBanner->name[$language['id_lang']] = $banner['default']['name'];
                        $extraBanner->img[$language['id_lang']] = $banner['default']['img'];
                        $extraBanner->link[$language['id_lang']] = $banner['default']['link'];
                        $extraBanner->content[$language['id_lang']] = $banner['default']['content'];
                    }
                }
                $extraBanner->add();
            }
        }

        return true;
    }

    private function populateExtraContentVideos()
    {
        $videosPath = $this->temporaryPath.'import/temp/videos.json';
        if (!file_exists($videosPath)) {
            return true;
        }

        $videosData = Tools::jsonDecode(Tools::file_get_contents($videosPath), true);

        if ($videosData) {
            foreach ($videosData as $id => $video) {
                $extraVideo = new JXMegaLayoutExtraVideo();
                $extraVideo->id = $id;
                $extraVideo->force_id = true;
                $extraVideo->specific_class = $video['default']['specific_class'];
                foreach (Language::getLanguages(false) as $language) {
                    if (isset($video['languages'][$language['iso_code']])) {
                        $extraVideo->name[$language['id_lang']] = $video['languages'][$language['iso_code']]['name'];
                        $extraVideo->url[$language['id_lang']] = $video['languages'][$language['iso_code']]['url'];
                        $extraVideo->content[$language['id_lang']] = $video['languages'][$language['iso_code']]['content'];
                    } else {
                        $extraVideo->name[$language['id_lang']] = $video['default']['name'];
                        $extraVideo->url[$language['id_lang']] = $video['default']['url'];
                        $extraVideo->content[$language['id_lang']] = $video['default']['content'];
                    }
                }
                $extraVideo->add();
            }
        }

        return true;
    }

    private function populateExtraContentSliders()
    {
        $slidersPath = $this->temporaryPath.'import/temp/sliders.json';
        if (!file_exists($slidersPath)) {
            return true;
        }

        $slidersData = Tools::jsonDecode(Tools::file_get_contents($slidersPath), true);

        if ($slidersData) {
            foreach ($slidersData as $id => $slider) {
                $extraSlider = new JXMegaLayoutExtraSlider();
                $extraSlider->id = $id;
                $extraSlider->force_id = true;
                $extraSlider->specific_class = $slider['default']['specific_class'];
                $extraSlider->visible_items = $slider['default']['visible_items'];
                $extraSlider->items_scroll = $slider['default']['items_scroll'];
                $extraSlider->margin = $slider['default']['margin'];
                $extraSlider->speed = $slider['default']['speed'];
                $extraSlider->auto_scroll = $slider['default']['auto_scroll'];
                $extraSlider->pause = $slider['default']['pause'];
                $extraSlider->loop = $slider['default']['loop'];
                $extraSlider->pager = $slider['default']['pager'];
                $extraSlider->controls = $slider['default']['controls'];
                $extraSlider->auto_height = $slider['default']['auto_height'];
                foreach (Language::getLanguages(false) as $language) {
                    if (isset($slider['languages'][$language['iso_code']])) {
                        $extraSlider->name[$language['id_lang']] = $slider['languages'][$language['iso_code']]['name'];
                        $extraSlider->content[$language['id_lang']] = $slider['languages'][$language['iso_code']]['content'];
                    } else {
                        $extraSlider->name[$language['id_lang']] = $slider['default']['name'];
                        $extraSlider->content[$language['id_lang']] = $slider['default']['content'];
                    }
                }
                if (isset($slider['slides']) && count($slider['slides'])) {
                    foreach ($slider['slides'] as $slide) {
                        $extraSlider->addImportedSliderSlide($slide['id_item'], $slide['id_extra_slider'], $slide['type'], $slide['id_content'], $slide['position']);
                    }
                }
                $extraSlider->add();
            }
        }

        return true;
    }

    /**
     * Copy extra content media files
     *
     * @return bool
     */
    private function importMediaFiles()
    {
        $mediaFilesSourcePath = $this->temporaryPath.'import/temp/media/';
        $mediaFilesPath = $this->temporaryPath.'extracontent/';

        if (!file_exists($mediaFilesPath)) {
            mkdir($mediaFilesPath, 0777);
        }

        $mediaFiles = array_merge(
            Tools::scandir($mediaFilesSourcePath, 'jpg'),
            Tools::scandir($mediaFilesSourcePath, 'png'),
            Tools::scandir($mediaFilesSourcePath, 'gif'),
            Tools::scandir($mediaFilesSourcePath, 'jpeg')
        );

        if ($mediaFiles) {
            foreach ($mediaFiles as $file) {
                copy($mediaFilesSourcePath.$file, $mediaFilesPath.$file);
            }
        }

        return true;
    }
}
