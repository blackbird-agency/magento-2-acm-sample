<?php
/**
 * Blackbird ContentManagerSample Module
 *
 * NOTICE OF LICENSE
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@bird.eu so we can send you a copy immediately.
 *
 * @category        Blackbird
 * @package         Blackbird_ContentManagerSample Project
 * @copyright       Copyright (c) 2017 Blackbird (https://black.bird.eu)
 * @author          Thomas Klein (Blackbird Team)
 * @license         MIT
 */
namespace Blackbird\ContentManagerSample\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Blackbird\ContentManager\Api\Data\ContentTypeInterfaceFactory;
use Blackbird\ContentManager\Api\Data\ContentType\CustomFieldsetInterfaceFactory;
use Blackbird\ContentManager\Api\Data\ContentInterfaceFactory;
use Blackbird\ContentManager\Model\ContentType;
use Blackbird\ContentManager\Model\ContentType\CustomFieldset;

class InstallData implements InstallDataInterface
{
    /**
     * @var ContentTypeInterfaceFactory
     */
    private $contentTypeFactory;

    /**
     * @var CustomFieldsetInterfaceFactory
     */
    private $customFieldsetfactory;

    /**
     * @var ContentInterfaceFactory
     */
    private $contentFactory;

    /**
     * @param ContentTypeInterfaceFactory $contentTypeFactory
     * @param CustomFieldsetInterfaceFactory $customFieldsetfactory
     * @param ContentInterfaceFactory $contentFactory
     */
    public function __construct(
        ContentTypeInterfaceFactory $contentTypeFactory,
        CustomFieldsetInterfaceFactory $customFieldsetfactory,
        ContentInterfaceFactory $contentFactory
    ) {
        $this->contentTypeFactory = $contentTypeFactory;
        $this->customFieldsetfactory = $customFieldsetfactory;
        $this->contentFactory = $contentFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->installSampleData();

        $setup->endSetup();
    }

    /**
     * Install the Content Manager Sample Data
     *
     * @return void
     */
    private function installSampleData()
    {
        foreach ($this->_getSampleData() as $sampleData) {
            if (isset($sampleData['content_type'])) {
                $contentType = $this->createContentType($sampleData['content_type']);

                if (isset($sampleData['custom_fieldsets'])) {
                    $this->addCustomFields($contentType, $sampleData['custom_fieldsets']);
                }

                if (isset($sampleData['contents'])) {
                    $this->addContents($contentType, $sampleData['contents']);
                }
            }
        }
    }

    /**
     * Create and save a new Content Type
     *
     * @param array $definition
     * @return ContentType
     */
    private function createContentType(array $definition)
    {
        return $this->contentTypeFactory->create()->setData($definition)->save();
    }

    /**
     * Add and save the Custom Fields to the Content Type
     *
     * @param ContentType $contentType
     * @param array $definition
     * @return void
     */
    private function addCustomFields(ContentType $contentType, array $definition)
    {
        foreach ($definition as $customFieldsetData) {
            if (isset($customFieldsetData['definition'])) {
                /** @var CustomFieldset $customFieldset */
                $customFieldset = $this->customFieldsetfactory->create()
                    ->setData($customFieldsetData['definition'])
                    ->setCtId($contentType->getCtId())
                    ->save();

                if (isset($customFieldsetData['custom_fields'])) {
                    foreach ($customFieldsetData['custom_fields'] as $customFieldData) {
                        $customFieldData['fieldset_id'] = $customFieldset->getId();
                        $contentType->addCustomField($customFieldData);
                    }
                }
            }
        }

        $contentType->saveCustomFields();
    }

    /**
     * Create and Save a new Content
     *
     * @param ContentType $contentType
     * @param array $contents
     * @return void
     */
    private function addContents(ContentType $contentType, array $contents)
    {
        foreach ($contents as $content) {
            $content['ct_id'] = $contentType->getCtId();
            $this->contentFactory->create()->setData($content)->save();
        }
    }

    /**
     * Retrieve the samples data of ACM
     *
     * @return array
     */
    private function _getSampleData()
    {
        return [
            [
                'content_type' => [
                    'title' => 'Travel',
                    'identifier' => 'travel',
                    'description' => 'My travels around the world.',
                    'breadcrumb' => '{{title}}',
                    'breadcrumb_prev_link' => 'a:6:{i:1;s:36:"travel;travel/{{travel_destination}}";}',
                    'breadcrumb_prev_name' => 'a:6:{i:1;s:29:"Travel;{{travel_destination}}";}',
                    'default_url' => 'travel/{{travel_destination}}',
                    'page_title' => 'My travel to {{travel_destination}}',
                    'search_enabled' => 1,
                    'sitemap_enabled' => 1,
                    'sitemap_frequency' => 'always',
                    'sitemap_priority' => 1,
                    'default_status' => 1,
                ],
                'custom_fieldsets' => [
                    [
                        'definition' => [
                            'title' => 'Destination',
                            'sort_order' => 0,
                        ],
                        'custom_fields' => [
                            [
                                'identifier' => 'travel_country',
                                'title' => 'Country of Destination',
                                'type' => 'country',
                                'is_require' => 1,
                                'sort_order' => 0,
                                'show_in_grid' => 1,
                                'default_value' => 'FR',
                            ],
                            [
                                'identifier' => 'travel_from',
                                'title' => 'Country of Departure',
                                'type' => 'country',
                                'is_require' => 0,
                                'sort_order' => 1,
                                'show_in_grid' => 0,
                                'default_value' => 'FR',
                            ],
                            [
                                'identifier' => 'travel_duration',
                                'title' => 'Duration of the trip',
                                'type' => 'integer',
                                'is_require' => 0,
                                'sort_order' => 2,
                                'show_in_grid' => 0,
                                'note' => 'The time is in hours',
                                'default_value' => 'FR',
                            ],
                        ],
                    ],
                    [
                        'definition' => [
                            'title' => 'Gallery',
                            'sort_order' => 1,
                        ],
                        'custom_fields' => [
                            [
                                'identifier' => 'travel_image',
                                'title' => 'Main Picture',
                                'type' => 'image',
                                'is_require' => 1,
                                'sort_order' => 3,
                                'show_in_grid' => 1,
                                'note' => '',
                                'crop' => 1,
                                'crop_w' => '',
                                'crop_h' => '',
                                'keep_aspect_ratio' => 1,
                                'file_path' => 'travel/',
                                'img_alt' => 1,
                                'img_title' => 1,
                                'img_url' => 0,
                                'file_extension' => 'png,jpg,jpeg,gif',
                            ],
                            [
                                'identifier' => 'travel_gallery',
                                'title' => 'Gallery Images',
                                'type' => 'content',
                                'is_require' => 0,
                                'sort_order' => 4,
                                'show_in_grid' => 0,
                                'note' => '',
                                'content_type' => 'gallery',
                            ],
                        ],
                    ],
                    [
                        'definition' => [
                            'title' => 'Gear',
                            'sort_order' => 2,
                        ],
                        'custom_fields' => [
                            [
                                'identifier' => 'travel_gear_review',
                                'title' => 'Gear Review',
                                'type' => 'area',
                                'is_require' => 1,
                                'sort_order' => 5,
                                'show_in_grid' => 0,
                                'note' => 'Short review of the product(s)',
                                'default_value' => '',
                                'max_characters' => '500',
                                'wysiwyg_editor' => 1,
                            ],
                            [
                                'identifier' => 'travel_main_gears',
                                'title' => 'Travel Gears',
                                'type' => 'product',
                                'is_require' => 1,
                                'sort_order' => 6,
                                'show_in_grid' => 1,
                                'note' => '',
                            ],
                            [
                                'identifier' => 'travel_other_gears',
                                'title' => 'Other used gears',
                                'type' => 'product',
                                'is_require' => 0,
                                'sort_order' => 7,
                                'show_in_grid' => 0,
                                'note' => '',
                            ],
                        ],
                    ],
                ],
                'contents' => [
                    [
                        'status' => 1,
                        'store_id' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                        'title' => 'Mon roadtrip en France',
                        'url_key' => 'travel/FR',
                        'travel_country' => 'FR',
                        'travel_gear_review' => 'Très bon produit ! ***** Magnifique !!',
                        'travel_main_gears' => '24-MB01',
                    ],
                    [
                        'status' => 1,
                        'store_id' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                        'title' => 'Mon roadtrip en Belgique',
                        'url_key' => 'travel/BE',
                        'travel_country' => 'BE',
                        'travel_gear_review' => 'Un autre très bon produit !',
                        'travel_main_gears' => '24-MB01',
                    ],
                ],
            ],
            [
                'content_type' => [
                    'title' => 'Gallery',
                    'identifier' => 'gallery',
                    'description' => 'My pictures.',
                    'breadcrumb' => '{{title}}',
                    'breadcrumb_prev_link' => 'a:6:{i:1;s:36:"gallery";}',
                    'breadcrumb_prev_name' => 'a:6:{i:1;s:29:"Gallery";}',
                    'default_url' => 'gallery/{{title}}',
                    'page_title' => 'My picture {{entity_id}}',
                    'search_enabled' => 0,
                    'sitemap_enabled' => 0,
                ],
                'custom_fieldsets' => [
                    [
                        'definition' => [
                            'title' => 'Picture',
                            'sort_order' => 0,
                        ],
                        'custom_fields' => [
                            [
                                'identifier' => 'gallery_text',
                                'title' => 'Short Description',
                                'type' => 'area',
                                'is_require' => 0,
                                'sort_order' => 4,
                                'show_in_grid' => 0,
                                'note' => '',
                                'default_value' => '',
                                'max_characters' => '200',
                                'wysiwyg_editor' => 0,
                            ],
                            [
                                'identifier' => 'gallery_picture',
                                'title' => 'Picture',
                                'type' => 'image',
                                'is_require' => 1,
                                'sort_order' => 4,
                                'show_in_grid' => 1,
                                'note' => '',
                                'crop' => 1,
                                'crop_w' => '',
                                'crop_h' => '',
                                'keep_aspect_ratio' => 1,
                                'file_path' => 'gallery/',
                                'img_alt' => 1,
                                'img_title' => 1,
                                'img_url' => 1,
                                'file_extension' => 'png,jpg,jpeg,gif',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
