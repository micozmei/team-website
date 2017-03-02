<?php

namespace Bolt\Extension\Uwfsae\Crowdfunding;

if (isset($app)) {
    $app['extensions']->register(new Extension($app));
}

?>
