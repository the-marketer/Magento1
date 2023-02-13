<?php
/**
 * @copyright   © EAX LEX SRL. All rights reserved.
 * @license     http://opensource.org/licenses/osl-3.0.php - Open Software License (OSL 3.0)
 **/

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
