<?php
/**
 * @copyright   Copyright (c) 2023 TheMarketer.com
 * @project     TheMarketer.com
 * @website     https://themarketer.com/
 * @author      Alexandru Buzica (EAX LEX S.R.L.) <b.alex@eax.ro>
 * @license     http://opensource.org/licenses/osl-3.0.php - Open Software License (OSL 3.0)
 * @docs        https://themarketer.com/resources/api
 */

/** @noinspection PhpUnused */

class Mktr_Google_Block_Loader extends Mage_Core_Block_Template
{
    public function _toHtml()
    {
        if (Mage::getStoreConfig("mktr_google/setting/status") == 0)
        {
            return '';
        }

        $key = Mage::getStoreConfig("mktr_google/setting/tracking");

        return "<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','".$key."');</script>
<!-- End Google Tag Manager -->";
    }
}
