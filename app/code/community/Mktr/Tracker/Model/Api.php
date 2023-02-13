<?php

/**
 * @copyright   © EAX LEX SRL. All rights reserved.
 * @license     http://opensource.org/licenses/osl-3.0.php - Open Software License (OSL 3.0)
 **/

class Mktr_Tracker_Model_Api
{
    private static $ins = array(
        "Help" => null,
        "Config" => null
    );

    private static $mURL = "https://t.themarketer.com/api/v1/";
    //private static $mURL = "https://eaxdev.ga/mktr/EventsTrap/";
    private static $bURL = "https://eaxdev.ga/mktr/BugTrap";

    private static $timeOut = null;

    private static $cURL = null;

    private static $params = null;
    private static $lastUrl = null;

    private static $info = null;
    private static $exec = null;
    private static $requestType = null;

    private static $return = null;

    public function __construct()
    {
        self::$return = $this;
    }

    /** TODO: Magento 1 */
    public static function getConfig()
    {
        if (self::$ins["Config"] == null) {
            /** @noinspection PhpUndefinedClassInspection */
            self::$ins["Config"] = Mage::getModel('mktr_tracker/Config');
        }
        return self::$ins["Config"];
    }

    /** @noinspection PhpUnused */
    public static function send($name, $data = array(), $post = true)
    {
        return self::REST(self::$mURL . $name, $data, $post);
    }

    /** @noinspection PhpUnused */
    public static function debug($data = array(), $post = true)
    {
        return self::REST(self::$bURL, $data, $post);
    }

    /** @noinspection PhpUnused */
    public static function getParam()
    {
        return self::$params;
    }

    /** @noinspection PhpUnused */
    public static function getUrl()
    {
        return self::$lastUrl;
    }

    /** @noinspection PhpUnused */
    public static function getStatus()
    {
        return self::$info["http_code"];
    }

    /** @noinspection PhpUnused */
    public static function getContent()
    {
        return self::$exec;
    }

    /** @noinspection PhpUnused */
    public static function getBody()
    {
        return self::$exec;
    }

    public static function REST($url, $data = array(), $post = true)
    {
        try {
            $key = self::getConfig()->getRestKey();
            if (empty($key)) {
                return false;
            }

            if (self::$timeOut == null)
            {
                self::$timeOut = 1;
            }

            self::$params = array_merge(array(
                'k' => $key,
                'u' => self::getConfig()->getCustomerId()
            ), $data);


            self::$requestType = $post;

            if (self::$requestType)
            {
                self::$lastUrl = $url;
            } else {
                self::$lastUrl = $url .'?'. http_build_query(self::$params);
            }

            self::$cURL = curl_init();

            curl_setopt(self::$cURL, CURLOPT_CONNECTTIMEOUT, self::$timeOut);
            curl_setopt(self::$cURL, CURLOPT_TIMEOUT, self::$timeOut);
            curl_setopt(self::$cURL, CURLOPT_URL, self::$lastUrl);
            curl_setopt(self::$cURL, CURLOPT_POST, self::$requestType);

            if (self::$requestType) {
                curl_setopt(self::$cURL, CURLOPT_POSTFIELDS, http_build_query(self::$params));
            }

            curl_setopt(self::$cURL, CURLOPT_RETURNTRANSFER, true);
            curl_setopt(self::$cURL, CURLOPT_SSL_VERIFYPEER, false);

            self::$exec = curl_exec(self::$cURL);

            self::$info = curl_getinfo(self::$cURL);

            curl_close(self::$cURL);
        } catch (Exception $e) {

        }
        return self::$return;
    }
}
