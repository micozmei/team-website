<?php

namespace Bolt\Extension\Uwfsae\GrabFacebookFeed;

if (isset($app)) {
    $app['extensions']->register(new Extension($app));
}

