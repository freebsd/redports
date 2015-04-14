<?php

/**
 * Class autoloader for our lib directory
 *
 * @author     Bernhard Froehlich <decke@bluelife.at>
 * @copyright  2015 Bernhard Froehlich
 * @license    BSD License (2 Clause)
 * @link       https://decke.github.io/redports/
 */

spl_autoload_register(function ($class) {
    include dirname(__FILE__).'/'.$class.'.php';
});

