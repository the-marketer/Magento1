<?php
/**
 * @copyright   Copyright (c) 2023 TheMarketer.com
 * @project     TheMarketer.com
 * @website     https://themarketer.com/
 * @author      Alexandru Buzica (EAX LEX S.R.L.) <b.alex@eax.ro>
 * @license     http://opensource.org/licenses/osl-3.0.php - Open Software License (OSL 3.0)
 * @docs        https://themarketer.com/resources/api
 */

class Mktr_Tracker_Model_Func
{
    private static $params;
    private static $dateFormat;
    private static $getOut;

    private static $ins = array(
        "Help" => null,
        "Config" => null
    );

    private static $storeID = null;

    /** TODO: Magento 1 */
    public static function getConfig()
    {
        if (self::$ins["Config"] == null) {
            self::$ins["Config"] = Mage::getModel('mktr_tracker/Config');
        }
        return self::$ins["Config"];
    }

    /** TODO: Magento 1
     */
    public static function getHelp()
    {
        if (self::$ins["Help"] == null) {
            self::$ins["Help"] = Mage::helper('mktr_tracker');
        }
        return self::$ins["Help"];
    }

    public static function digit2($num)
    {
        // return sprintf('%.2f', (float) $num);
        return number_format((float) $num, 2, '.', ',');
    }

    public static function validateTelephone($phone)
    {
        return preg_replace("/\D/", "", $phone);
    }

    public static function toJson($data = null){
        return json_encode(($data === null ? array() : $data), JSON_UNESCAPED_SLASHES);
    }

    public static function validateDate($date, $format = 'Y-m-d')
    {
        self::$dateFormat = $format;
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    public static function getOutPut()
    {
        return self::$getOut;
    }

    public static function correctDate($date = null, $format = "Y-m-d H:i")
    {
        return $date !== null ? date($format, strtotime($date)) : $date;
    }

    public static function setStoreId($id)
    {
        self::$storeID = $id;
    }

    public static function getWebsiteId($store)
    {
        try {
            return Mage::app()->getWebsite($store)->getDefaultGroup()->getDefaultStoreId();
        } catch(Exception $e) {
            return false;
        }
    }

    public static function getStoreId()
    {
        if (self::$storeID == null) {
            /* TODO PAGE LIMIT */
            $store = self::getHelp()->getRequest->getParam('store', false);
            
            if ($store !== false) {
                try {
                    $store = Mage::app()->getStore($store)->getId();
                } catch(Exception $e) {
                    $store = self::getWebsiteId($store);
                }
            }
            if ($store !== false) {
                self::$storeID = $store;
                Mage::app()->setCurrentStore($store);
            } else {
                self::$storeID = self::getHelp()->getStore->getStoreId();
            }
        }
        return self::$storeID;
    }

    public static function readOrWrite($fName, $secondName, $action)
    {
        if (!self::getHelp()->getRequest->getParam("mime-type")) {
            self::getHelp()->getRequest->setParam("mime-type", 'xml');
        }

        $params = self::getHelp()->getRequest->getParams();
        if (isset($params['start_date']))
        {
            $script = base64_encode($params['start_date'].'-'.self::getStoreId());
        } else {
            $script = self::getStoreId();
        }

        $fileName = $fName.".".$script.".".$params["mime-type"];

        $module = self::getHelp()->getFileSystem->setWorkDirectory("Storage");

        if (isset($params['read']) && $module->isExists($fileName)) {
            $out = $module->readFile($fileName);

            if ($out !== false) {
                return self::justOutput($out);
            }
        }

        $out = $action->freshData();

        $result = self::Output($fName, array($secondName => $out));

        $module->writeFile($fileName, self::getOutPut());

        return $result;
    }

    public static function Write($action)
    {
        if (!self::getHelp()->getRequest->getParam("mime-type")) {
            self::getHelp()->getRequest->setParam("mime-type", 'xml');
        }

        $params = self::getHelp()->getRequest->getParams();

        if (isset($params['start_date']))
        {
            $script = base64_encode($params['start_date'].'-'.self::getStoreId());
        } else {
            $script = self::getStoreId();
        }

        $fileName = $action->getName().".".$script.".".$params["mime-type"];

        $module = self::getHelp()->getFileSystem->setWorkDirectory("Storage");

        $out = $action->freshData();

        $result = self::Output($action->getName(), array($action->getSecondName() => $out));

        $module->writeFile($fileName, self::getOutPut());

        return $result;
    }

    public static function justOutput($data, $data1=null, $type = null)
    {
        return self::Output($data, $data1, $type, false);
    }

    public static function Output($data, $data1=null, $type = null, $convert = true)
    {
        if ($type == null)
        {
            $type = self::getHelp()->getRequest->getParam('mime-type');
        }
        if (empty($type))
        {
            $type = "xml";
        }

        $result = self::getHelp()->getPageRaw;
        self::$getOut = "";

        if ($type === 'json')
        {
            $result->setHeader('Content-type', 'application/json; charset=utf-8;', 1);

            if($convert) {
                if ($data1 !== null) {
                    $data = array($data => $data1);
                }

                self::$getOut = self::toJson($data);
            }
        } else {
            $result->setHeader('Content-type', 'application/xhtml+xml; charset=utf-8;', 1);

            if($convert) {
                self::$getOut = self::getHelp()->getArray2XML->cXML($data, $data1)->saveXML();
            }
        }

        if(!$convert)
        {
            self::$getOut = $data;
        }

        return $result->setBody(self::$getOut);
    }

    public static function isParamValid($checkParam = null)
    {
        self::$params = self::getHelp()->getRequest->getParams();

        if (self::$params === null)
        {
            return "oops";
        }

        if ($checkParam === null)
        {
            return null;
        }

        $error = null;

        foreach ($checkParam as $k=>$v)
        {
            if ($v !== null)
            {
                $check = explode("|", $v);
                foreach ($check as $do)
                {
                    if ($error === null) {
                        switch ($do)
                        {
                            case "Required":
                                if (!isset(self::$params[$k]))
                                {
                                    $error = "Missing Parameter ". $k;
                                }
                                break;
                            case "DateCheck":
                                if (isset(self::$params[$k]) && !self::validateDate(self::$params[$k]))
                                {
                                    $error = "Incorrect Date ".
                                        $k." - ".
                                        self::$params[$k] . " - ".
                                        self::$dateFormat;
                                }
                                break;
                            case "StartDate":
                                if (isset(self::$params[$k]) && strtotime(self::$params[$k]) > time())
                                {
                                    $error = "Incorrect Start Date ".
                                        $k." - ".
                                        self::$params[$k] . " - Today is ".
                                        date(self::$dateFormat, time());
                                }
                                break;
                            case "Key":
                                if (isset(self::$params[$k]) && self::$params[$k] !== self::getConfig()->getRestKey())
                                {
                                    $error = "Incorrect REST API Key ". self::$params[$k];
                                }
                                break;
                            case "RuleCheck":
                                /** TODO: Check If Break */
                                $getDiscountRules = self::getConfig()->getDiscountRules();
                                if (isset(self::$params[$k]) && !isset($getDiscountRules[self::$params[$k]]))
                                {
                                    $error = "Incorrect Rule Type ". self::$params[$k];
                                }
                                break;
                            case "Int":
                                if (isset(self::$params[$k]) && !is_numeric(self::$params[$k]))
                                {
                                    $error = "Incorrect Value ". self::$params[$k];
                                }
                                break;
                            case "allow_export":
                                if (self::getConfig()->getAllowExport() === 0) {
                                    $error = "Export not Allow";
                                }
                                break;
                            default:
                        }
                    }
                }
            }
        }

        return $error;
    }
}