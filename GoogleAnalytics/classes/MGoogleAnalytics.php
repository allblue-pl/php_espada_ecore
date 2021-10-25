<?php namespace EC\GoogleAnalytics;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class MGoogleAnalytics extends E\Module
{

    private $trackingCode = null;
    private $scriptCSPHash = null;


    public function __construct(EC\Basic\MHead $head, $trackingCode = null)
    {
        parent::__construct();

        $this->head = $head;
        $this->trackingCode = $trackingCode;
        $this->scriptCSPHash = $this->head->generateScriptCSPHash();        
    }

    public function setTrackingCode($trackingCode)
    {
        $this->trackingCode = $trackingCode;
    }


    protected function _preDisplay(E\Site $site)
    {
        if ($this->trackingCode === null)
            return;
            // throw new \Exception('Google Analytics tracking code not set.');

        $this->head->addHtml("
            <script nonce=\"{$this->scriptCSPHash}\">
                (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
                m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
                })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

                ga('create', '{$this->trackingCode}', 'auto');
                ga('send', 'pageview');
            </script>
        ");
    }

}
