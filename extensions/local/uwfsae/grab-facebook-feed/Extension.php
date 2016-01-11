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
        $result = 'https://graph.facebook.com/v2.5/' . $pageId . '/feed?access_token=' . $appToken;

        return "This is a test" . $result;
    }
}
