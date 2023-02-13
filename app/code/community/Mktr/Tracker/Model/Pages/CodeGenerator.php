<?php
/**
 * @copyright   © EAX LEX SRL. All rights reserved.
 * @license     http://opensource.org/licenses/osl-3.0.php - Open Software License (OSL 3.0)
 **/

class Mktr_Tracker_Model_Pages_CodeGenerator
{
    private static $ins = array(
        "Help" => null,
        "Config" => null
    );

    private static $error = null;
    private static $NewCode = null;
    private static $generator = null;
    private static $params;

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

    /** @noinspection PhpUnused */
    public function indexAction()
    {
        self::$error =  self::getHelp()->getFunc->isParamValid(array(
            'key' => 'Required|Key',
            'expiration_date' => 'DateCheck',
            'value' => 'Required|Int',
            'type' => "Required|RuleCheck"
        ));

        if (self::status())
        {
            self::$params = Mage::app()->getRequest()->getParams();
            $gCode = self::getNewCode(self::$params);

            if ($gCode != null) {
                return self::getHelp()->getFunc->Output(array('code' => $gCode->getCode()));
            }
        }
        return self::getHelp()->getFunc->Output(array('status' => self::$error));
    }
    private static $cGroups = null;
    private static $sIds = null;

    private static $rules = array(
        0 => "fixedValue",
        1 => "percentage",
        2 => "freeShipping"
    );

    const NAME = "MKTR-%s-%s";

    private static function getGenerator()
    {
        if (self::$generator === null)
        {
            $mktrGenerator = Mage::getModel('salesrule/coupon_massgenerator');
            $mktrGenerator->setFormat(Mage_SalesRule_Helper_Coupon::COUPON_FORMAT_ALPHANUMERIC);
            $mktrGenerator->setDash(0);
            $mktrGenerator->setLength(8);
            $mktrGenerator->setPrefix('MKTR-');
            $mktrGenerator->setSuffix('');
            self::$generator = $mktrGenerator;
        }
        return self::$generator;
    }

    public static function getNewCode($p)
    {
        $val = self::$rules[$p['type']];

        $name = vsprintf(self::NAME, array($val, $p['value'])).(isset($p['expiration_date']) ? '-' . $p['expiration_date'] : '');

        if (self::$cGroups === null)
        {
            $nGroups = array();
            foreach (Mage::getModel('customer/group')->getCollection() as $group) {
                $nGroups[] = $group->getCustomerGroupId();
            }
            self::$cGroups = $nGroups;
        }

        if (self::$sIds === null)
        {
            $nIds = array();
            foreach (Mage::app()->getWebsites() as $websiteId => $website) {
                $nIds[] = $websiteId;
            }
            self::$sIds = $nIds;
        }

        if (!isset(self::$NewCode[$name]))
        {
            if (self::$NewCode === null)
            {
                self::$NewCode = array();
            }

            $NewCode = Mage::getModel('salesrule/rule')
                ->getCollection()
                ->addFieldToFilter('name', array('eq'=>$name))
                ->getFirstItem();

            if ($NewCode === null || !$NewCode->getId())
            {
                $NewCode = Mage::getModel('salesrule/rule');
                $NewCode->setName($name)
                    ->setDescription("Discount Code Generated through TheMarketer API")
                    ->setCouponType(Mage_SalesRule_Model_Rule::COUPON_TYPE_SPECIFIC)
                    ->setUsesPerCustomer(1)
                    ->setUsesPerCoupon(1)
                    ->setCustomerGroupIds(self::$cGroups)
                    ->setIsActive(1)
                    ->setConditionsSerialized('')
                    ->setActionsSerialized('')
                    ->setStopRulesProcessing(0)
                    ->setIsAdvanced(1)
                    ->setProductIds('')
                    ->setSortOrder(0)
                    ->setSimpleFreeShipping('0')
                    ->setApplyToShipping('0')
                    ->setIsRss(0)
                    ->setWebsiteIds(self::$sIds)
                    ->setUseAutoGeneration(1);

                $NewCode->setDiscountAmount($p['value'])
                    ->setDiscountQty(null)
                    ->setDiscountStep(0);

                switch ($val) {
                    case 'percentage':
                        $NewCode->setSimpleAction('by_percent');
                        break;
                    case 'freeShipping':
                        $NewCode->setSimpleAction('by_fixed')
                            ->setSimpleFreeShipping('1')
                            ->setDiscountAmount(0);
                        break;
                    case 'fixedValue':
                        $NewCode->setSimpleAction('cart_fixed');
                        break;
                }

                $NewCode->setFromDate(date('Y-m-d'));

                if (isset($p['expiration_date'])) {
                    $NewCode->setToDate($p['expiration_date']);
                }

                $NewCode->setCouponCodeGenerator(self::getGenerator());
                $NewCode->save();

            }
            self::$NewCode[$name] = $NewCode;
        }

        self::$NewCode[$name]->setCouponCodeGenerator(self::getGenerator());

        self::$NewCode[$name]->setCouponType(Mage_SalesRule_Model_Rule::COUPON_TYPE_AUTO);

        $coupon = self::$NewCode[$name]->acquireCoupon(true);

        if ($coupon->getCode() === null)
        {
            self::$error = "Acquire Coupon Fail";
            return null;
        }

        $coupon->setType(Mage_SalesRule_Helper_Coupon::COUPON_TYPE_SPECIFIC_AUTOGENERATED)->save();
        return $coupon;
    }
}
