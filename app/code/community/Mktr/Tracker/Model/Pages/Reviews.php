<?php
/**
 * @copyright   © EAX LEX SRL. All rights reserved.
 * @license     http://opensource.org/licenses/osl-3.0.php - Open Software License (OSL 3.0)
 **/

class Mktr_Tracker_Model_Pages_Reviews
{
    private static $ins = array(
        "Help" => null,
        "Config" => null
    );

    private static $error = null;

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

    /** TODO: Magento 2 */
    public static function getStoreList()
    {
        if (self::$ins["Config"] == null) {
            self::$ins["Config"] = array();
            foreach (Mage::app()->getStores() as $k) {
                if (self::getHelp()->getConfig->getStoreValue("status", $k->getId()) &&
                    self::getHelp()->getConfig->getStoreValue("rest_key", $k->getId()) === self::getHelp()->getConfig->getRestKey())
                {
                    self::$ins["Config"][] = $k->getId();
                }
            }
        }
        return self::$ins["Config"];
    }

    private static function status()
    {
        return self::$error == null;
    }

    public function indexAction()
    {
        self::$error = self::getHelp()->getFunc->isParamValid(array(
            'key' => 'Required|Key',
            'start_date' => 'Required|DateCheck|StartDate'
        ));

        if (self::status())
        {
            return self::getHelp()->getFunc->Output('reviews', json_decode( json_encode($this->execute()), true));
        }
        return self::getHelp()->getFunc->Output('status', self::$error);
    }
    public function execute()
    {
        $t = self::getHelp()->getRequest->getParam("start_date");

        if (empty($t))
        {
            $t = date('Y-m-d');
        }

        $o = self::getHelp()->getApi->send("product_reviews", array('t' => strtotime($t)), false);

        $xml = simplexml_load_string($o->getContent(), 'SimpleXMLElement', LIBXML_NOCDATA);
        $rating = array(
            3 => array(1 => 11, 2 => 12, 3 => 13, 4 => 14, 5 => 15),
            2 => array(1 => 6, 2 => 7, 3 => 8, 4 => 9, 5 => 10),
            1 => array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5)
        );
        $added = array();

        $revStore = self::getHelp()->getData->{"reviewStore".self::getHelp()->getConfig->getRestKey()};
        foreach ($xml->review as $value){
            if (isset($value->review_date)) {
                if (!isset($revStore[(string) $value->review_id]))
                {

                    $review = Mage::getModel('review/review');
                    $review->setCreatedAt($value->review_date); //created date and time
                    $review->setEntityPkValue($value->product_id);//product id
                    $review->setStatusId(1); // status id
                    $review->setTitle(substr($value->review_text, 0, 40)); // review title
                    $review->setDetail($value->review_text); // review detail
                    $review->setEntityId(1); // leave it 1
                    $review->setStoreId(Mage::app()->getStore()->getId()); // store id

                    $customer = self::getHelp()->getCustomerData
                        ->setWebsiteId(self::getHelp()->getWebsite->getId())
                        ->loadByEmail($value->review_email);

                    if ($customer->getId() != null) {
                        $review->setCustomerId($customer->getId()); //null is for administrator
                    }

                    // $review->setCustomerId($_customerId); //null is for administrator
                    $review->setNickname($value->review_author); //customer nickname
                    $review->setReviewId($review->getId());//set current review id$value->review_id
                    $review->setStores(self::getStoreList()); //store id's
                    $review->save();

                    foreach($rating as $key=>$vv){
                        Mage::getModel('rating/rating')
                            ->setRatingId($key)
                            ->setReviewId($review->getId())
                            //$value->review_id
                            // ->setCustomerId($_customerId)
                            ->addOptionVote($vv[round(((int) $value->rating/2))], $value->product_id);
                    }
                    $review->aggregate();
                    $added[(string) $value->review_id] = $review->getId();
                } else {
                    $added[(string) $value->review_id] = self::getHelp()->getData->reviewStore[(string) $value->review_id];
                }
            }
        }

        self::getHelp()->getData->{"reviewStore".self::getHelp()->getConfig->getRestKey()} = $added;
        self::getHelp()->getData->save();
        return $xml;
    }
}
