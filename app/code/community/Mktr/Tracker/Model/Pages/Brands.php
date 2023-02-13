<?php
/**
 * @copyright   © EAX LEX SRL. All rights reserved.
 * @license     http://opensource.org/licenses/osl-3.0.php - Open Software License (OSL 3.0)
 **/

class Mktr_Tracker_Model_Pages_Brands
{
    private static $ins = array(
        "Help" => null,
        "Config" => null
    );

    private static $error = null;
    private static $fileName = "brands";
    private static $secondName = "brand";

    private static $data;
    private static $url;

    private static $cons = null;

    public function __construct() {
        self::$cons = $this;
    }

    private static function init()
    {
        if (self::$cons == null) {
            self::$cons = new self();
        }
        return self::$cons;
    }

    /** TODO: Magento 1
     * @noinspection PhpUndefinedClassInspection
     */
    public static function getHelp()
    {
        if (self::$ins["Help"] == null) {
            self::$ins["Help"] = Mage::helper('mktr_tracker');
        }
        return self::$ins["Help"];
    }

    private static function status()
    {
        return self::$error == null;
    }

    /** @noinspection PhpUnused */
    public function indexAction()
    {
        self::$error = self::getHelp()->getFunc->isParamValid(array(
            'key' => 'Required|Key'
        ));

        if ($this->status())
        {
            return self::getHelp()->getFunc->readOrWrite(self::$fileName, self::$secondName, self::init());
        }

        return self::getHelp()->getFunc->Output('status', self::$error);
    }

    public static function freshData()
    {
        $brandAttribute = self::getHelp()->getConfig->getBrandAttribute();
        self::$url = self::getHelp()->getBaseUrl . 'catalogsearch/result/?q=';
        self::$data = array();
        foreach ($brandAttribute as $item) {

            foreach (self::getHelp()
                         ->getBrands
                         ->getAttribute(Mage_Catalog_Model_Product::ENTITY, $item)
                         ->getSource()->getAllOptions(true, true) as $option) {
                if (!empty($option['value'])) {
                    self::$data[] = array(
                        'name' => $option['label'],
                        'id' => $option['value'],
                        'url' => self::$url . $option['label']
                    );
                }
            }
        }

        return self::$data;
    }
}
