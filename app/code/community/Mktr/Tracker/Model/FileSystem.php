<?php
/**
 * @copyright   © EAX LEX SRL. All rights reserved.
 * @license     http://opensource.org/licenses/osl-3.0.php - Open Software License (OSL 3.0)
 **/

class Mktr_Tracker_Model_FileSystem
{
    private static $path = null;
    private static $cons = null;

    private static $lastPath = null;

    private static $ins = array(
        "Help" => null,
        "fileSystem" => null,
        "ModulePath" => null,
        "status" => array()
    );

    public function __construct()
    {
        self::$cons = $this;
    }

    private static function getModulePath()
    {
        if (self::$ins['ModulePath'] === null)
        {
            self::$ins['ModulePath'] = dirname(__DIR__). "/";
        }
        return self::$ins['ModulePath'];
    }

    /** @noinspection PhpMissingReturnTypeInspection */
    public static function setWorkDirectory($name = 'base')
    {
        if ($name == 'base')
        {
            self::$path = Mage::getBaseDir('base'). "/";
        } else {
            self::$path = self::getModulePath() . $name . "/";
        }
        return self::$cons;
    }

    /** @noinspection PhpMissingReturnTypeInspection */
    public static function writeFile($fName, $content, $mode = 'w+')
    {
        $file = fopen(self::$path.$fName, $mode);
        fwrite($file, $content);
        fclose($file);

        self::$ins['status'][] = array(
            'path' => self::$path,
            'fileName' => $fName,
            'fullPath' => self::$path.$fName,
            'status' => true
        );

        return self::$cons;
    }

    public static function rFile($fName, $mode = "rb")
    {
        self::$lastPath = self::$path . $fName;
        if (file_exists(self::$lastPath)) {
            $file = fopen(self::$lastPath, $mode);

            $contents = fread($file, filesize(self::$lastPath));

            fclose($file);
        } else {
            $contents = '';
        }

        return $contents;
    }

    public static function readFile($fName, $mode = "rb")
    {
        self::$lastPath = self::$path . $fName;
        $file = fopen(self::$lastPath, $mode);

        $contents = fread($file, filesize(self::$lastPath));

        fclose($file);

        return $contents;
    }

    public static function isExists($fName)
    {
        return file_exists(self::$path . $fName);
    }

    public static function deleteFile($fName)
    {
        if (file_exists(self::$path . $fName)) {
            unlink(self::$path . $fName);
        }
        return true;
    }

    public static function getPath()
    {
        return self::$path;
    }

    public static function getLastPath()
    {
        return self::$lastPath;
    }

    public static function getStatus()
    {
        return self::$ins['status'];
    }
}
