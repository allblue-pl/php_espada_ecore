<?php namespace EC\FacebookPixel;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class MFacebookPixel extends E\Module {

    private $trackingCode = null;


    public function __construct(EC\Basic\MHead $header, $trackingCode = '')
    {
        parent::__construct();

        $this->header = $header;
        $this->trackingCode = $trackingCode;        
    }

    public function setTrackingCode($trackingCode)
    {
        $this->trackingCode = $trackingCode;
    }


    protected function _preDisplay(E\Site $site)
    {
        if ($this->trackingCode === '')
            return;

        $this->header->addHtml("
            <!-- Facebook Pixel Code -->
            <script>
            !function(f,b,e,v,n,t,s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};
            if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
            n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s)}(window, document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '{$this->trackingCode}');
            fbq('track', 'PageView');
            fbq('track', 'ViewContent');
            </script>
            <noscript><img height=\"1\" width=\"1\" style=\"display:none\"
            src=\"https://www.facebook.com/tr?id={$this->trackingCode}&ev=PageView&noscript=1\"
            /></noscript>
            <!-- End Facebook Pixel Code -->
        ");

        // $site->addL('init', E\Layout::_('Basic:raw', [
        //     'raw' => "
        //         <!-- Google Tag Manager (noscript) -->
        //         <noscript><iframe src=\"https://www.googletagmanager.com/ns.html?id={$this->trackingCode}\"
        //         height=\"0\" width=\"0\" style=\"display:none;visibility:hidden\"></iframe></noscript>
        //         <!-- End Google Tag Manager (noscript) -->
        //     ",
        // ]));
    }

}
