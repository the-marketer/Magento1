<?php
/**
 * @copyright   Copyright (c) 2023 TheMarketer.com
 * @project     TheMarketer.com
 * @website     https://themarketer.com/
 * @author      Alexandru Buzica (EAX LEX S.R.L.) <b.alex@eax.ro>
 * @license     http://opensource.org/licenses/osl-3.0.php - Open Software License (OSL 3.0)
 * @docs        https://themarketer.com/resources/api
 */

class Mktr_Tracker_Observer_Events
{
    private static $observer = null;
    private static $eventName = null;
    private static $eventAction = null;
    private static $eventData = array();

    private static $observerEvents = array(
        "checkout_cart_product_add_after" => "addToCart",
        "sales_quote_remove_item" => "removeFromCart",
        "wishlist_add_product" => "addToWishlist",
        "controller_action_predispatch_wishlist_index_remove" => "removeFromWishlist",
        "checkout_onepage_controller_success_action" => "saveOrder",
        /** TODO: Magento 1 **/
        "sales_order_place_after" => "saveOrder",
        "multishipping_checkout_controller_success_action" => "saveOrder",
        "model_save_after" => "emailAndPhone",
        "customer_register_success" => "Register",
        "customer_login" => "RegisterOrLogIn",
        /* "review_controller_product_init_after" => "Review", */
        "admin_system_config_changed_section_mktr_tracker" => "SaveButton",
        "sales_order_save_after" => "UpdateOrder"
    );

    private static $ins = array(
        "Help" => null,
        "Config" => null
    );

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

    /** @noinspection PhpUnused */
    public function execute($observer)
    {
        self::$eventAction = $this;
        self::$observer = $observer;

        self::$eventName = $this->getObserverEvents($observer->getEvent()->getName());
        if (!empty(self::$eventName)) {
            $this->{self::$eventName}();
        }
        return true;
    }

    public static function getObserverEvents($name = null)
    {
        if ($name == null)
        {
            return self::$observerEvents;
        }
        $ev = self::$observerEvents;
        if (isset($ev[$name])) {
            return $ev[$name];
        }
        return null;
    }

    /** @noinspection PhpUnused */
    public function addToCart()
    {
        $variant = self::$observer->getEvent()->getQuoteItem()->getOptionByCode('simple_product');

        if ($variant == null)
        {
            $variant = self::$observer->getQuoteItem();
        }

        self::$eventData = array(
            'product_id' => self::$observer->getEvent()->getProduct()->getId(),
            'quantity'=> (int) self::$observer->getQuoteItem()->getQty(),
            'variation' => array(
                'id' => $variant->getProduct()->getId(),
                'sku' => $variant->getProduct()->getSku()
            )
        );

        self::MktrSessionSet();
    }

    /** @noinspection PhpUnused */
    public function removeFromCart()
    {
        $product = self::$observer->getQuoteItem();

        $variant = self::$observer
            ->getEvent()
            ->getQuoteItem()
            ->getOptionByCode('simple_product');

        if ($variant)
        {
            $variant = self::$observer->getQuoteItem();
        }

        self::$eventData = array(
            'product_id' => $product->getProductId(),
            'quantity'=> (int) self::$observer->getQuoteItem()->getQty(),
            'variation' => array(
                'id' => $variant->getProduct()->getId(),
                'sku' => $variant->getProduct()->getSku()
            )
        );

        self::MktrSessionSet();
    }

    /** @noinspection PhpUnused */
    public function addToWishList()
    {
        $product = self::$observer->getItem()->getOptionByCode('simple_product');

        $ID = self::$observer->getEvent()->getProduct()->getId();

        if ($product == null) {
            $valueID = $ID;
        } else {
            $valueID = $product->getValue();
        }

        self::$eventData = array(
            'product_id' => $ID,
            'variation' => array(
                'id' => $valueID,
                'sku' => self::getHelp()->getProductRepo->load($valueID)->getSku()
            )
        );

        self::MktrSessionSet();
    }

    /** @noinspection PhpUnused */
    public function removeFromWishlist()
    {
        $item = self::getHelp()->getWishItem->loadWithOptions(self::getHelp()->getRequest->getParam('item'));

        $ID = $item->getProductId();
        $product = $item->getOptionByCode('simple_product');

        if ($product === null) {
            $valueID = $ID;
        } else {
            $valueID = $product->getProductId();
        }

        self::$eventData = array(
            'product_id' => $ID,
            'variation' => array(
                'id' => $valueID,
                'sku' => self::getHelp()->getProductRepo->load($valueID)->getSku()
            )
        );

        self::MktrSessionSet();
    }

    /** @noinspection PhpUnused */
    public function saveOrder()
    {
        $saveOrder = self::$observer->getOrder();

        if (self::getHelp()->getMageVersion > "1.4.2.0")
        {
            $billingAddress = $saveOrder->getbillingAddress();
        } else {
            $billingAddress = $saveOrder->getBillingAddress();
        }

        $products = array();

        foreach ($saveOrder->getAllVisibleItems() as $item) {
            $products[] = array(
                'product_id' => $item->getProductId(),
                /** TODO: Magento 2 - self::getHelp()->getTax->getTaxPrice  - Magento 1 - self::getHelp()->getTax->getPrice */
                'price' => self::getHelp()->getFunc->digit2( self::getHelp()->getTax->getPrice($item, $item->getPrice(), true) ),
                'quantity' => (int) $item->getQtyOrdered(),
                'variation_sku' => $item->getSku()
            );
        }
        $couponCode = $saveOrder->getCouponCode();

        if ($couponCode == null)
        {
            $couponCode = '';
        }

        self::$eventData = array(
            "number" => $saveOrder->getIncrementId(),
            "email_address" => $billingAddress->getEmail(),
            "phone" => self::getHelp()->getFunc->validateTelephone($billingAddress->getTelephone()),
            "firstname" => $billingAddress->getFirstname(),
            "lastname" => $billingAddress->getLastname(),
            "city" => $billingAddress->getCity(),
            "county" => $billingAddress->getRegion(),
            "address" => implode(" ", $billingAddress->getStreet()),
            "discount_value" => self::getHelp()->getFunc->digit2($saveOrder->getDiscountAmount()),
            "discount_code" => $couponCode,
            "shipping" => self::getHelp()->getFunc->digit2($saveOrder->getShippingInclTax()),
            "tax" => self::getHelp()->getFunc->digit2($saveOrder->getTaxAmount()),// ->getFullTaxInfo()
            "total_value" => self::getHelp()->getFunc->digit2($saveOrder->getGrandTotal()),
            "products" => $products
        );

        self::MktrSessionSet();
    }

    /** @noinspection PhpUnused */
    public function emailAndPhone()
    {
        $object = self::$observer->getObject();

        /** TODO: Magento 2 - Subscriber - Magento 1 - Mage_Newsletter_Model_Subscriber*/
        /** @noinspection PhpUndefinedClassInspection */
        if ($object instanceof Mage_Newsletter_Model_Subscriber) {

            $tApi = self::getHelp()->getSessionName."Api";
            self::getHelp()->getSession->{"set".$tApi}([ 'Sub' => true ]);


            if ($object->getEmail() === null) {
                $object = self::getHelp()->getCustomerSession->getCustomer();
            }

            if (!$object->getDefaultShipping()) {
                $object1 = self::getHelp()->getCustomerData
                    ->setWebsiteId(self::getHelp()->getWebsite->getId())
                    ->loadByEmail($object->getEmail());
                if ($object1->getEmail() !== null) {
                    $object = $object1;
                }
            }

            $this->EmailSet($object);

            if ($object->getDefaultShipping()) {
                self::$eventName = "setPhone";

                $customerAddress = self::getHelp()->getCustomerAddress->load($object->getDefaultShipping());

                self::$eventData = array(
                    'phone' => self::getHelp()->getFunc->validateTelephone($customerAddress->getTelephone())
                );

                self::MktrSessionSet();
            }
        }
    }
    /** @noinspection PhpUnused */
    public function Register()
    {
        $tApi = self::getHelp()->getSessionName."Api";
        
        self::getHelp()->getSession->{"set".$tApi}([ 'Sub' => Mage::app()->getRequest()->getParam('is_subscribed') ]);
        
        $customer = self::$observer->getCustomer();

        $this->EmailSet($customer);

        if ($customer->getDefaultShipping()) {
            self::$eventName = "setPhone";
            $address = self::getHelp()->getCustomerAddress->load($customer->getDefaultShipping());

            self::$eventData = array(
                'phone' => self::getHelp()->getFunc->validateTelephone($address->getTelephone())
            );

            self::MktrSessionSet();
        }
    }

    /** @noinspection PhpUnused */
    public function RegisterOrLogIn()
    {
        $customer = self::$observer->getCustomer();

        $this->EmailSet($customer);

        if ($customer->getDefaultShipping()) {
            self::$eventName = "setPhone";
            $address = self::getHelp()->getCustomerAddress->load($customer->getDefaultShipping());

            self::$eventData = array(
                'phone' => self::getHelp()->getFunc->validateTelephone($address->getTelephone())
            );

            self::MktrSessionSet();
        }
    }

    public function EmailSet($object)
    {
        $emailData = array(
            'email_address' => $object->getEmail()
        );

        $fName = $object->getFirstname();
        $lName = $object->getLastname();

        if ($fName) {
            $emailData['firstname'] = $fName;
        }
        if ($lName) {
            $emailData['lastname'] = $lName;
        }
        self::$eventName = "setEmail";

        self::$eventData = $emailData;

        self::MktrSessionSet();
    }

    /** @noinspection PhpUnused */
    public function SaveButton()
    {
        $module = self::getHelp()->getFileSystem->setWorkDirectory();

        if (self::getHelp()->getConfig->getPushStatus() != 0) {
            $module->writeFile("firebase-config.js", self::getHelp()->getConfig->getFireBase());
            $module->writeFile("firebase-messaging-sw.js", self::getHelp()->getConfig->getFireBaseMessaging());
        } else {
            $module->deleteFile("firebase-config.js");
            $module->deleteFile("firebase-messaging-sw.js");
        }
    }

    /** @noinspection PhpUnused */
    public function UpdateOrder()
    {
        $o = self::$observer->getEvent()->getOrder();
        $status = $o->getState();

        $send = array(
            'order_number' => $o->getIncrementId(),
            'order_status' => $status
        );

        self::getHelp()->getApi->send("update_order_status", $send, false);
    }

    /** @noinspection PhpReturnValueOfMethodIsNeverUsedInspection */
    private static function MktrSessionSet()
    {
        $fName = "set".self::getHelp()->getSessionName.self::$eventName;

        self::getHelp()->getSession->{$fName}(self::$eventData);
        return self::$eventAction;
    }
}
