<?php

namespace Bolt\Extension\Uwfsae\Countdown;

use Bolt\Application;
use Bolt\BaseExtension;

class Extension extends BaseExtension
{
    public function initialize()
    {
        $this->addTwigFunction('countdown', 'twigCountdown');
    }

    public function getName()
    {
        return "Countdown";
    }

    public function twigCountdown($targetTime) {
        echo '<p class="test-clock clock">' . $targetTime . '</p>';
    }
}
