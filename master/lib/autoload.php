<?php

spl_autoload_register(function ($class) {
    include 'lib/'.$class.'.php';
});

