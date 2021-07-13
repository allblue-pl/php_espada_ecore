<?php namespace EC\Basic;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class SBasic extends E\Site
{

    public function __construct()
    {
        parent::__construct();

        $this->addM('postHead', new \EC\Basic\MHeader());
    }

    protected function _initialize()
    {
        parent::_initialize();
    }

    protected function _preDisplay()
    {
        if (EDEBUG) {
            $this->addL('debug', E\Layout::_('Basic:raw', [
                'raw' => $this->getDebugJS(),
            ]));
        }

        parent::_preDisplay();
    }


    private function getDebugJS()
    {
        $js = '';

        $notices = E\Notice::GetAll();
        foreach ($notices as $notice) {
            $message = str_replace("'", "\\'", $notice['message']);

            $js .= '<script type="text/javascript">';
            $js .= "console.groupCollapsed('Espada: {$message}');";
            foreach ($notice['stack'] as $stackPart)
                $js .= "console.warn('  ' + " . json_encode($stackPart) . ");";
            $js .= 'console.groupEnd();';
            $js .= '</script>';
        }

        return $js;
    }

}
