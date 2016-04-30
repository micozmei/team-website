<?php

namespace Bolt\Extension\Uwfsae\TeamApplication;

if (isset($app)) {
    $app['extensions']->register(new Extension($app));
}

?>
