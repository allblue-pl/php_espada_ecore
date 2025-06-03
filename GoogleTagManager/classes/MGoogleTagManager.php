<?php namespace EC\GoogleTagManager;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class MGoogleTagManager extends E\Module {

    private $trackingCode = null;


    public function __construct(EC\Basic\MHead $header, $trackingCode = '') {
        parent::__construct();

        $this->header = $header;
        $this->trackingCode = $trackingCode;        
    }

    public function setTrackingCode($trackingCode) {
        $this->trackingCode = $trackingCode;
    }


    protected function _preDisplay(E\Site $site) {
        if ($this->trackingCode === '')
            return;

        $this->header->addHtml("
            <!-- Google Tag Manager -->
            <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
            })(window,document,'script','dataLayer','{$this->trackingCode}');</script>
            <!-- End Google Tag Manager -->
        ");

        $site->addL('init', E\Layout::_('Basic:raw', [
            'raw' => "
                <!-- Google Tag Manager (noscript) -->
                <noscript><iframe src=\"https://www.googletagmanager.com/ns.html?id={$this->trackingCode}\"
                height=\"0\" width=\"0\" style=\"display:none;visibility:hidden\"></iframe></noscript>
                <!-- End Google Tag Manager (noscript) -->
            ",
        ]));
    }

}
