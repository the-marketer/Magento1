<?php
/**
 * @copyright   Copyright (c) 2023 TheMarketer.com
 * @project     TheMarketer.com
 * @website     https://themarketer.com/
 * @author      Alexandru Buzica (EAX LEX S.R.L.) <b.alex@eax.ro>
 * @license     http://opensource.org/licenses/osl-3.0.php - Open Software License (OSL 3.0)
 * @docs        https://themarketer.com/resources/api
 */

class Mktr_Tracker_Model_Pages_LoadEvents
{
    private static $ins = array(
        "Help" => null,
        "Subscriber" => null
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

    public function indexAction()
    {
        $lines = array();

        foreach (self::getHelp()->getConfig->getEventsObs() as $event=>$Name)
        {
            if (!$Name[0]) {
                $fName = "get".self::getHelp()->getSessionName.$event;

                $eventData = self::getHelp()->getSession->{$fName}();

                if ($eventData) {
                    $lines[] = "dataLayer.push(".self::getHelp()->getManager->getEvent($Name[1], $eventData)->toJson().");";

                    $uName = "uns".self::getHelp()->getSessionName.$event;
                    self::getHelp()->getSession->{$uName}();
                }
            }
        }

        $result = self::getHelp()->getPageRaw;
        $result->setHeader('Content-type', 'application/javascript; charset=utf-8;', 1);
        /** TODO Magento 1 - setBody() | Magento 2 - setContents()  */
        $result->setBody(implode(self::getHelp()->getSpace(), $lines).PHP_EOL);
        return $result;
    }
}
