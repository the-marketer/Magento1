<?php
/**
 * @copyright   Copyright (c) 2023 TheMarketer.com
 * @project     TheMarketer.com
 * @website     https://themarketer.com/
 * @author      Alexandru Buzica (EAX LEX S.R.L.) <b.alex@eax.ro>
 * @license     http://opensource.org/licenses/osl-3.0.php - Open Software License (OSL 3.0)
 * @docs        https://themarketer.com/resources/api
 */

class Mktr_Tracker_Model_Cron
{
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

    /** TODO: Magento 1 */
    public static function getStores()
    {
        if (self::$ins["Config"] == null) {
            self::$ins["Config"] = Mage::app()->getStores();
        }
        return self::$ins["Config"];
    }

    public function execute()
    {
        $upFeed = self::getHelp()->getData->update_feed;
        $upReview = self::getHelp()->getData->update_review;

        foreach (self::getStores() as $k)
        {
            if ($k->getId() != 0) {
                Mage::app()->setCurrentStore($k->getId());
                self::getHelp()->getConfig->setScopeCode($k->getId());
                self::getHelp()->getFunc->setStoreId($k->getId());

                if (self::getHelp()->getConfig->getStatus() != 0) {

                    if (self::getHelp()->getConfig->getCronFeed() != 0 && $upFeed < time())
                    {
                        self::getHelp()->getFunc->Write(self::getHelp()->getPagesFeed);
                        self::getHelp()->getData->update_feed = strtotime("+".self::getHelp()->getConfig->getUpdateFeed()." hour");
                    }

                    if (self::getHelp()->getConfig->getCronReview() != 0 && $upReview < time())
                    {
                        self::getHelp()->getPagesReviews->execute();
                        self::getHelp()->getData->update_review = strtotime("+".self::getHelp()->getConfig->getUpdateReview()." hour");
                    }
                }
            }
        }
        self::getHelp()->getData->save();
    }
}
