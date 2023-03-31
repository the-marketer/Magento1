<?php
/**
 * @copyright   Copyright (c) 2023 TheMarketer.com
 * @project     TheMarketer.com
 * @website     https://themarketer.com/
 * @author      Alexandru Buzica (EAX LEX S.R.L.) <b.alex@eax.ro>
 * @license     http://opensource.org/licenses/osl-3.0.php - Open Software License (OSL 3.0)
 * @docs        https://themarketer.com/resources/api
 */

class Mktr_Tracker_Helper_Data extends Mage_Core_Helper_Abstract
{
    const sessionName = "Mk";
    const space = "\n        ";

    private static $ins = array(
        "getFunc" => array(null,'model','mktr_tracker/Func'),
        "getConfig" => array(null,'model','mktr_tracker/Config'),
        "getApi" => array(null,'model','mktr_tracker/Api'),
        "getFileSystem" => array(null,'model','mktr_tracker/FileSystem'),
        "getManager" => array(null,'model','mktr_tracker/Manager'),
        "getArray2XML" => array(null,'model','mktr_tracker/Array2XML'),
        "getData" => array(null, 'model', 'mktr_tracker/Data'),
        "getPagesReviews" => array(null, 'model', 'mktr_tracker/Pages_Reviews'),
        "getPagesFeed" => array(null, 'model', 'mktr_tracker/Pages_Feed'),
        "getRequest" => array(null,'app','getRequest'),
        "getWebsite" => array(null,'app','getWebsite'),
        "getStore" => array(null,'app','getStore'),
        "getCustomerSession" => array(null,'singleton','customer/session'),
        "getCustomerData" => array(null,'model','customer/customer'),
        "getCustomerAddress" => array(null,'model','customer/address'),
        "getOrderRepo" => array(null,'model','sales/order'),
        "getProduct" => array(null,'model','catalog/product'),
        "getProductRepo" => array(null,'model','catalog/product'),
        "getProductCol" => array(null,'model','catalog/product'),
        /** TODO Magento 1 */
        "getProductType" => array(null,'model','catalog/product_type_configurable'),
        "getStockRepo" => array(null,'model','cataloginventory/stock_item'),
        "getBrands" => array(null,'model','eav/config'),
        "getCategoryRepo" => array(null,'model', 'catalog/category'),
        "getCategoriesData" => array(null,'helper', 'catalog/category'),
        "getWishItem" => array(null,'model','wishlist/item'),
        "getTax" => array(null,'helper','tax'),
        "getSession" => array(null,'singleton','core/session'),
        "getBaseUrl" => array(null,'mage','getBaseUrl'),
        "getMageVersion" => array(null,'mage','getVersion'),
        "getSpace" => array(null,'self','getSpace'),
        "getSessionName" => array(null,'self','getSessionName'),
        "getRegistry" => array(null,'mage','getRegistry'),
        "getPageRaw" => array(null, 'app', 'getResponse')
    );

    public function __get($property) {
        if (self::$ins[$property][0] == null) {
            if (self::$ins[$property][1] == 'model')
            {
                self::$ins[$property][0] = Mage::getModel(self::$ins[$property][2]);
            } else if (self::$ins[$property][1] == 'app')
            {
                self::$ins[$property][0] = Mage::app()->{self::$ins[$property][2]}();
            } else if (self::$ins[$property][1] == 'singleton')
            {
                self::$ins[$property][0] = Mage::getSingleton(self::$ins[$property][2]);
            } else if (self::$ins[$property][1] == 'helper')
            {
                self::$ins[$property][0] = Mage::helper(self::$ins[$property][2]);
            } else if (self::$ins[$property][1] == 'mage')
            {
                self::$ins[$property][0] = Mage::{self::$ins[$property][2]}();
            } else {
                self::$ins[$property][0] = self::{self::$ins[$property][2]}();
            }
        }
        return self::$ins[$property][0];
    }

    /** @noinspection PhpUnused */
    public static function getRegistry($registryName)
    {
        /** @noinspection PhpUndefinedClassInspection */
        return Mage::registry($registryName);
    }

    /** @noinspection PhpUnused */
    public static function getSpace(){
        return self::space;
    }

    /** @noinspection PhpUnused */
    public static function getSessionName(){
        return self::sessionName;
    }
}
