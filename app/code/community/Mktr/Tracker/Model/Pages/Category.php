<?php
/**
 * @copyright   © EAX LEX SRL. All rights reserved.
 * @license     http://opensource.org/licenses/osl-3.0.php - Open Software License (OSL 3.0)
 **/

class Mktr_Tracker_Model_Pages_Category
{
    private static $ins = array(
        "Help" => null,
        "Config" => null
    );

    private static $error = null;
    private static $params = null;
    private static $fileName = "categories";
    private static $secondName = "category";

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
        self::$params = self::getHelp()->getRequest->getParams();
        self::$error = self::getHelp()->getFunc->isParamValid(array(
                'key' => 'Required|Key'
            ));

        if ($this->status())
        {
            return self::getHelp()->getFunc->readOrWrite(self::$fileName, self::$secondName, self::init());
        }

        return self::getHelp()->getFunc->Output('status', self::$error);
    }

    public static function hierarchy($category)
    {
        $breadcrumb = array($category->getName());

        while ($category->getLevel() > 2) {
            $category = self::getHelp()->getCategoryRepo->load($category->getParentId());
            $breadcrumb[] = $category->getName();
        }
        $breadcrumb = array_reverse($breadcrumb);
        return implode("|", $breadcrumb);
    }

    public static function build($category){

        $newList = array(
            "name" => $category->getName(),
            "url" => self::$url. $category->getUrlPath().'.html',
            'id'=> $category->getId(),
            "hierarchy" => self::hierarchy($category),
            "image_url" => $category->getImageUrl()
        );

        if (empty($newList["image_url"]))
        {
            unset($newList["image_url"]);
        }

        self::$data[] = $newList;
    }

    public static function freshData()
    {
        $categories = self::getHelp()->getCategoriesData->getStoreCategories(false,true,true);
        self::$data = array();
        self::$url = self::getHelp()->getBaseUrl;
        foreach ($categories as $category) {
            $cat = self::getHelp()->getCategoryRepo->load($category->getId());
            self::build($cat);
        }

        return self::$data;
    }
}
