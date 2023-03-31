<?php
/**
 * @copyright   Copyright (c) 2023 TheMarketer.com
 * @project     TheMarketer.com
 * @website     https://themarketer.com/
 * @author      Alexandru Buzica (EAX LEX S.R.L.) <b.alex@eax.ro>
 * @license     http://opensource.org/licenses/osl-3.0.php - Open Software License (OSL 3.0)
 * @docs        https://themarketer.com/resources/api
 */

class Mktr_Tracker_ApiController extends Mage_Core_Controller_Front_Action
{
    private static $ins = array(
        "Help" => null,
        "Config" => null
    );

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

    public function TestAction()
    {

        $upFeed = self::getHelp()->getData->update_feed;
        $upReview = self::getHelp()->getData->update_review;

        foreach (self::getStores() as $k)
        {
            if ($k->getId() != 0) {
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
        // $module->writeFile("data.json", self::getHelp()->getFunc->toJson($data));
        /*$module = self::getHelp()->getFileSystem->setWorkDirectory("Storage");

                $data = $module->rFile("data.json");

                if ($data !== null) {
                    $data = json_decode($data, true);

                    if (!isset($data['update_feed']))
                    {
                        $data['update_feed'] = 0;
                    }

                    if (!isset($data['update_review']))
                    {
                        $data['update_review'] = 0;
                    }
                } else {
                    $data = array('update_feed' => 0, 'update_review'=> 0);
                }*/
        /*
$data['Out'][] = [
                        $k->getId(),
                        $k->getGroupId(),
                        self::getHelp()->getConfig->getStatus(),
                        // self::getHelp()->getConfig->getScopeCode(),
                        // Mage::getStoreConfig("mktr_tracker/tracker/status", $k->getId())
                    ];
        // $data = $module->rFile("cron.txt");self::getHelp()->getConfig->getValue("mktr_tracker")
        $data = array();
        foreach (Mage::app()->getStores() as $k)
        {
            $data[] = [$k->getId(), $k->getGroupId()];
        }*/

        self::getHelp()->getRequest->setParam("mime-type", 'json');

        return self::getHelp()->getFunc->Output('status', self::getHelp()->getData->getData());
    }

    /** @noinspection PhpUnused */
    public function LoadEventsAction()
    {
        /** @noinspection PhpUndefinedClassInspection */
        return Mage::getModel('mktr_tracker/Pages_LoadEvents')->indexAction($this);
    }

    /** @noinspection PhpUnused */
    public function SaveOrderAction()
    {
        /** @noinspection PhpUndefinedClassInspection */
        return Mage::getModel('mktr_tracker/Pages_SaveOrder')->indexAction($this);
    }

    /** @noinspection PhpUnused */
    public function setEmailAction()
    {
        /** @noinspection PhpUndefinedClassInspection */
        return Mage::getModel('mktr_tracker/Pages_SetEmail')->indexAction($this);
    }

    /** @noinspection PhpUnused */
    public function OrdersAction()
    {
        /** @noinspection PhpUndefinedClassInspection */
        return Mage::getModel('mktr_tracker/Pages_Orders')->indexAction($this);
    }

    /** @noinspection PhpUnused */
    public function CategoryAction()
    {
        /** @noinspection PhpUndefinedClassInspection */
        return Mage::getModel('mktr_tracker/Pages_Category')->indexAction($this);
    }

    /** @noinspection PhpUnused */
    public function BrandsAction()
    {
        /** @noinspection PhpUndefinedClassInspection */
        return Mage::getModel('mktr_tracker/Pages_Brands')->indexAction($this);
    }

    /** @noinspection PhpUnused */
    public function FeedAction()
    {
        /** @noinspection PhpUndefinedClassInspection */
        return Mage::getModel('mktr_tracker/Pages_Feed')->indexAction($this);
    }

    /** @noinspection PhpUnused */
    public function CodeGeneratorAction()
    {
        /** @noinspection PhpUndefinedClassInspection */
        return Mage::getModel('mktr_tracker/Pages_CodeGenerator')->indexAction($this);
    }

    /** @noinspection PhpUnused */
    public function ReviewsAction()
    {
        /** @noinspection PhpUndefinedClassInspection */
        return Mage::getModel('mktr_tracker/Pages_Reviews')->indexAction($this);
    }
}
