<?php
/**
 * @copyright   © EAX LEX SRL. All rights reserved.
 * @license     http://opensource.org/licenses/osl-3.0.php - Open Software License (OSL 3.0)
 **/

class Mktr_Tracker_Model_Pages_SetEmail
{
    private static $ins = array(
        "Help" => null
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

    /** @noinspection PhpUnused */
    public function indexAction()
    {
        $result = self::getHelp()->getPageRaw;
        $result->setHeader('Content-type', 'application/javascript; charset=utf-8;', 1);

        $lines = "";
        $fName = vsprintf(self::getHelp()->getSessionName, array('setEmail'));
        $sEmail = self::getHelp()->getSession->{"get".$fName}();

        if ($sEmail !== null) {
            /** @noinspection DuplicatedCode */
            $nws = self::getSubscriber()->loadByEmail($sEmail["email_address"]);

            $info = array(
                "email" => $sEmail['email_address']
            );
            // var_dump($fName,$sEmail,$sEmail["email_address"],$nws->getStatus(), $nws);
            // die();
            if ($nws && $nws->getStatus() == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED)
            {
                $customer = self::getHelp()->getCustomerData
                    ->setWebsiteId(self::getHelp()->getWebsite->getId())
                    ->loadByEmail($sEmail['email_address']);
                $customerAddressId = $customer->getDefaultShipping();
                if ($customerAddressId) {
                    $address = self::getHelp()->getCustomerAddress
                        ->load($customer->getDefaultShipping());

                    $customerData = $address->getData();
                    $info["phone"] = self::getHelp()->getFunc->validateTelephone($customerData['telephone']);
                }
                if ($customer->getName() !== null && $customer->getName() !== ' ') {
                    $info["name"] = $customer->getName();
                } else if ($customer->getFirstname() === null && $customer->getLastname() === null) {
                    $info["name"] = explode("@",$customer->getEmail())[0];
                } else if ($customer->getFirstname() !== null && $customer->getLastname() !== null) {
                    $info["name"] = $customer->getFirstname().' '.$customer->getLastname();
                } else if ($customer->getFirstname() !== null) {
                    $info["name"] = $customer->getFirstname();
                } else  if ($customer->getLastname() !== null) {
                    $info["name"] = $customer->getLastname();
                } else {
                    $info["name"] = explode("@",$sEmail['email'])[0];
                }
                self::getHelp()->getApi->send("add_subscriber", $info);

                $lines = "setEmailAdd";
            } else {
                self::getHelp()->getApi->send("remove_subscriber", $info);
                $lines = "setEmailRemove";
            }

            if (self::getHelp()->getApi->getStatus() == 200)
            {
                $fNameP = vsprintf(self::getHelp()->getSessionName, array('setPhone'));
                if (self::getHelp()->getSession->{"get".$fNameP}()) {
                    self::getHelp()->getSession->{"uns".$fNameP}();
                }
                self::getHelp()->getSession->{"uns".$fName}();
            }

            /** TODO Magento 1 - setBody() | Magento 2 - setContents()  */
            $result->setBody("console.log('".$lines."', '".
                self::getHelp()->getApi->getStatus()."', '".
                self::getHelp()->getApi->getBody()."', '".
                self::getHelp()->getApi->getUrl()."','".
                json_encode(self::getHelp()->getApi->getParam())."');");
        }

        return $result;
    }
}
