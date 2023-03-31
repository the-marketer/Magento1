<?php
/**
 * @copyright   Copyright (c) 2023 TheMarketer.com
 * @project     TheMarketer.com
 * @website     https://themarketer.com/
 * @author      Alexandru Buzica (EAX LEX S.R.L.) <b.alex@eax.ro>
 * @license     http://opensource.org/licenses/osl-3.0.php - Open Software License (OSL 3.0)
 * @docs        https://themarketer.com/resources/api
 */

class Mktr_Tracker_Model_Option_Provider
{
    public function toOptionArray()
    {
        return array(
            array('value' => 0, 'label' => "WebSite"),
            array('value' => 1, 'label' => "The Marketer")
        );
    }
}
