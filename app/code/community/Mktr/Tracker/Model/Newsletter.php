<?php
/**
 * @copyright   © EAX LEX SRL. All rights reserved.
 * @license     http://opensource.org/licenses/osl-3.0.php - Open Software License (OSL 3.0)
 **/

class Mktr_Tracker_Model_Newsletter extends Mage_Newsletter_Model_Subscriber
{
    private static $Mktr = null;

    /** TODO: Magento 1 */
    public static function getHelp()
    {
        if (self::$Mktr == null) {
            self::$Mktr = Mage::getModel('mktr_tracker/Config');
        }
        return self::$Mktr;
    }

    public function sendConfirmationSuccessEmail() {
        if (self::getHelp()->getOptIn() == 0)
        {
            return parent::sendConfirmationSuccessEmail();
        }
        return $this;
    }

    public function sendUnsubscriptionEmail() {
        if (self::getHelp()->getOptIn() == 0)
        {
            return parent::sendUnsubscriptionEmail();
        }
        return $this;
    }
}
