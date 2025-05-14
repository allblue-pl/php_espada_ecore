<?php namespace EC\Basic;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class LScript extends E\Layout {

    public function __construct($script, ?string $cspHash = null)
    {
        parent::__construct('Basic:raw', is_callable($script) ? 
                function() use ($script, $cspHash) {
            $script_Raw = $script();
            return [
                'raw' => "<script" . ($cspHash === null ? '' : " nonce=\"{$cspHash}\"") .  
                        ">{$script_Raw}</script>",    
            ];
        } : [
            'raw' => "<script" . ($cspHash === null ? '' : " nonce=\"{$cspHash}\"") .  
                    ">{$script}</script>",
        ]);
    }

}
