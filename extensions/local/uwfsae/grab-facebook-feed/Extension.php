<?php

namespace Bolt\Extension\Uwfsae\GrabFacebookFeed;

use Bolt\Application;
use Bolt\BaseExtension;

class Extension extends BaseExtension
{
    public function initialize()
    {
        $this->addTwigFunction('addFacebookPosts', 'twigAddFacebookPosts');
    }

    public function getName()
    {
        return "GrabFacebookFeed";
    }

    public function twigAddFacebookPosts($pageId, $appToken) {

        return "This is a test";
    }
}
