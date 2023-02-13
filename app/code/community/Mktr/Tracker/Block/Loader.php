<?php
/**
 * @copyright   © EAX LEX SRL. All rights reserved.
 * @license     http://opensource.org/licenses/osl-3.0.php - Open Software License (OSL 3.0)
 **/

class Mktr_Tracker_Block_Loader extends Mage_Core_Block_Template
{
    private static $actions = array(
        "cms_index_index" => "__sm__view_homepage",
        "catalog_category_view" => "__sm__view_category",
        "catalog_product_view" => "__sm__view_product",
        /* "checkout_cart_index" => "Cart", */
        /** TODO: Magento 1 - "checkout_onepage_index" => "__sm__initiate_checkout" */
        "checkout_onepage_index" => "__sm__initiate_checkout",
        /* "checkout_index_index" => "__sm__initiate_checkout", */
        "catalogsearch_result_index" => "__sm__search"
    );

    private static $ins = array(
        "Help" => null,
        "Config" => null
    );

    private static $actionName = null;

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

    public static function getEventName()
    {
        $ac = self::$actions;
        if (!isset($ac[self::actionName()])) {
            return null;
        } else {
            return $ac[self::actionName()];
        }
    }

    /** @noinspection PhpUndefinedClassInspection */
    public static function actionName()
    {
        if (self::$actionName === null)
        {
            /** TODO: Magento 1 */
            self::$actionName = Mage::app()->getFrontController()->getAction()->getFullActionName();
        }
        return self::$actionName;
    }

    /** @noinspection PhpUnused */
    protected function _toHtml()
    {
        $key = self::getHelp()->getConfig->getKey();

        if (self::getHelp()->getConfig->getStatus() === 0 || empty($key))
        {
            return '';
        }

        $lines = array();

        $lines[] = vsprintf(self::getHelp()->getConfig->getLoader(), self::getHelp()->getConfig->getKey());

        $loadJS = array();

        $eventName = self::getEventName();

        if ($eventName != null)
        {
            $lines[] = "dataLayer.push(".self::getHelp()->getManager->getEvent($eventName)->toJson().");";
        }

        foreach (self::getHelp()->getConfig->getEventsObs() as $event=>$Name)
        {
            $fName = "get".vsprintf(self::getHelp()->getSessionName, array($event));

            $eventData = self::getHelp()->getSession->{$fName}();
            if ($eventData)
            {
                $lines[] = "dataLayer.push(".self::getHelp()->getManager->getEvent($Name[1], $eventData)->toJson().");";
                if ($Name[0]) {
                    $loadJS[$event] = true;
                }else {
                    $uName = "uns".vsprintf(self::getHelp()->getSessionName, array($event));
                    self::getHelp()->getSession->{$uName}();
                }
            }
        }
        $baseURL = self::getHelp()->getBaseUrl;

        foreach ($loadJS as $k=>$v)
        {
            $lines[] = '(function(){ let add = document.createElement("script"); add.async = true; add.src = "'.$baseURL.'mktr/api/'.$k.'"; let s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(add,s); })();';
        }

        $lines[] = 'window.MktrDebug = function () { if (typeof dataLayer != undefined) { for (let i of dataLayer) { console.log("Mktr","Google",i); } } };';

        // $lines[] = 'console.log("Mktr","ActionName","'.self::actionName().'");';

        $wh =  array(self::getHelp()->getSpace(), implode(self::getHelp()->getSpace(), $lines));
        $rep = array("%space%","%implode%");
        return str_replace($rep,$wh,'<!-- Mktr Script Start -->%space%<script type="text/javascript">%space%%implode%%space%</script>%space%<!-- Mktr Script END -->');
    }
}
