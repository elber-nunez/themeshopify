<?php
/**
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
*/

set_time_limit(0);

if (!defined('_PS_CORE_IMG_DIR_')) {
	define('_PS_CORE_IMG_DIR_', _PS_IMG_DIR_);
}
require_once(dirname(__FILE__).'/../../classes/SampleDataInstallHelper.php');

class AdminSampleDataInstallExportController extends ModuleAdminController {

	public $className = 'SampleDataInstall';
	protected $use_lang = '';
	public $frendly_url = 0;
	public $csv_delimiter = ';';
    public $helper = false;
    public $method_list;

	public function __construct()
	{
		$this->bootstrap = true;

		//$this->meta_title = $this->trans('Export Data', array(), 'Modules.SampleDataInstall.Admin');
		$this->meta_title = 'Export Data';

		parent::__construct();

		if (!$this->module->active) {
			Tools::redirectAdmin($this->context->link->getAdminLink('AdminHome'));
		}

        $this->helper = new SampleDataInstallHelper();
		require_once(dirname(__FILE__).'/../../helper/tables_list.php');
		require_once(dirname(__FILE__).'/../../helper/configuration_exceptions.php');
		require_once(dirname(__FILE__).'/../../helper/fields_list.php');
        $this->method_list = [
            'clearOutputDirectory' => [
                'success_message' => $this->l('Success'),
                'error_message' => $this->l('Error'),
                'description' => $this->l('Clearing output directory')
            ],
            'checkFriendlyUrl' => [
                'success_message' => $this->l('Success'),
                'error_message' => $this->l('Error'),
                'description' => $this->l('Setting up necessary settings')
            ],
            'generateCategoryData' => [
                'success_message' => $this->l('Success'),
                'error_message' => $this->l('Categories data generating failed'),
                'description' => $this->l('Generating products categories data')
            ],
            'generateManufacturerData' => [
                'success_message' => $this->l('Success'),
                'error_message' => $this->l('Error'),
                'description' => $this->l('Generating manufacturers data')
            ],
            'generateSupplierData' => [
                'success_message' => $this->l('Success'),
                'error_message' => $this->l('Error'),
                'description' => $this->l('Generating supplier data')
            ],
            'generateAttributeGroupData' => [
                'success_message' => $this->l('Success'),
                'error_message' => $this->l('Error'),
                'description' => $this->l('Generating attributes groups data')
            ],
            'generateAttributeData' => [
                'success_message' => $this->l('Success'),
                'error_message' => $this->l('Error'),
                'description' => $this->l('Generating attributes data')
            ],
            'generateAttachmentData' => [
                'success_message' => $this->l('Success'),
                'error_message' => $this->l('Error'),
                'description' => $this->l('Generating attachments data')
            ],
            'generateFeaturesData' => [
                'success_message' => $this->l('Success'),
                'error_message' => $this->l('Error'),
                'description' => $this->l('Generating features data')
            ],
            'generateFeatureValuesData' => [
                'success_message' => $this->l('Success'),
                'error_message' => $this->l('Error'),
                'description' => $this->l('Generating features values data')
            ],
            'generateProductsData' => [
                'success_message' => $this->l('Success'),
                'error_message' => $this->l('Error'),
                'description' => $this->l('Generating products data')
            ],
            'generateProductAttributesData' => [
                'success_message' => $this->l('Success'),
                'error_message' => $this->l('Error'),
                'description' => $this->l('Generating products attributes data')
            ],
            'generateProductPackData' => [
                'success_message' => $this->l('Success'),
                'error_message' => $this->l('Error'),
                'description' => $this->l('Generating products packs data')
            ],
            'generateStockAvailableData' => [
                'success_message' => $this->l('Success'),
                'error_message' => $this->l('Error'),
                'description' => $this->l('Generating stock availability data')
            ],
            'generateImagesData' => [
                'success_message' => $this->l('Success'),
                'error_message' => $this->l('Error'),
                'description' => $this->l('Generating images data')
            ],
            'generateCustomerData' => [
                'success_message' => $this->l('Success'),
                'error_message' => $this->l('Error'),
                'description' => $this->l('Generating customers information data')
            ],
            'generateAddressData' => [
                'success_message' => $this->l('Success'),
                'error_message' => $this->l('Error'),
                'description' => $this->l('Generating addresses data')
            ],
            'generateAliasData' => [
                'success_message' => $this->l('Success'),
                'error_message' => $this->l('Error'),
                'description' => $this->l('Generating stock alias data')
            ],
            'generateCMSCategoryData' => [
                'success_message' => $this->l('Success'),
                'error_message' => $this->l('Error'),
                'description' => $this->l('Generating CMS categories data')
            ],
            'generateCMSPageData' => [
                'success_message' => $this->l('Success'),
                'error_message' => $this->l('Error'),
                'description' => $this->l('Generating CMS pages data')
            ],
            'generateLanguageData' => [
                'success_message' => $this->l('Success'),
                'error_message' => $this->l('Error'),
                'description' => $this->l('Generating store languages data')
            ],
            'generateCurrencyData' => [
                'success_message' => $this->l('Success'),
                'error_message' => $this->l('Error'),
                'description' => $this->l('Generating store currency data')
            ],
            'generateCarrierData' => [
                'success_message' => $this->l('Success'),
                'error_message' => $this->l('Error'),
                'description' => $this->l('Generating store carriers data')
            ],
            'generateCartRuleData' => [
                'success_message' => $this->l('Success'),
                'error_message' => $this->l('Error'),
                'description' => $this->l('Generating cart rules data')
            ],
            'generateContactData' => [
                'success_message' => $this->l('Success'),
                'error_message' => $this->l('Error'),
                'description' => $this->l('Generating contacts data')
            ],
            'generateZoneData' => [
                'success_message' => $this->l('Success'),
                'error_message' => $this->l('Error'),
                'description' => $this->l('Generating store zones data')
            ],
            'generateCountryData' => [
                'success_message' => $this->l('Success'),
                'error_message' => $this->l('Error'),
                'description' => $this->l('Generating store countries data')
            ],
            'generateStateData' => [
                'success_message' => $this->l('Success'),
                'error_message' => $this->l('Error'),
                'description' => $this->l('Generating store states data')
            ],
            'generateDeliveryData' => [
                'success_message' => $this->l('Success'),
                'error_message' => $this->l('Error'),
                'description' => $this->l('Generating store delivery data')
            ],
            'generateSpecificPriceData' => [
                'success_message' => $this->l('Success'),
                'error_message' => $this->l('Error'),
                'description' => $this->l('Generating specific prices data')
            ],
            'generateSpecificPriceRuleData' => [
                'success_message' => $this->l('Success'),
                'error_message' => $this->l('Error'),
                'description' => $this->l('Generating specific prices rule data')
            ],
            'generateTaxData' => [
                'success_message' => $this->l('Success'),
                'error_message' => $this->l('Error'),
                'description' => $this->l('Generating store taxes data')
            ],
            'generateTaxRuleData' => [
                'success_message' => $this->l('Success'),
                'error_message' => $this->l('Error'),
                'description' => $this->l('Generating store taxes rules data')
            ],
            'generateTaxRuleGroupData' => [
                'success_message' => $this->l('Success'),
                'error_message' => $this->l('Error'),
                'description' => $this->l('Generating store tax rules groups data')
            ],
            'generateCustomtextData' => [
                'success_message' => $this->l('Success'),
                'error_message' => $this->l('Error'),
                'description' => $this->l('Generating "Customtext" data')
            ],
            'generateConfigurationData' => [
                'success_message' => $this->l('Success'),
                'error_message' => $this->l('Error'),
                'description' => $this->l('Generating configurations')
            ],
            'getCustomTablesSQL' => [
                'success_message' => $this->l('Success'),
                'error_message' => $this->l('Error'),
                'description' => $this->l('Generating custom sql data')
            ],
            'getAdditionalImages' => [
                'success_message' => $this->l('Success'),
                'error_message' => $this->l('Error'),
                'description' => $this->l('Generating complement images')
            ],
            'generateHomeSlidesData' => [
                'success_message' => $this->l('Success'),
                'error_message' => $this->l('Error'),
                'description' => $this->l('Generating Home slides')
            ],
            'getExtraModulesImages' => [
                'success_message' => $this->l('Success'),
                'error_message' => $this->l('An error occurred during modules images copying'),
                'description' => $this->l('Copying extra modules\' images')
            ],
            'checkFriendlyUrl1' => [
                'success_message' => $this->l('Success'),
                'error_message' => $this->l('Error'),
                'description' => $this->l('Restoring store settings')
            ],
            'generateParametersFile' => [
                'success_message' => $this->l('Success'),
                'error_message' => $this->l('Error'),
                'description' => $this->l('Generating sampledata info file')
            ],
            'archiveData' => [
                'success_message' => $this->l('Success'),
                'error_message' => $this->l('Error'),
                'description' => $this->l('Archive data')
            ],
            'finished' => [
                'success_message' => $this->l('Success'),
                'error_message' => $this->l('Error'),
                'description' => $this->l('Generating completed')
            ]
        ];
	}

    public function setMedia($isNewTheme = false)
    {
        $this->context->controller->addJquery();
        Media::addJsDef(array('exportDir'=> $this->context->link->getAdminLink('AdminSampleDataInstallExport')));
        $this->context->controller->addJS($this->module->module_path.'views/js/admin_export.js');
        $this->context->controller->addCss($this->module->module_path.'views/css/admin_export.css');
        parent::setMedia();
    }

	public function renderConfigurationForm()
	{
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$langs = Language::getLanguages();
		$options = array();
		foreach ($langs as $language)
			$options[] = array('id_option' => $language['id_lang'], 'name' => $language['name']);

		$inputs = array(
			array(
				'type'    => 'select',
				'label'   => $this->l('Language'),
				'desc'    => $this->l('Choose a language you wish to export'),
				'name'    => 'export_language',
				'class'   => 't',
				'options' => array(
					'query' => $options,
					'id'    => 'id_option',
					'name'  => 'name'
				),
			),
		);

		$fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Export Options'),
					'icon'  => 'icon-cogs'
				),
				'input'  => $inputs,
				'submit' => array(
					'title' => $this->l('Export'),
				)
			),
		);

		$helper = new HelperForm();
		$helper->show_toolbar = false;

		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$this->fields_form = array();
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'submitExport';
		$helper->currentIndex = self::$currentIndex;
		$helper->token = Tools::getAdminTokenLite('AdminSampleDataInstallExport');
		$helper->tpl_vars = array(
			'fields_value' => $this->getConfigFieldsValues(),
			'languages'    => $this->context->controller->getLanguages(),
			'id_language'  => $this->context->language->id,
		);

		return $helper->generateForm(array($fields_form));
	}


	public function getConfigFieldsValues()
	{
		return array(
			'export_language'  => (int)Configuration::get('PS_LANG_DEFAULT')
		);
	}

	public function postProcess()
	{
        if (Tools::getIsset('ajax') && Tools::getValue('ajax')) {
            $this->use_lang = Tools::getValue('export_language');
            $method = Tools::getValue('method');
            if (method_exists($this, $method)) {
                if ($this->{$method}()) {
                    $keys = array_keys($this->method_list);
                    $position = array_search($method, $keys);
                    $progress = ($position/count($this->method_list)) * 100;
                    $next_key = $keys[array_search($method, $keys) + 1];
                    die(json_encode(array(
                        'status' => true,
                        'response' => $this->method_list[$method]['success_message'],
                        'method' => $next_key,
                        'progress' => (int)$progress,
                        'info' => $this->method_list[$next_key]['description'],
                    )));
                }
                die(json_encode(array('status' => false, 'response' => $this->method_list[$method]['error_message'])));
            }
            die(json_encode(array('status' => false, 'response' => 'The method "'.$method.'" doesn\'t exist but invoked')));
        }

        if (Tools::getIsset('getArchive') && Tools::getValue('getArchive')) {
            die(json_encode(array('href' => $this->module->getWebPath().'output/sample_data.zip')));
        }
		if (Tools::isSubmit('submitExport')) {
			$this->use_lang = Tools::getValue('export_language');
			$this->clearOutputDirectory();
			$this->checkFriendlyUrl();
            /*$this->generateCategoryData();
            $this->generateManufacturerData();
            $this->generateSupplierData();
            $this->generateAttributeGroupData();
            $this->generateAttributeData();
            $this->generateAttachmentData();
            $this->generateFeaturesData();
            $this->generateFeatureValuesData();
            $this->generateProductsData();
			$this->generateProductAttributesData();
            $this->generateProductPackData();
            $this->generateStockAvailableData();
            $this->generateImagesData();
            $this->generateCustomerData();
            $this->generateAddressData();
            $this->generateAliasData();
            $this->generateCMSCategoryData();
            $this->generateCMSPageData();
            $this->generateLanguageData();
            $this->generateCurrencyData();
            $this->generateCarrierData();
            $this->generateCartRuleData();
            $this->generateContactData();
            $this->generateZoneData();
            $this->generateCountryData();
            $this->generateStateData();
            $this->generateDeliveryData();
            $this->generateSpecificPriceData();
            $this->generateSpecificPriceRuleData();
            $this->generateTaxData();
            $this->generateTaxRuleData();
            $this->generateTaxRuleGroupData();
            $this->generateCustomtextData();
            $this->generateConfigurationData();
            $this->getCustomTablesSQL();
            $this->getAdditionalImages();
			$this->generateHomeSlidesData();
			$this->checkFriendlyUrl(true);
			$this->generateParametersFile();
            $this->getExtraModulesImages();*/
			//$this->archiveData();
		}
	}

	public function initContent()
	{
        //var_dump($this->module->display($this->module->getLocalPath(), 'views/templates/admin/export.tpl'));
        //parent::initContent();
		$this->content = $this->displayWarning($this->l('1. Module is working in test mode. Please backup your store data before export.'));
		$this->content .= $this->displayWarning($this->l('2. Exported archive contains the following store data: Address, Alias, Attachments, Attributes,
			Carriers, Categories, CMS Categories, CMS Pages, Configurations(only custom fields), Contacts, Countries, Currencies, Deliveries, Features, Home Slides, Images, Infos, Languages, 
			Manufacturers, Products, Product Attributes, Product Packs, Specific Prices, States, Stock Available, Suppliers, Taxes, Zones and non-prestashop tables.'));
		$this->content .= $this->displayWarning($this->l('3. Export and import are fully functional only with single langue stores. Only one language can be used for export and import.'));
		$this->content .= $this->displayWarning($this->l('4. We do not guarantee the correct work of exported erchive with other third party importing tools.'));
        $this->context->smarty->assign('form', $this->renderConfigurationForm());
        $this->content .= $this->module->display($this->module->getLocalPath(), 'views/templates/admin/export.tpl');

		parent::initContent();
	}

	public function getWarehouses($id_warehouses)
	{
		return $id_warehouses['id_warehouse'];
	}

    /**
     * Create a .csv file and prepare it for writing
     *
     * @param $file_name (string) the name of the file
     *
     * @return resource
     */
	protected function openCsv($file_name)
	{
		return fopen($this->module->sendPath().'output/'.$file_name.'.vsc', 'w');
	}

    /**
     * Close a .csv file after writing
     *
     * @param $file (string) the name of the file
     */
	protected function closeCsv($file)
	{
		return fclose($file);
	}

    /**
     * Write to new .csv file all headings which will be in it
     *
     * @param $file (string) the name of the file
     * @param $fields (array) the list of exists titles for current file
     */
	protected function writeHeadings($file, $fields)
	{
		if ($fields) {
			foreach ($fields as $field => $item) {
				$titles[] = $item['label'];
			}
			fputcsv($file, $titles, $this->csv_delimiter, '"');
		}
	}

    /**
     * Write content to the opened file
     *
     * @param $file (string) the name of the file
     *
     * @param $content (array) the list of exists parameters for current file
     */
    protected function writeContent($file, $content)
    {
        fputcsv($file, $content, $this->csv_delimiter, '"');
    }

    /**
     * Generate line from object, according the fields and if class property exists
     *
     * @param $class_name
     * @param $fields
     * @param $object
     *
     * @return array
     */
    protected function transformObjectData($class_name, $fields, $object)
    {
        $result = [];
        foreach ($fields as $field => $array) {
            $result[$field] = property_exists($class_name, $field) && !is_array($object->$field) && !Tools::isEmpty($object->$field) ? $object->$field : '';
        }

        if (!$result[$field]) { // clear field if it's 0
            $result[$field] = '';
        }

        return $result;
    }

	protected function generateCategoryData()
	{
		$file = $this->openCsv('categories');
        $this->writeHeadings($file, $this->category_fields);
		$categories = Category::getCategories($this->use_lang, true);
        if ($categories) {
            foreach ($categories as $category) {
                foreach ($category as $id_category => $c) {
                    $line = array();
                    // check if category is not ROOT and not HOME
                    if ($id_category != Configuration::get('PS_HOME_CATEGORY') && $id_category != Configuration::get('PS_ROOT_CATEGORY')) {
                        $cat = new Category($id_category, $this->use_lang);
                        $line = $this->transformObjectData('Category', $this->category_fields, $cat);

                        $link = new Link();
                        $image_link = '';
                        // copy the category main image
                        if ($cat->id_image) {
                            $image_link = Tools::getShopProtocol().$link->getCatImageLink($cat->link_rewrite, $cat->id_image);
                            $this->copyConvertFileName($image_link);
                        }
                        // if exists the main category thumbnail copy it to sampledata
                        if (file_exists(_PS_CAT_IMG_DIR_.(int)$cat->id.'_thumb.jpg')) {
                            $this->copyConvertFileName(_PS_CAT_IMG_DIR_.(int)$cat->id.'_thumb.jpg');
                        }
                        // if exists thumbnails for menu copy it to sampledata
                        for ($i = 0; $i < 3; $i++) {
                            if (file_exists(_PS_CAT_IMG_DIR_.(int)$cat->id.'-'.$i.'_thumb.jpg')) {
                                $this->copyConvertFileName(_PS_CAT_IMG_DIR_.(int)$cat->id.'-'.$i.'_thumb.jpg');
                            }
                        }
                        $line['image_url'] = $image_link ? $image_link : '';

                        $this->writeContent($file, $line);
                    }
                }
            }
        }

		return $this->closeCsv($file);
	}

    protected function generateManufacturerData()
    {
        $file = $this->openCsv('manufacturers');
        $this->writeHeadings($file, $this->manufacturers_fields);
        $manufacturers = Manufacturer::getManufacturers(false, $this->use_lang, true);
        if ($manufacturers) {
            foreach ($manufacturers as $manufacturer) {
                $m = new Manufacturer($manufacturer['id_manufacturer'], $this->use_lang);
                $line = $this->transformObjectData('Manufacturer', $this->manufacturers_fields, $m);
                if (file_exists(_PS_MANU_IMG_DIR_.$m->id.'.jpg')) {
                    $this->copyConvertFileName(_PS_MANU_IMG_DIR_.$m->id.'.jpg');
                }

                $this->writeContent($file, $line);
            }
        }

        return $this->closeCsv($file);
    }

    protected function generateSupplierData()
    {
        $file = $this->openCsv('suppliers');
        $this->writeHeadings($file, $this->suppliers_fields);
        $suppliers = Supplier::getSuppliers(false, $this->use_lang, true);
        if ($suppliers) {
            foreach ($suppliers as $supplier) {
                $s = new Supplier($supplier['id_supplier'], $this->use_lang);
                $line = $this->transformObjectData('Supplier', $this->suppliers_fields, $s);
                if (file_exists(_PS_SUPP_IMG_DIR_.$s->id.'.jpg')) {
                    $this->copyConvertFileName(_PS_SUPP_IMG_DIR_.$s->id.'.jpg');
                }

                $this->writeContent($file, $line);
            }
        }

        return $this->closeCsv($file);
    }

    protected function generateAttributeGroupData()
    {
        $file = $this->openCsv('attribute_groups');
        $this->writeHeadings($file, $this->attribute_groups_fields);
        $attribute_groups = AttributeGroup::getAttributesGroups($this->use_lang);

        if ($attribute_groups) {
            uasort($attribute_groups, array('SampleDataInstallHelper', 'cmp'));

            foreach ($attribute_groups as $attribute_group) {
                foreach ($this->attribute_groups_fields as $field => $array) {
                    $line[$field] = !Tools::isEmpty($attribute_group[$field]) ? $attribute_group[$field] : '';
                }

                $this->writeContent($file, $line);
            }
        }

        return $this->closeCsv($file);
    }

    protected function generateAttributeData()
    {
        $file = $this->openCsv('attributes');
        $this->writeHeadings($file, $this->attributes_fields);
        $attributes = Attribute::getAttributes($this->use_lang);

        if ($attributes) {
            foreach ($attributes as $attribute) {
                $a = new Attribute($attribute['id_attribute'], $this->use_lang);
                $line = $this->transformObjectData('Attribute', $this->attributes_fields, $a);
                $this->writeContent($file, $line);
            }
        }

        return $this->closeCsv($file);
    }

    protected function generateAttachmentData()
    {
        $file = $this->openCsv('attachments');
        $this->writeHeadings($file, $this->attachments_fields);
        $attachments = $this->helper->getAttachments();

        if ($attachments) {
            foreach ($attachments as $attachment) {
                $a = new Attachment($attachment['id_attachment'], $this->use_lang);
                $line = $this->transformObjectData('Attachment', $this->attachments_fields, $a);

                if (file_exists(_PS_DOWNLOAD_DIR_.$a->file)) {
                    $this->writeContent($file, $line);
                    $this->copyConvertFileName(_PS_DOWNLOAD_DIR_.$a->file, false, true);
                }
            }
        }

        return $this->closeCsv($file);
    }

    protected function generateFeaturesData()
    {
        $file = $this->openCsv('features');
        $this->writeHeadings($file, $this->feature_fields);
        $features = Feature::getFeatures($this->use_lang);

        if ($features) {
            foreach ($features as $feature) {
                $feature = new Feature($feature['id_feature'], $this->use_lang);
                $line = $this->transformObjectData('Feature', $this->feature_fields, $feature);
                $this->writeContent($file, $line);
            }
        }

        return $this->closeCsv($file);
    }

    protected function generateFeatureValuesData()
    {
        $file = $this->openCsv('feature_values');
        $this->writeHeadings($file, $this->feature_value_fields);
        $feature_values = $this->helper->getAllFeaturesValues();

        if ($feature_values) {
            foreach ($feature_values as $feature_value) {
                $fv = new FeatureValue($feature_value, $this->use_lang);
                $line = $this->transformObjectData('FeatureValue', $this->feature_value_fields, $fv);
                $this->writeContent($file, $line);
            }
        }

        return $this->closeCsv($file);
    }

	protected function generateProductsData()
	{
        $file = $this->openCsv('products');
        $this->writeHeadings($file, $this->product_fields);
		$products = Product::getProducts($this->use_lang, 0, 0, 'id_product', 'ASC', false, true);

		foreach ($products as $product) {
			$p = new Product($product['id_product'], true, $this->use_lang, 1);
            $line = $this->transformObjectData('Product', $this->product_fields, $p);

			$cats = $p->getProductCategoriesFull($p->id, $this->use_lang);
			$cat_array = array();
			foreach ($cats as $cat) {
                $cat_array[] = $cat['id_category'];
            }

			$line['categories'] = implode(',', $cat_array);

			$line['price_tex'] = $p->getPrice(false);
			$line['price_tin'] = $p->getPrice(true);
			$line['upc'] = $p->upc ? $p->upc : '';

			$features = $p->getFrontFeatures($this->use_lang);
			$line['features'] = $this->helper->getConvertedProductFeatures($features);

			$specificPrice = SpecificPrice::getSpecificPrice($p->id, 1, 0, 0, 0, 0);

			$line['reduction_price'] = '';
			$line['reduction_percent'] = '';
			$line['reduction_from'] = '';
			$line['reduction_to'] = '';

			if ($specificPrice) {
				if ($specificPrice['reduction_type'] == 'amount') {
                    $line['reduction_price'] = $specificPrice['reduction'];
                } elseif ($specificPrice['reduction_type'] == 'percent') {
                    $line['reduction_percent'] = $specificPrice['reduction'];
                }

				if ($line['reduction_price'] !== '' || $line['reduction_percent'] !== '') {
					$line['reduction_from'] = $specificPrice['from'];
    				$line['reduction_to'] = $specificPrice['to'];
				}
			}

			$tags = $p->getTags($this->use_lang);
			$line['tags'] = $tags;

			$link = new Link();
			$imagelinks = array();
			$images = $p->getImages($this->use_lang);
			foreach ($images as $image) {
				$imagelink = Tools::getShopProtocol().$link->getImageLink($p->link_rewrite, $p->id.'-'.$image['id_image']);
				$this->copyConvertFileName($imagelink);
				$imagelinks[] = $imagelink;
			}

			$line['image'] = implode(',', $imagelinks);
			$line['delete_existing_images'] = 0;
			$line['shop'] = 1;
			$warehouses = Warehouse::getWarehousesByProductId($p->id);
			$line['warehouse'] = '';
			if (!empty($warehouses)) {
                $line['warehouse'] = implode(',', array_map("$this->getWarehouses", $warehouses));
            }

			$values = array();
			$accesories = $p->getAccessories($this->use_lang);
			if (isset($accesories) && $accesories && count($accesories)) {
				foreach ($accesories as $accesorie) {
                    $values[] = $accesorie['id_product'];
                }
			}

			$line['accessories'] = $values ? implode(',', $values) : '';

			$values = array();
			$carriers = $p->getCarriers();
			if (isset($carriers) && $carriers && count($carriers)) {
				foreach ($carriers as $carrier) {
                    $values[] = $carrier['id_carrier'];
                }
			}

			$line['carriers'] = $values ? implode(',', $values) : '';

			$values = array();
			$customization_fields_ids = $p->getCustomizationFieldIds();
			if (class_exists('CustomizationField') && isset($customization_fields_ids) && $customization_fields_ids && count($customization_fields_ids)) {
				foreach ($customization_fields_ids as $customization_field_id) {
					$cf = new CustomizationField($customization_field_id['id_customization_field'], $this->use_lang);
					$values[] = $cf->id.':'.$cf->type.':'.$cf->required.':'.$cf->name;
				}
			}

			$line['customization_fields_ids'] = $values ? implode(',', $values) : '';

			$values = array();
			$attachments = $p->getAttachments($this->use_lang);

			if (isset($attachments) && $attachments && count($attachments)) {
				foreach ($attachments as $attachment) {
                    $values[] = $attachment['id_attachment'];
                }
			}

			$line['attachments'] = $values ? implode(',', $values) : '';
			if (!property_exists('Product', 'base_price')) { // for versions < 1.6.0.13
                $line['base_price'] = !is_array($p->base_price) && !Tools::isEmpty($p->base_price) ? $p->base_price : '';
            }

            $this->writeContent($file, $line);
		}

        return $this->closeCsv($file);
	}

	protected function generateProductAttributesData()
	{
        $file = $this->openCsv('product_attributes');
        $this->writeHeadings($file, $this->product_attribute_fields);

		$product_attributes = $this->helper->getAllProductsAttributes();

		if ($product_attributes) {
            foreach ($product_attributes as $product_attribute) {
                $pa = new Combination($product_attribute);
                $line = $this->transformObjectData('Combination', $this->product_attribute_fields, $pa);
                $value = $this->helper->getAttributeValues($pa->id);
                $line['values'] = $value ? implode(',', $value) : '';
                $attr_images = array();
                $images = $pa->getWsImages();
                foreach ($images as $image) {
                    $attr_images[] = $image['id'];
                }
                $line['image'] = $attr_images ? implode(',', $attr_images) : '';

                $this->writeContent($file, $line);
			}
		}

        return $this->closeCsv($file);
	}

	protected function generateProductPackData()
	{
        $file = $this->openCsv('product_packs');
        $this->writeHeadings($file, $this->product_pack_fields);
		$product_packs = $this->helper->getAllProductsPacks();

		if ($product_packs) {
			foreach ($product_packs as $product_pack) {
				$line['id_product_pack'] = $product_pack['id_product_pack'];
				$line['id_product_item'] = $product_pack['id_product_item'];
				$line['id_product_attribute_item'] = $product_pack['id_product_attribute_item'];
				$line['quantity'] = $product_pack['quantity'];

                $this->writeContent($file, $line);
			}
		}

        return $this->closeCsv($file);
	}

    protected function generateStockAvailableData()
    {
        $file = $this->openCsv('stock_available');
        $this->writeHeadings($file, $this->stock_available_fields);
        $stock = $this->helper->getAllStockAvailableData();

        if ($stock) {
            foreach ($stock as $stock_available) {
                $sa = new StockAvailable($stock_available);
                $line = $this->transformObjectData('StockAvailable', $this->stock_available_fields, $sa);
                $this->writeContent($file, $line);
            }
        }

        return $this->closeCsv($file);
    }

	protected function generateImagesData()
	{
        $file = $this->openCsv('images');
        $this->writeHeadings($file, $this->image_fields);
		$images = Image::getAllImages();
		if ($images) {
			foreach ($images as $image) {
				$i = new Image($image['id_image'], $this->use_lang);
                $line = $this->transformObjectData('Image', $this->image_fields, $i);
                $this->writeContent($file, $line);
			}
		}

        return $this->closeCsv($file);
	}

	protected function generateCustomerData()
	{
        $file = $this->openCsv('customers');
        $this->writeHeadings($file, $this->customers_fields);
		$customers = Customer::getCustomers();

		foreach ($customers as $customer) {
			$c = new Customer($customer['id_customer']);

			foreach ($this->customers_fields as $field => $array) {
				if ($field == 'passwd') {
                    $line[$field] = Tools::encrypt($c->$field);
                } else {
                    $line[$field] = property_exists('Customer', $field) && !is_array($c->$field) && !Tools::isEmpty($c->$field) ? $c->$field : '';
                }
			}

            $this->writeContent($file, $line);
		}

        return $this->closeCsv($file);
	}

	protected function generateAddressData()
	{
        $file = $this->openCsv('address');
        $this->writeHeadings($file, $this->address_fields);
		$addresses = $this->helper->getCustomersAddresses();

		foreach ($addresses as $address) {
			$a = new Address($address['id_address']);

			foreach ($this->address_fields as $field => $array) {
				$line['id'] = $address['id_address'];
				$line['active'] = $address['active'];
				if ($field != 'id' && $field != 'active') {
                    $line[$field] = property_exists('Address', $field) && !is_array($a->$field) && !Tools::isEmpty($a->$field) ? $a->$field : '';
                }
			}

            $this->writeContent($file, $line);
		}

        return $this->closeCsv($file);
	}

	protected function generateAliasData()
	{
        $file = $this->openCsv('alias');
        $this->writeHeadings($file, $this->alias_fields);
		$aliases = $this->helper->getAliases();

		if ($aliases) {
			foreach ($aliases as $alias) {
				$a = new Alias($alias['id_alias']);
                $line = $this->transformObjectData('Alias', $this->alias_fields, $a);
                $this->writeContent($file, $line);
			}
		}

        return $this->closeCsv($file);
	}

	protected function generateCMSCategoryData()
	{
        $file = $this->openCsv('cms_categories');
        $this->writeHeadings($file, $this->cms_category_fields);

		$cms_categories = CMSCategory::getCategories($this->use_lang);

		if ($cms_categories) {
			foreach ($cms_categories as $cms_category) {
				foreach ($cms_category as $cc) {
					$cms_cat = new CMSCategory($cc['infos']['id_cms_category'], $this->use_lang);
                    $line = $this->transformObjectData('CMSCategory', $this->cms_category_fields, $cms_cat);
                    $this->writeContent($file, $line);
				}
			}
		}

        return $this->closeCsv($file);
	}

	protected function generateCMSPageData()
	{
        $file = $this->openCsv('cms_pages');
        $this->writeHeadings($file, $this->cms_pages_fields);
		$cms_pages = CMS::getCMSPages($this->use_lang);

		foreach ($cms_pages as $cms_page) {
			$cms_p = new CMS($cms_page['id_cms'], $this->use_lang);
            $line = $this->transformObjectData('CMS', $this->cms_pages_fields, $cms_p);
            $this->writeContent($file, $line);
		}

        return $this->closeCsv($file);
	}

	protected function generateLanguageData()
	{
        $file = $this->openCsv('languages');
        $this->writeHeadings($file, $this->languages_fields);
		$languages = Language::getLanguages();

		if ($languages) {
			foreach ($languages as $language) {
                $l = new Language($language['id_lang']);
                $line = $this->transformObjectData('Language', $this->languages_fields, $l);
                $this->writeContent($file, $line);
			}
		}

        return $this->closeCsv($file);
	}

	protected function generateCurrencyData()
	{
        $file = $this->openCsv('currencies');
        $this->writeHeadings($file, $this->currencies_fields);
		$currencies = Currency::getCurrencies();

		if ($currencies) {
			foreach ($currencies as $currency) {
				$c = new Currency($currency['id_currency']);
                $line = $this->transformObjectData('Currency', $this->currencies_fields, $c);
                $this->writeContent($file, $line);
			}
		}

        return $this->closeCsv($file);
	}

	protected function generateCarrierData()
	{
        $file = $this->openCsv('carriers');
        $this->writeHeadings($file, $this->carriers_fields);
		$carriers = Carrier::getCarriers($this->use_lang);

		if ($carriers) {
			foreach ($carriers as $carrier) {
				$c = new Carrier($carrier['id_carrier'], $this->use_lang);
                $line = $this->transformObjectData('Carrier', $this->carriers_fields, $c);
				$line['carrier_groups'] = $c->getGroups($carrier['id_carrier']) ? implode(',', $this->helper->oneL($c->getGroups($carrier['id_carrier']))) : '';
				$line['zones'] = $this->helper->getCarrierZones($carrier['id_carrier']) ? implode(',', $this->helper->getCarrierZones($carrier['id_carrier'])) : '';
				$line['range_price'] = $this->helper->getRangePriceByCarrier('range_price', $carrier['id_carrier']) ? implode(',', $this->helper->getRangePriceByCarrier('range_price', $carrier['id_carrier'])) : '';
				$line['range_weight'] = $this->helper->getRangePriceByCarrier('range_weight', $carrier['id_carrier']) ? implode(',', $this->helper->getRangePriceByCarrier('range_weight', $carrier['id_carrier'])) : '';

				if (file_exists(_PS_SHIP_IMG_DIR_.$c->id.'.jpg')) {
                    $this->copyConvertFileName(_PS_SHIP_IMG_DIR_.$c->id.'.jpg');
                }

                $this->writeContent($file, $line);
			}
		}

        return $this->closeCsv($file);
	}

	protected function generateCartRuleData()
	{
        $file = $this->openCsv('cart_rules');
        $this->writeHeadings($file, $this->cartrules_fields);
		$cart_rules = $this->helper->getCartRules();

		if ($cart_rules) {
			foreach ($cart_rules as $cart_rule) {
				$cr = new CartRule($cart_rule['id_cart_rule'], $this->use_lang);
                $line = $this->transformObjectData('CartRule', $this->cartrules_fields, $cr);
                $this->writeContent($file, $line);
			}
		}

        return $this->closeCsv($file);
	}

	protected function generateContactData()
	{
        $file = $this->openCsv('contacts');
        $this->writeHeadings($file, $this->contacts_fields);
		$contacts = Contact::getContacts($this->use_lang);

		if ($contacts) {
			foreach ($contacts as $contact) {
				$c = new Contact($contact['id_contact'], $this->use_lang);
                $line = $this->transformObjectData('Contact', $this->contacts_fields, $c);
                $this->writeContent($file, $line);
			}
		}

        return $this->closeCsv($file);
	}

    protected function generateZoneData()
    {
        $file = $this->openCsv('zones');
        $this->writeHeadings($file, $this->zones_fields);
        $zones = Zone::getZones();

        if ($zones) {
            foreach ($zones as $zone) {
                foreach ($this->zones_fields as $field => $array) {
                    $line[$field] = !Tools::isEmpty($zone[$field]) ? $zone[$field] : '';
                }

                $this->writeContent($file, $line);
            }
        }

        return $this->closeCsv($file);
    }

	protected function generateCountryData()
	{
        $file = $this->openCsv('countries');
        $this->writeHeadings($file, $this->countries_fields);
		$countries = Country::getCountries($this->use_lang);

		if ($countries) {
			foreach ($countries as $country) {
				foreach ($this->countries_fields as $field => $array) {
                    $line[$field] = !Tools::isEmpty($country[$field]) ? $country[$field] : '';
                }

                $this->writeContent($file, $line);
			}
		}

        return $this->closeCsv($file);
	}

	protected function generateStateData()
	{
        $file = $this->openCsv('states');
        $this->writeHeadings($file, $this->states_fields);
		$states = State::getStates($this->use_lang);

		if ($states) {
			foreach ($states as $state) {
				foreach ($this->states_fields as $field => $array) {
                    $line[$field] = !Tools::isEmpty($state[$field]) ? $state[$field] : '';
                }

                $this->writeContent($file, $line);
			}
		}

        return $this->closeCsv($file);
	}

	protected function generateDeliveryData()
	{
        $file = $this->openCsv('deliveries');
        $this->writeHeadings($file, $this->delivery_fields);
		$deliveries = $this->helper->getDeliveries();

		if ($deliveries) {
			foreach ($deliveries as $delivery) {
				$d = new Delivery($delivery['id_delivery']);
                $line = $this->transformObjectData('Delivery', $this->delivery_fields, $d);
				$this->writeContent($file, $line);
			}
		}

        return $this->closeCsv($file);
	}

	protected function generateSpecificPriceData()
	{
        $file = $this->openCsv('specific_prices');
        $this->writeHeadings($file, $this->specific_price_fields);
		$specific_prices = $this->helper->getAllSpecificPrices();

		if ($specific_prices) {
			foreach ($specific_prices as $specific_price) {
				$sp = new SpecificPrice($specific_price['id_specific_price']);
                $line = $this->transformObjectData('SpecificPrice', $this->specific_price_fields, $sp);
                $this->writeContent($file, $line);
			}
		}

        return $this->closeCsv($file);
	}

	protected function generateSpecificPriceRuleData()
	{
        $file = $this->openCsv('specific_price_rules');
        $this->writeHeadings($file, $this->specific_price_rule_fields);
        $specific_price_rules = $this->helper->getAllSpecificPriceRules();

        if ($specific_price_rules) {
            foreach ($specific_price_rules as $specific_price_rule) {
                $spr = new SpecificPriceRule($specific_price_rule['id_specific_price_rule']);
                $line = $this->transformObjectData('SpecificPriceRule', $this->specific_price_rule_fields, $spr);
                $values = array();
                $conditions = $spr->getConditions();
                if ($conditions) {
                    foreach ($conditions as $condition) {
                        foreach ($condition as $c) {
                            $values[] = $c['type'].':'.$c['value'];
                        }
					}
				}
				$line['conditions'] = $conditions ? implode(',', $values) : '';

                $this->writeContent($file, $line);
			}
		}

        return $this->closeCsv($file);
	}

	protected function generateTaxData()
	{
        $file = $this->openCsv('taxes');
        $this->writeHeadings($file, $this->tax_fields);
		$taxes = Tax::getTaxes();

		if ($taxes) {
			foreach ($taxes as $tax) {
				$t = new Tax($tax['id_tax'], $this->use_lang);
                $line = $this->transformObjectData('Tax', $this->tax_fields, $t);
                $this->writeContent($file, $line);
			}
		}

        return $this->closeCsv($file);
	}

	protected function generateTaxRuleData()
	{
        $file = $this->openCsv('tax_rules');
        $this->writeHeadings($file, $this->tax_rule_fields);
		$tax_rules = $this->helper->getAllTaxRules();

		if ($tax_rules) {
			foreach ($tax_rules as $tax_rule) {
				$tr = new TaxRule($tax_rule);
                $line = $this->transformObjectData('TaxRule', $this->tax_rule_fields, $tr);
                $this->writeContent($file, $line);
			}
		}

        return $this->closeCsv($file);
	}

	protected function generateTaxRuleGroupData()
	{
        $file = $this->openCsv('tax_rule_groups');
        $this->writeHeadings($file, $this->tax_rule_group_fields);
		$tax_rule_groups = TaxRulesGroup::getTaxRulesGroups();

		if ($tax_rule_groups) {
			foreach ($tax_rule_groups as $tax_rule_group) {
				$trg = new TaxRulesGroup($tax_rule_group['id_tax_rules_group']);
                $line = $this->transformObjectData('TaxRulesGroup', $this->tax_rule_group_fields, $trg);
                $this->writeContent($file, $line);
			}
		}

        return $this->closeCsv($file);
	}

	protected function generateHomeSlidesData()
	{
		if (!(int)Validate::isLoadedObject(Module::getInstanceByName('ps_imageslider')) || !Module::isEnabled('ps_imageslider') || !Module::isInstalled('ps_imageslider')) {
            return;
        }

        $file = $this->openCsv('home_slides');
        $this->writeHeadings($file, $this->home_slide_fields);
		$slides = $this->helper->getAllHomeSlides();

		if ($slides) {
			foreach ($slides as $slide) {
				$s = new Ps_HomeSlide($slide, $this->use_lang);
                $line = $this->transformObjectData('Ps_HomeSlide', $this->home_slide_fields, $s);
				if (file_exists(_PS_MODULE_DIR_.'ps_imageslider/images/'.$s->image)) {
                    $this->copyConvertFileName(_PS_MODULE_DIR_.'ps_imageslider/images/'.$s->image, true);
                }
                $this->writeContent($file, $line);
			}
		}

        return $this->closeCsv($file);
	}

	protected function generateCustomtextData()
	{
		if (!(int)Validate::isLoadedObject(Module::getInstanceByName('ps_customtext')) || !Module::isEnabled('ps_customtext') || !Module::isInstalled('ps_customtext')) {
            return;
        }

        $file = $this->openCsv('customtext');
        $this->writeHeadings($file, $this->customtext_fields);
		$custom_text = $this->helper->getAllCustomText();

		if ($custom_text) {
			if (!class_exists('CustomText')) {
                return;
            }
			foreach ($custom_text as $info) {
				$i = new CustomText($info, $this->use_lang);
                $line = $this->transformObjectData('CustomText', $this->customtext_fields, $i);
                $this->writeContent($file, $line);
			}
		}

        return $this->closeCsv($file);
	}

    protected function getExtraModulesImages()
    {
        if (Module::isEnabled('jxblog')) {
            $blogImages = array_merge(
                Tools::scandir(_PS_MODULE_DIR_.'jxblog/img', 'jpg', '', true),
                Tools::scandir(_PS_MODULE_DIR_.'jxblog/img', 'gif', '', true),
                Tools::scandir(_PS_MODULE_DIR_.'jxblog/img', 'png', '', true),
                Tools::scandir(_PS_MODULE_DIR_.'jxblog/img', 'jpeg', '', true)
            );
            if ($blogImages) {
                foreach ($blogImages as $image) {
                    if (file_exists(_PS_MODULE_DIR_.'jxblog/img/'.$image)) {
                        $this->copyConvertFileName(_PS_MODULE_DIR_.'jxblog/img/'.$image, true);
                    }
                }
            }
        }
        if (Module::isEnabled('jxmegalayout')) {
            $jxmegalayoutExtraContentImages = array_merge(
                Tools::scandir(_PS_MODULE_DIR_.'jxmegalayout/extracontent', 'jpg', '', true),
                Tools::scandir(_PS_MODULE_DIR_.'jxmegalayout/extracontent', 'gif', '', true),
                Tools::scandir(_PS_MODULE_DIR_.'jxmegalayout/extracontent', 'png', '', true),
                Tools::scandir(_PS_MODULE_DIR_.'jxmegalayout/extracontent', 'jpeg', '', true)
            );
            if ($jxmegalayoutExtraContentImages) {
                foreach ($jxmegalayoutExtraContentImages as $image) {
                    if (file_exists(_PS_MODULE_DIR_.'jxmegalayout/extracontent/'.$image)) {
                        $this->copyConvertFileName(_PS_MODULE_DIR_.'jxmegalayout/extracontent/'.$image, true);
                    }
                }
            }
        }
        if (Module::isEnabled('jxmegamenu')) {
            $jxmegamenuImages = array_merge(
                Tools::scandir(_PS_MODULE_DIR_.'jxmegamenu/images/', 'jpg', '', true),
                Tools::scandir(_PS_MODULE_DIR_.'jxmegamenu/images/', 'gif', '', true),
                Tools::scandir(_PS_MODULE_DIR_.'jxmegamenu/images/', 'png', '', true),
                Tools::scandir(_PS_MODULE_DIR_.'jxmegamenu/images/', 'jpeg', '', true)
            );
            if ($jxmegamenuImages) {
                foreach ($jxmegamenuImages as $image) {
                    if (file_exists(_PS_MODULE_DIR_.'jxmegamenu/images/'.$image)) {
                        $this->copyConvertFileName(_PS_MODULE_DIR_.'jxmegamenu/images/'.$image, true);
                    }
                }
            }
        }
        if (Module::isEnabled('jxmosaicproducts')) {
            $jxmosaicproductsImages = array_merge(
                Tools::scandir(_PS_MODULE_DIR_.'jxmosaicproducts/images/', 'jpg', '', true),
                Tools::scandir(_PS_MODULE_DIR_.'jxmosaicproducts/images/', 'gif', '', true),
                Tools::scandir(_PS_MODULE_DIR_.'jxmosaicproducts/images/', 'png', '', true),
                Tools::scandir(_PS_MODULE_DIR_.'jxmosaicproducts/images/', 'jpeg', '', true)
            );
            if ($jxmosaicproductsImages) {
                foreach ($jxmosaicproductsImages as $image) {
                    if (file_exists(_PS_MODULE_DIR_.'jxmosaicproducts/images/'.$image)) {
                        $this->copyConvertFileName(_PS_MODULE_DIR_.'jxmosaicproducts/images/'.$image, true);
                    }
                }
            }
        }
        return true;
    }

	protected function generateConfigurationData()
	{
        $file = $this->openCsv('configurations');
        $this->writeHeadings($file, $this->configuration_fields);
		$configurations = $this->helper->getAllConfigurations();

		if ($configurations) {
			$exceptions = $this->configuration_exeptions;
			foreach ($configurations as $configuration) {
				if (!in_array($configuration['name'], $exceptions)) {
					$line['name'] = $configuration['name'];
					$line['value'] = $configuration['value'];
                    $this->writeContent($file, $line);
				}
			}
		}

        return $this->closeCsv($file);
	}

	protected function getCustomTablesSQL()
	{
		$path = new Sampledatainstall();
		$tables = array_diff($this->getAllTablesList(), $this->defaultTablesList);
		foreach ($tables as $table) {
			$file = fopen($path->sendPath().'output/'.$table.'.lqs', 'w');
			if (!is_resource($file)) {
                return false;
            }
			$data = $this->mySQLDumpTableStructure(_DB_PREFIX_.$table);
			$data .= $this->mySQLDumpTableData(_DB_PREFIX_.$table);
			fwrite($file, $data);
			fclose($file);
		}

        return true;
	}

	protected function getAllTablesList()
	{
		$list = array();
		$result = Db::getInstance()->executeS('SHOW TABLES');
		if (!$result)
			return false;

		foreach ($result as $res) {
			$key = array_keys($res);
			$list[] = str_replace(_DB_PREFIX_, '', $res[$key[0]]);
		}

		return $list;
	}

	private function mySQLDumpTableStructure($table)
	{
		$data = '';
		$schema = Db::getInstance()->executeS('SHOW CREATE TABLE `'.$table.'`');

		$data .= 'DROP TABLE IF EXISTS `'.$schema[0]['Table'].'`;'."\n";
		$data .= $schema[0]['Create Table'].";\n\n";

		return $data;
	}

	private function mySQLDumpTableData($table)
	{
		$schema = Db::getInstance()->executeS('SHOW CREATE TABLE `'.$table.'`');
		$data = '';

		$query = Db::getInstance()->query('SELECT * FROM `'.$schema[0]['Table'].'`', false);
		$sizeof = DB::getInstance()->NumRows();
		$lines = explode("\n", $schema[0]['Create Table']);

		if ($query && $sizeof > 0) {
			// Export the table data
			$data .= '/* Scheme for table '.$schema[0]['Table']." */\n";

			$data .= 'INSERT INTO `'.$schema[0]['Table']."` VALUES\n";
			$i = 1;
			while ($row = DB::getInstance()->nextRow($query)) {
				$s = '(';

				foreach ($row as $field => $value) {
					$tmp = "'".pSQL($value, true)."',";
					if ($tmp != "'',") {
                        $s .= $tmp;
                    } else {
						foreach ($lines as $line) {
                            if (strpos($line, '`'.$field.'`') !== false) {
                                if (preg_match('/(.*NOT NULL.*)/Ui', $line)) {
                                    $s .= "'',";
                                } else {
                                    $s .= 'NULL,';
                                }
                                break;
                            }
                        }
					}
				}
				$data .= rtrim($s, ',');

				if ($i % 200 == 0 && $i < $sizeof) {
                    $data .= ");\nINSERT INTO `".$schema[0]['Table']."` VALUES\n";
                } elseif ($i < $sizeof) {
                    $data .= "),\n";
                } else {
                    $data .= ");\n";
                }

				++$i;
			}
		}

		return $data;
	}

	protected function generateParametersFile()
	{
		$delimiter = ';';
		$new_path = new Sampledatainstall();
		$path = $new_path->sendPath().'output/';
		$file_list = array();

		if ($dh = opendir($path)) {
			while (($file = readdir($dh)) !== false) {
				if ($file != '.' && $file != '..') {
                    $file_list[] = $file;
                }
			}
			closedir($dh);
		}
		$file_list[] = 'settings.vsc';

		$f = fopen($path.'settings.vsc', 'w');

		$titles = array('Database version', 'Prestashop Version', 'Files list');

		fputcsv($f, $titles, $delimiter, '"');

		$lines = array(Configuration::get('PS_VERSION_DB'), _PS_VERSION_, implode(',', $file_list));

		fputcsv($f, $lines, $delimiter, '"');

		fclose($f);

        return true;
	}

	protected function getAdditionalImages()
	{
		// copy logo + favicon + stores icon
		$main_images = array(Configuration::get('PS_LOGO'), Configuration::get('PS_FAVICON'), Configuration::get('PS_STORES_ICON'));
		foreach ($main_images as $main_image) {
			if (file_exists(_PS_CORE_IMG_DIR_.$main_image)) {
                $this->copyConvertFileName(_PS_CORE_IMG_DIR_.$main_image);
            }
		}
		// copy cms images
		$cms_images_path = _PS_CORE_IMG_DIR_.'cms/';
		$images_jpg = Tools::scandir($cms_images_path, 'jpg');
		$images_gif = Tools::scandir($cms_images_path, 'gif');
		$images_png = Tools::scandir($cms_images_path, 'png');
		$images_jpeg = Tools::scandir($cms_images_path, 'jpeg');
		$images = array_merge($images_jpg, $images_gif, $images_png, $images_jpeg);
		if ($images && count($images)) {
            foreach ($images as $image) {
                $this->copyConvertFileName($cms_images_path.$image);
            }
        }

        return true;
	}

	protected function copyConvertFileName($file, $is_module = false, $is_download = false)
	{
		$divider = $is_module ? 'modules/' : 'img/';
		if ($is_download) {
            $divider = 'download/';
        }

		$get_file_name = explode('/', $file);
		$file_name = $get_file_name[count($get_file_name) - 1];

		$file_path_name = str_replace('/', '#', rtrim(str_replace($file_name, '', strstr($file, $divider)), '/'));

		$file_type = explode('.', $file_name);
		$name_f = $file_type[0];
		$file_type = strrev($file_type[count($file_type) - 1]);

		if ($is_download) {
            copy($file, $this->module->sendPath().'output/'.$file_path_name.'@'.$name_f);
        } else {
            copy($file, $this->module->sendPath().'output/'.$file_path_name.'@'.$name_f.'.'.$file_type);
        }
	}

	protected function clearOutputDirectory()
	{
        $result = true;
		$files = glob($this->module->sendPath().'output/{,.}*', GLOB_BRACE);
		foreach ($files as $file) {
			if (is_file($file)) {
                $result &= unlink($file);
            }
		}

        return $result;
	}

	protected function archiveData()
	{
		$p = new Sampledatainstall();
		$path = $p->sendPath().'output/';
		$file_name = 'sample_data.zip';
		$files = array();
		if ($dh = opendir($path)) {
			while (($file = readdir($dh)) !== false) {
				if ($file != '.' && $file != '..') {
                    $files[] = $file;
                }
			}
			closedir($dh);
		}

		$zip = new ZipArchive();
		if ($zip->open($path.$file_name, ZipArchive::OVERWRITE | ZipArchive::CREATE) !== true) {
            $this->displayError($this->l('cannot open '.$file_name.'\n'));
        }

		foreach ($files as $file) {
            $zip->addFile($path.$file, ltrim($file, '/'));
        }

		if ($zip->close()) {
            foreach ($files as $file) {
                unlink($path.$file);
            }
        }

        return true;
		//$this->zipSave($path, $file_name);
	}

	protected function zipSave($path, $file_name)
	{
		$file = $path.$file_name;

		session_write_close();

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Cache-Control: public');
		header('Content-Description: File Transfer');
		header('Content-type: application/octet-stream');
		header('Content-Disposition: attachment; filename='.$file_name.'');
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: '.@filesize($file));

		$this->readfileChunked($file) || header('Location: '.$path);

		exit();
	}

	protected function readfileChunked($file, $retbytes = true)
	{
		$chunksize = 1024 * 1024;
		$cnt       = 0;
		$handle    = @fopen($file, 'r');

		if ($size = @filesize($file)) {
            header('Content-Length: '.$size);
        }

		if (false === $handle) {
            return false;
        }

		while (!@feof($handle)) {
			$buffer = @fread($handle, $chunksize);
			echo $buffer;
			ob_flush();
			flush();
			if ($retbytes) {
                $cnt += Tools::strlen($buffer);
            }
		}

		$status = @fclose($handle);

		if ($retbytes && $status) {
            return $cnt;
        }

		return $status;
	}
	
	protected function checkFriendlyUrl($refresh = false)
	{		
		if ($refresh) {
            Configuration::updateValue('PS_REWRITING_SETTINGS', $this->frendly_url);
        } else {
			$this->frendly_url = Configuration::get('PS_REWRITING_SETTINGS');
			Configuration::updateValue('PS_REWRITING_SETTINGS', false);
		}

        return true;
	}

    protected function checkFriendlyUrl1() {
        return $this->checkFriendlyUrl(true);
    }
}