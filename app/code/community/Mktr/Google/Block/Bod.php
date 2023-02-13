<?php
/**
 * @copyright   © EAX LEX SRL. All rights reserved.
 * @license     http://opensource.org/licenses/osl-3.0.php - Open Software License (OSL 3.0)
 **/

/** @noinspection PhpUnused */

class Mktr_Google_Block_Bod extends Mage_Core_Block_Template
{
    public function _toHtml()
    {
        if (Mage::getStoreConfig("mktr_google/setting/status") == 0)
        {
            return '';
        }

        $key = Mage::getStoreConfig("mktr_google/setting/tracking");

        return '<!-- Google Tag Manager (noscript) -->
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id='.$key.'" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        <!-- End Google Tag Manager (noscript) -->';
    }
}
