<?php namespace EC\Basic;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class SBasic extends E\Site {
    private MHead $head;

    public function __construct() {
        parent::__construct();

        $this->head = new \EC\Basic\MHead($this);
    }


    public function getHead(): MHead {
        return $this->head;
    }

    
    protected function _initialize(): void {
        parent::_initialize();
    }

    protected function _preDisplay(): void {
        if (EDEBUG) {
            $this->addL('debug', E\Layout::_('Basic:raw', [
                'raw' => $this->getDebugJS(),
            ]));
        }

        parent::_preDisplay();
    }


    private function getDebugJS() {
        $js = '';

        $notices = E\Notice::GetAll();
        foreach ($notices as $notice) {
            $message = str_replace("'", "\\'", $notice['message']);

            $js .= '<script>';
            $js .= "console.groupCollapsed('Espada: {$message}');";
            foreach ($notice['stack'] as $stackPart)
                $js .= "console.warn('  ' + " . json_encode($stackPart) . ");";
            $js .= 'console.groupEnd();';
            $js .= '</script>';
        }

        return $js;
    }
}
