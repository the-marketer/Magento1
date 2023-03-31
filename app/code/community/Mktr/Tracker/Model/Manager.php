<?php
/**
 * @copyright   Copyright (c) 2023 TheMarketer.com
 * @project     TheMarketer.com
 * @website     https://themarketer.com/
 * @author      Alexandru Buzica (EAX LEX S.R.L.) <b.alex@eax.ro>
 * @license     http://opensource.org/licenses/osl-3.0.php - Open Software License (OSL 3.0)
 * @docs        https://themarketer.com/resources/api
 */

class Mktr_Tracker_Model_Manager
{
    private static $data = array();
    private static $assets = array();
    private static $bMultiCat = array();
    private static $cons = null;

    private static $ins = array(
        "Help" => null,
        "Config" => null
    );

    private static $shName = null;

    private static $eventsName = array(
        "__sm__view_homepage" =>"HomePage",
        "__sm__view_category" => "Category",
        "__sm__view_brand" => "Brand",
        "__sm__view_product" => "Product",
        "__sm__add_to_cart" => "addToCart",
        "__sm__remove_from_cart" => "removeFromCart",
        "__sm__add_to_wishlist" => "addToWishlist",
        "__sm__remove_from_wishlist" => "removeFromWishlist",
        "__sm__initiate_checkout" => "Checkout",
        "__sm__order" => "saveOrder",
        "__sm__search" => "Search",
        "__sm__set_email" => "setEmail",
        "__sm__set_phone" => "setPhone"
    );

    private static $eventsSchema = array(
        "HomePage" => null,
        "Checkout" => null,
        "Cart" => null,

        "Category" => array(
            "category" => "category"
        ),

        "Brand" => array(
            "name" => "name"
        ),

        "Product" => array(
            "product_id" => "product_id"
        ),

        "Search" => array(
            "search_term" => "search_term"
        ),

        "setPhone" => array(
            "phone" => "phone"
        ),

        "addToWishlist" => array(
            "product_id" => "product_id",
            "variation" => array(
                "@key" => "variation",
                "@schema" => array(
                    "id" => "id",
                    "sku" => "sku"
                )
            )
        ),

        "removeFromWishlist" => array(
            "product_id" => "product_id",
            "variation" => array(
                "@key" => "variation",
                "@schema" => array(
                    "id" => "id",
                    "sku" => "sku"
                )
            )
        ),

        "addToCart" => array(
            "product_id" => "product_id",
            "quantity" => "quantity",
            "variation" => array(
                "@key" => "variation",
                "@schema" => array(
                    "id" => "id",
                    "sku" => "sku"
                )
            )
        ),

        "removeFromCart" => array(
            "product_id" => "product_id",
            "quantity" => "quantity",
            "variation" => array(
                "@key" => "variation",
                "@schema" => array(
                    "id" => "id",
                    "sku" => "sku"
                )
            )
        ),

        "saveOrder" => array(
            "number" => "number",
            "email_address" => "email_address",
            "phone" => "phone",
            "firstname" => "firstname",
            "lastname" => "lastname",
            "city" => "city",
            "county" => "county",
            "address" => "address",
            "discount_value" => "discount_value",
            "discount_code" => "discount_code",
            "shipping" => "shipping",
            "tax" => "tax",
            "total_value" => "total_value",
            "products" => array(
                "@key" => "products",
                "@schema" =>
                    array(
                        "product_id" => "product_id",
                        "price" => "price",
                        "quantity" => "quantity",
                        "variation_sku" => "variation_sku"
                    )
            )
        ),

        "setEmail" => array(
            "email_address" => "email_address",
            "firstname" => "firstname",
            "lastname" => "lastname"
        )
    );

    /** TODO: Magento 1 */
    public function __construct()
    {
        self::$cons = $this;
    }


    /** TODO: Magento 1 */
    public static function getHelp()
    {
        if (self::$ins["Help"] == null) {
            /** @noinspection PhpUndefinedClassInspection */
            self::$ins["Help"] = Mage::helper('mktr_tracker');
        }
        return self::$ins["Help"];
    }

    /** @noinspection PhpUnused */
    public static function getEvent($Name, $eventData = array())
    {
        if (empty(self::$eventsName[$Name])) {
            return false;
        }

        self::$shName = self::$eventsName[$Name];

        self::$data = array(
            "event" => $Name
        );

        self::$assets = array();

        switch (self::$shName){
            case "Category":
                self::$assets['category'] = self::buildCategory(self::getHelp()->getRegistry('current_category'));
                break;
            case "Product":
                self::$assets['product_id'] = self::getHelp()->getRegistry('current_product')->getId();
                break;
            case "Search":
                self::$assets['search_term'] = self::getHelp()->getRequest->getParam('q');
                break;
            default:
                self::$assets = $eventData;
        }

        self::$assets = self::schemaValidate(self::$assets, self::$eventsSchema[self::$shName]);

        self::build();

        /** TODO: Magento 1 */
        if (self::$cons == null)
        {
            return new self();
        } else {
            return self::$cons;
        }
    }

    public static function getEventsSchema($sName = null)
    {
        return $sName === null ? self::$eventsSchema : self::$eventsSchema[$sName];
    }

    /** @noinspection PhpUnused */
    public static function schemaValidate($array, $schema)
    {
        $newOut = array();

        foreach ($array as $key=>$val) {
            if (isset($schema[$key])){
                if (is_array($val)) {
                    $newOut[$schema[$key]["@key"]] = self::schemaValidate($val, $schema[$key]["@schema"]);
                } else {
                    $newOut[$schema[$key]] = $val;
                }
            } else if (is_array($val)){
                $newOut[] = self::schemaValidate($val, $schema);
            }
        }

        return $newOut;
    }

    public static function buildMultiCategory($List)
    {
        self::$bMultiCat = array();
        foreach ($List as $value) {
            $categoryRegistry = self::getHelp()->getCategoryRepo->load($value);
            self::buildSingleCategory($categoryRegistry);
        }

        if (empty(self::$bMultiCat))
        {
            self::$bMultiCat[] = "Default Category";
        }
        return implode("|", array_reverse(self::$bMultiCat));
    }

    public static function buildSingleCategory($categoryRegistry)
    {
        if ($categoryRegistry->getId() != 2)
        {
            self::$bMultiCat[] = $categoryRegistry->getName();

            while ($categoryRegistry->getLevel() > 2) {

                $categoryRegistry = self::getHelp()->getCategoryRepo->load($categoryRegistry->getParentId());

                self::$bMultiCat[] = $categoryRegistry->getName();
            }
        }
    }

    public static function buildCategory($categoryRegistry)
    {
        if ($categoryRegistry->getId() != 2)
        {
            $build = array($categoryRegistry->getName());
            while ($categoryRegistry->getLevel() > 2) {

                $categoryRegistry = self::getHelp()->getCategoryRepo->load($categoryRegistry->getParentId());

                $build[] = $categoryRegistry->getName();
            }

            return implode("|", array_reverse($build));
        }
        return "Default Category";
    }

    public static function build()
    {
        foreach (self::$assets as $key=>$val) {
            self::$data[$key] = $val;
        }
    }

    public function toJson(){
        return self::getHelp()->getFunc->toJson(self::$data);
    }
}
