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

use PrestaShop\PrestaShop\Core\Addon\Theme\ThemeManager;
use Symfony\Component\Yaml\Yaml;
use PrestaShop\PrestaShop\Core\Addon\Theme\ThemeManagerBuilder;
use PrestaShop\PrestaShop\Core\Addon\Theme\ThemeRepository;
use Symfony\Component\HttpFoundation\Request as BuilderRequest;

class JXMegalayoutThemeBuilder
{
    protected $parent_theme;
    protected $parent_theme_library;
    protected $theme_manager;
    protected $theme_repository;
    private $api_url = 'http://prestashop7.devoffice.com/meilleur/themebuilder_api/';

    public function __construct()
    {
        $this->theme_manager = (new ThemeManagerBuilder(Context::getContext(), Db::getInstance()))->build();
        $this->theme_repository = (new ThemeManagerBuilder(Context::getContext(), Db::getInstance()))->buildRepository();
    }

    public function setParentTheme($name)
    {
        $this->parent_theme = $this->theme_repository->getInstanceByName($name);

        return $this;
    }

    /**
     * Get all existed themes names
     *
     * @return array
     */
    public function getAllThemesNames()
    {
        $themes = array();
        if ($allThemes = $this->theme_repository->getList()) {
            foreach ($allThemes as $name => $theme) {
                $themes[] = $name;
            }
        }

        return $themes;
    }

    /**
     * Get a list of the themes which are compatible with a theme builder
     * To be a compatible theme it has to contain library folder theme_name/templates/library/
     * and a info.yml file inside the latter
     *
     * @return array of themes names
     */
    public function getCompatibleParentThemes()
    {
        $compatibleThemes = array();
        if ($allThemes = $this->theme_repository->getList()) {
            foreach ($allThemes as $name => $theme) {
                if (!$theme->get('parent') && $this->hasLayoutsLibrary($theme)) {
                    $compatibleThemes[] = array('name' => $name, 'image' => _PS_BASE_URL_ . __PS_BASE_URI__ . $theme->get('preview'));
                }
            }
        }

        return $compatibleThemes;
    }

    /**
     * Check if a theme has a library and info.yml file
     *
     * @param $theme object
     *
     * @return bool
     */
    private function hasLayoutsLibrary($theme)
    {
        if (file_exists($theme->getDirectory().'templates/library/') && file_exists(
                $theme->getDirectory().'templates/library/info.yml'
            )
        ) {
            return true;
        }

        return false;
    }

    /**
     * Get all compatible children themes which were produced from this one
     *
     * @return array
     */
    public function getChildren()
    {
        $children = array();
        if ($allThemes = $this->theme_repository->getList()) {
            foreach ($allThemes as $themeName => $theme) {
                if ($theme->get('parent') == $this->parent_theme->getName() && $this->checkChildCompatibility($theme)) {
                    $children[] = array('name' => $themeName, 'image' => _PS_BASE_URL_ . __PS_BASE_URI__ . $theme->get('preview'));
                }
            }
        }

        return $children;
    }

    /**
     * Get a library info for a theme by its name
     *
     * @return mixed
     */
    public function loadParentThemeLibrary()
    {
        $this->parent_theme_library = Yaml::parse(file_get_contents($this->parent_theme->getDirectory().'templates/library/info.yml'));

        return $this->parent_theme_library;
    }

    /**
     * Get layouts previews
     *
     * @return array
     */
    public function loadParentThemeLibraryPreviews()
    {
        $previewList = array();

        if ($this->parent_theme_library && isset($this->parent_theme_library['pages_list'])) {
            foreach ($this->parent_theme_library['pages_list'] as $type=>$data) {
                if (isset($data['layouts']) && $data['layouts']) {
                    foreach ($data['layouts'] as $name => $layout) {
                        if (file_exists($this->parent_theme->getDirectory().'templates/library/preview/'.$type.'/'.$name.'.png')) {
                            $previewList[$type][$name] = __PS_BASE_URI__.'/themes/'.$this->parent_theme->get('name').'/templates/library/preview/'.$type.'/'.$name.'.png';
                        } else {
                            $previewList[$type][$name] = false;
                        }
                    }
                }
            }
        }

        return $previewList;
    }

    /**
     * Check if a child theme is a compatible with a current parent theme
     *
     * @param $theme object
     *
     * @return bool
     */
    private function checkChildCompatibility($theme)
    {
        return file_exists($theme->getDirectory().'config/themeChildInfo.yml');
    }

    /**
     * Build/rebuild child theme according to selected parameters
     *
     * @param       $parentTheme name of a parent theme
     * @param       $name        a name which will be used for child theme
     * @param       $publicName  a public name which will be used for current theme in an admin panel
     * @param array $layouts     a list of selected layouts which will be applied to a child theme
     *
     * @return int a code of an error if it occurred
     */
    public function buildChildTheme($parentTheme, $name, $publicName, array $layouts)
    {
        $theme = $this->theme_repository->getInstanceByName($parentTheme);
        $themeDir = $theme->getDirectory();
        $libraryDirectory = $themeDir.'templates/library/';
        if (!$newPath = $this->copyFilesFromLibrary($name, $libraryDirectory, $layouts)) {
            return 1;
        }
        if (!$this->createThemeSettingsFiles($themeDir, $newPath, $name, $publicName)) {
            return 2;
        }
        if (!copy($themeDir.'preview.png', $newPath.'preview.png')) {
            return 3;
        }
        if (!$this->writeSettingsFile($newPath.'config/', 'themeChildInfo.yml', array('name' => $name, 'display_name' => $publicName, 'layouts' => $layouts))) {
            return 4;
        }

        return 0;
    }

    /**
     * Copy all layout's files which are necessary for the child theme
     *
     * @param       $name             name of a new theme
     * @param       $libraryDirectory a path to the library inside a parent theme
     * @param array $layouts          a list of layouts
     *
     * @return string
     */
    private function copyFilesFromLibrary($name, $libraryDirectory, array $layouts)
    {
        $newThemeDirectory = _PS_ALL_THEMES_DIR_.$name.'/';
        if (is_dir($newThemeDirectory)) {
            $this->deleteDir($newThemeDirectory);
        }
        mkdir($newThemeDirectory, 0777, true);
        foreach ($layouts as $type => $layout) {
            if (is_dir($libraryDirectory.'/'.$type.'/'.$layout['name'].'/')) {
                $this->recurseCopy($libraryDirectory.'/'.$type.'/'.$layout['name'].'/', $newThemeDirectory);
            }
        }

        return $newThemeDirectory;
    }

    /**
     * Remove a directory and everything inside it
     *
     * @param $dirPath
     */
    private function deleteDir($dirPath)
    {
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath.'*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                $this->deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }

    /**
     * Copy folder with containing files
     *
     * @param $src copy from
     * @param $dst copy to
     */
    private function recurseCopy($src, $dst)
    {
        $dir = opendir($src);
        if (!is_dir($dst)) {
            mkdir($dst, 0777, true);
        }
        while (false !== ($file = readdir($dir))) {
            if ($file != '.' && $file != '..') {
                if (is_dir($src.'/'.$file)) {
                    $this->recurseCopy($src.'/'.$file, $dst.'/'.$file);
                } else {
                    copy($src.'/'.$file, $dst.'/'.$file);
                }
            }
        }
        closedir($dir);
    }

    /**
     * Create a file with mandatory settings for a child theme usage
     *
     * @param $themeDir   a path to a parent theme
     * @param $newPath    a path a path to a new child theme
     * @param $name       a name of the new child theme
     * @param $publicName a public name of the new child theme
     *
     * @return int
     */
    private function createThemeSettingsFiles($themeDir, $newPath, $name, $publicName)
    {
        $childThemeSettings = $parentThemeSettings = Yaml::parse(file_get_contents($themeDir.'config/theme.yml'));
        $childThemeSettings['parent'] = $parentThemeSettings['name'];
        if (!$parentThemeSettings['assets']) {
            unset($childThemeSettings['assets']);
        }
        $childThemeSettings['name'] = $name;
        $childThemeSettings['display_name'] = $publicName;

        return $this->writeSettingsFile($newPath.'config/', 'theme.yml', $childThemeSettings);
    }

    /**
     * Write an information to a yml file
     *
     * @param $path to a new file
     * @param $name a name of the new file
     * @param $data an information
     *
     * @return int
     */
    private function writeSettingsFile($path, $name, $data)
    {
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        if (!file_exists($path.$name)) {
            $fp = fopen($path.$name, 'w');
            fclose($fp);
        }

        return file_put_contents($path.$name, Yaml::dump($data, 5, 2));
    }

    /**
     * Load information about child theme to get know which layouts it uses and other information
     *
     * @param $name
     *
     * @return mixed
     */
    public function loadChildThemeInfo($name)
    {
        $theme = $this->theme_repository->getInstanceByName($name);
        $infoPath = $theme->getDirectory().'config/themeChildInfo.yml';

        return Yaml::parse(file_get_contents($infoPath));
    }

    /**
     * Init theme remove
     *
     * @param $name
     *
     * @return int
     */
    public function remove($name)
    {
        // check if a theme isn't used
        if ($this->checkIfThemeIsUsed($name)) {
            return 1;
        }

        if (!$this->theme_manager->uninstall($name)) {
            return 2;
        }

        return 0;
    }

    /**
     * Check if any of stores use the theme
     *
     * @param $name
     *
     * @return bool
     */
    private function checkIfThemeIsUsed($name)
    {
        $shops = Shop::getShops(false);
        $used = false;
        foreach ($shops as $shop) {
            if ($shop['theme_name'] == $name) {
                $used = true;
            }
        }

        return $used;
    }

    /**
     * Check if this theme has a new version on the repository
     *
     * @return bool
     */
    public function checkParentThemeUpdates()
    {
        $latestVersion = $this->getLatestThemeVersion($this->parent_theme->getName(), $this->parent_theme->get('secret_hash'));
        if ($latestVersion && Tools::version_compare($this->parent_theme->get('version'), $latestVersion, '<')) {
            return true;
        }

        return false;
    }

    /**
     * Send a query to an API in order to check if a local theme has latest version
     *
     * @param $themeName
     * @param $hash secret key used to avoid fraudulent theme uploading
     *
     * @return mixed
     */
    private function getLatestThemeVersion($themeName, $hash)
    {
        return $this->getAPIData($this->api_url, array('action' => 'getLatestVersion', 'theme_name' => $themeName, 'secret_hash' => $hash));
    }

    /**
     * Check if a theme library has a new version
     *
     * @return bool
     */
    public function checkParentThemeLibraryUpdates()
    {
        $latestLibraryVersion = $this->getLatestThemeLibraryVersion($this->parent_theme->getName(), $this->parent_theme->get('secret_hash'));
        $libraryInfo = $this->loadParentThemeLibrary();
        if ($latestLibraryVersion && Tools::version_compare($libraryInfo['version'], $latestLibraryVersion, '<')) {
            return true;
        }

        return false;
    }

    private function getLatestThemeLibraryVersion($themeName, $hash)
    {
        return $this->getAPIData($this->api_url, array('action' => 'getLibraryLatestVersion', 'theme_name' => $themeName, 'secret_hash' => $hash));
    }

    /**
     * Send request to API
     *
     * @param $url
     * @param $data
     *
     * @return mixed
     */
    private function getAPIData($url, $data)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    /**
     * Upload latest files and update parent theme
     *
     * @return bool
     */
    public function updateParentTheme()
    {
        $themeUpdatesPath = $this->getParentThemeLatestArchivePath();
        if (file_get_contents($themeUpdatesPath)) {
            $jxmegalayout = new Jxmegalayout();
            $tmpPath = $jxmegalayout->localPath().'tmp/';
            if (!is_dir($tmpPath)) {
                mkdir($tmpPath, 0777);
            }
            $themeName = $this->parent_theme->getName();
            copy($themeUpdatesPath, $tmpPath.$themeName.'.zip');
            if (file_exists($tmpPath.$themeName.'.zip')) {
                $this->extractArchive($tmpPath.$themeName.'.zip');
            }
            if (is_dir($tmpPath)) {
                $this->deleteDir($tmpPath);
            }
            return $this->theme_manager->reset($themeName);
        }

        return false;
    }

    /**
     * Upload latest files and update parent theme library
     *
     * @return bool
     */
    public function updateParentThemeLibrary()
    {
        $themeLibraryUpdatesPath = $this->getParentThemeLibraryLatestArchivePath();
        if (file_get_contents($themeLibraryUpdatesPath)) {
            $jxmegalayout = new Jxmegalayout();
            $tmpPath = $jxmegalayout->localPath().'tmp/';
            if (is_dir($tmpPath)) {
                $this->deleteDir($tmpPath);
            }
            mkdir($tmpPath, 0777);

            copy($themeLibraryUpdatesPath, $tmpPath.'library.zip');
            if (file_exists($tmpPath.'library.zip')) {
                $this->extractArchive($tmpPath.'library.zip', true);
            }
            if (is_dir($tmpPath)) {
                $this->deleteDir($tmpPath);
            }

            return true;
        }

        return false;
    }

    /**
     * Extract archive with all files overriding
     *
     * @param      $file
     * @param bool $library
     *
     * @return bool
     */
    private function extractArchive($file, $library = false)
    {
        $destination = $this->parent_theme->getDirectory();
        if ($library) {
            $destination = $destination.'templates/library/';
        }
        $zip = new ZipArchive();
        if ($zip->open($file) === true) {
            $zip->extractTo($destination);
            $zip->close();

            return true;
        }

        return false;
    }

    /**
     * Get path to an archive
     *
     * @return mixed
     */
    private function getParentThemeLatestArchivePath()
    {
        return $this->getAPIData($this->api_url, array('action' => 'getLatestVersionArchive', 'theme_name' => $this->parent_theme->getName()));
    }
    
    /**
     * Get path to an archive
     *
     * @return mixed
     */
    private function getParentThemeLibraryLatestArchivePath()
    {
        return $this->getAPIData($this->api_url, array('action' => 'getLatestLibraryVersionArchive', 'theme_name' => $this->parent_theme->getName()));
    }
}
