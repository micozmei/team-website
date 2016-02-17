<?php

namespace Bolt\Extension\Uwfsae\Countdown;

if (isset($app)) {
    $app['extensions']->register(new Extension($app));
}

