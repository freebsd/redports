#!/usr/bin/env php
<?php

/**
 * redports is a continuous integration platform for FreeBSD ports
 *
 * @author     Bernhard Froehlich <decke@bluelife.at>
 * @copyright  2015 Bernhard Froehlich
 * @license    BSD License (2 Clause)
 * @link       https://decke.github.io/redports/
 */

require_once 'vendor/autoload.php';

$pm = new ProcessManager();
$pm->addJail('JAIL1');
$pm->addJail('JAIL2');
$pm->addJail('JAIL3');

$pm->run();

