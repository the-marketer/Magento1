<?php
/**
 * @copyright   Copyright (c) 2023 TheMarketer.com
 * @project     TheMarketer.com
 * @website     https://themarketer.com/
 * @author      Alexandru Buzica (EAX LEX S.R.L.) <b.alex@eax.ro>
 * @license     http://opensource.org/licenses/osl-3.0.php - Open Software License (OSL 3.0)
 * @docs        https://themarketer.com/resources/api
 */

class Mktr_Tracker_Model_Pages_Orders
{
    private static $ins = array(
        "Help" => null,
        "Config" => null,
        "Subscriber" => null
    );

    private static $error = null;
    private static $params = null;
    private static $brandAttribute = null;
    private static $data = array();
    private static $imageLink = null;
    private static $fileName = "orders";
    private static $secondName = "order";

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

    private static function getProductImage($product)
    {
        if (self::$imageLink === null)
        {
            /** TODO: Magento 1 */
            self::$imageLink = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'catalog/product';
        }
        return self::$imageLink . $product->getImage();
    }

    public static function getOrderInfo($saveOrder)
    {
        $billingAddress = $saveOrder->getBillingAddress();

        $products = array();

        foreach ($saveOrder->getAllVisibleItems() as $item) {
            $pro = self::getHelp()->getProductRepo->load($item->getProductId());

            $pro->setStoreId(self::getHelp()->getFunc->getStoreId());

            /** TODO: Magento 2 - self::getHelp()->getTax->getTaxPrice  - Magento 1 - self::getHelp()->getTax->getPrice */
            $price = self::getHelp()->getFunc->digit2(
                self::getHelp()->getTax->getPrice($item, $item->getPrice(), true)
            );

            $sale_price = $item->getFinalPrice() > 0 ? self::getHelp()->getFunc->digit2(
                self::getHelp()->getTax->getPrice($item, $item->getFinalPrice(), true)
            ) : $price;

            $ct = self::getHelp()->getManager->buildMultiCategory($pro->getCategoryIds());

            $brand = '';
            foreach (self::$brandAttribute as $v)
            {
                $brand = $pro->getAttributeText($v);
                if (!empty($brand)) {
                    break;
                }
            }

            $products[] = array(
                'product_id' => $item->getProductId(),
                'name' => $item->getName(),
                'url' => $pro->getProductUrl(),
                'main_image' => self::getProductImage($pro),
                'category' => $ct,
                'brand' => $brand,
                'price' => $price,
                'sale_price' => $sale_price,
                'quantity' => (int) $item->getQtyOrdered(),
                'variation_id' => $pro->getId(),
                'variation_sku' => $item->getSku()
            );
        }

        $refund = self::getHelp()->getFunc->digit2($saveOrder->getTotalRefunded());
        $refund = empty($refund) ? 0 : $refund;
        $coupon = $saveOrder->getCouponCode();
        $coupon = empty($coupon) ? "" : $coupon;

        return array(
            "order_no" => $saveOrder->getIncrementId(),
            "order_status" => $saveOrder->getState(),
            "refund_value" => $refund,
            "created_at" => self::getHelp()->getFunc->correctDate($saveOrder->getCreatedAt()),
            "email_address" => $billingAddress->getEmail(),
            "phone" => self::getHelp()->getFunc->validateTelephone($billingAddress->getTelephone()),
            "firstname" => $billingAddress->getFirstname(),
            "lastname" => $billingAddress->getLastname(),
            "city" => $billingAddress->getCity(),
            "county" => $billingAddress->getRegion(),
            "address" => implode(" ", $billingAddress->getStreet()),
            "discount_value" => self::getHelp()->getFunc->digit2($saveOrder->getDiscountAmount()),
            "discount_code" => $coupon,
            "shipping" => self::getHelp()->getFunc->digit2($saveOrder->getShippingInclTax()),
            "tax" => self::getHelp()->getFunc->digit2($saveOrder->getTaxAmount()),// ->getFullTaxInfo()
            "total_value" => self::getHelp()->getFunc->digit2($saveOrder->getGrandTotal()),
            "products" => $products
        );
    }

    /** @noinspection PhpUnused */
    public function indexAction()
    {
        if (!self::getHelp()->getRequest->getParam("mime-type")) {
            self::getHelp()->getRequest->setParam("mime-type", 'json');
        }

        self::$error = self::getHelp()->getFunc->isParamValid(array(
            'key' => 'Required|Key|allow_export',
            'start_date' => 'Required|DateCheck|StartDate',
            'page' => null,
            'customerId' => null
        ));

        if ($this->status())
        {
            return self::getHelp()->getFunc->readOrWrite(self::$fileName, self::$secondName, self::init());
        }

        return self::getHelp()->getFunc->Output('status', self::$error);
    }

    public static function freshData()
    {
        $or = array();
        $stop = false;

        self::$params = self::getHelp()->getRequest->getParams();

        if (isset(self::$params['page']))
        {
            $stop = true;
        }

        self::$brandAttribute = self::getHelp()->getConfig->getBrandAttribute();
        self::$params['page'] = (int) (isset(self::$params['page']) ? self::$params['page'] : 1);
        self::$params['limit'] = (int) (isset(self::$params['limit']) ? self::$params['limit'] : 50);

        self::$data['startDate'] = date(
            self::getHelp()->getConfig->getDateStart(),
            strtotime(self::$params['start_date'])
        );

        self::$data['endDate'] = date(
            self::getHelp()->getConfig->getDateEnd(),
            !isset(self::$params['end_date']) ? time() : strtotime(self::$params['end_date'])
        );

        self::$data['Orders'] = self::getHelp()->getOrderRepo->getCollection()
            ->addFieldToFilter('store_id',array('in', self::getHelp()->getFunc->getStoreId()))
            ->addAttributeToFilter('created_at', array('from' => self::$data['startDate'], 'to' => self::$data['endDate']))
            ->setPageSize(self::$params['limit'])
            ->setOrder('created_at','ASC');
        // ->addStoreFilter(self::getHelp()->getFunc->getStoreId());

        if ($stop) {
            $pages = self::$params['page'];
        } else {
            $pages = self::$data['Orders']->getLastPageNumber();
        }

        do {
            self::$data['Orders']->setCurPage(self::$params['page'])->load();

            if (self::$params['page'] == self::$data['Orders']->getCurPage()) {
                foreach (self::$data['Orders'] as $orders) {
                    $or[] = self::getOrderInfo($orders);
                }
            }

            self::$params['page']++;
            self::$data['Orders']->clear();

        } while (self::$params['page'] <= $pages);

        return $or;
    }
}
