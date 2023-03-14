<?php
/**
 * @copyright   © EAX LEX SRL. All rights reserved.
 * @license     http://opensource.org/licenses/osl-3.0.php - Open Software License (OSL 3.0)
 **/

class Mktr_Tracker_Model_Pages_SaveOrder
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

    /** TODO: Magento 1 */
    public static function getSubscriber()
    {
        if (self::$ins["Subscriber"] == null) {
            self::$ins["Subscriber"] = Mage::getModel('newsletter/subscriber');
        }
        return self::$ins["Subscriber"];
    }

    public function indexAction()
    {
        $result = self::getHelp()->getPageRaw;
        $result->setHeader('Content-type', 'application/javascript; charset=utf-8;', 1);
        $fName = vsprintf(self::getHelp()->getSessionName, array('saveOrder'));
        $sOrder = self::getHelp()->getSession->{"get".$fName}();

        if ($sOrder !== null) {
            self::getHelp()->getApi->send("save_order", $sOrder);

            $nws = self::getSubscriber()->loadByEmail($sOrder["email_address"]);

            if ($nws && $nws->getStatus() == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED)
            {
                if (!empty($sOrder["email_address"]))
                {
                    $fNameS = "set".vsprintf(self::getHelp()->getSessionName, array('setEmail'));
                    self::getHelp()->getSession->{$fNameS}(
                        self::getHelp()->getManager->schemaValidate(
                            $sOrder, self::getHelp()->getManager->getEventsSchema('setEmail')
                        )
                    );
                }

                if (!empty($sOrder["phone"]))
                {
                    $fNameS = "set".vsprintf(self::getHelp()->getSessionName, array('setPhone'));
                    self::getHelp()->getSession->{$fNameS}(array('phone' => $sOrder["phone"]));
                }
            }
            if (self::getHelp()->getApi->getStatus() == 200)
            {
                self::getHelp()->getSession->{"uns".$fName}();
            }
            /** TODO Magento 1 - setBody() | Magento 2 - setContents()  */
            $result->setBody("console.log('SaveOrder', '".
                self::getHelp()->getApi->getStatus()."', '".
                self::getHelp()->getApi->getBody()."', '".
                self::getHelp()->getApi->getUrl()."');");
        }

        return $result;
    }
}
