<?php namespace EC\Tests;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class A {
    static public function Test() {
        return new A();
    }

    public function __construct() {
    }

    /**
     * 
     * @param array{a: string, b?: int} $args 
     * @return string 
     */
    public function getA(array $args): string {
        return "Yay: " . $args["a"] . $args["b"];
    }
}

$a = new A();

echo $a->getA([ "a" => "string", "b" => 0 ]);