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

    // From http://stackoverflow.com/a/3525863/646543
    public static function makeLink($s) {
      return preg_replace('@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@', '<a href="$1" target="_blank">$1</a>', $s);
    }

    public static function humanizeDate($raw_date) {
        $mapping = array(
            "01" => "January",
            "02" => "February",
            "03" => "March",
            "04" => "April",
            "05" => "May",
            "06" => "June",
            "07" => "July",
            "08" => "August",
            "09" => "September",
            "10" => "October",
            "11" => "November",
            "12" => "December"
        );

        $post_date = explode('T', $raw_date)[0];
        list($year, $month, $day) = explode('-', $post_date);

        return $mapping[$month] . ' ' . $day . ', ' . $year;
    }

    public function twigAddFacebookPosts($pageId, $appToken, $limit) {
        $request_url = 'https://graph.facebook.com/v2.5/' . $pageId . '/feed?access_token=' . $appToken;
        $raw_data = file_get_contents($request_url);
        $json_data = json_decode($raw_data);

        $out = '<div class="facebook-feed">';
        foreach ($json_data->data as $post) {
            $message = $this->makeLink(str_replace("\n\n", '</p><p>', $post->message));
            $link = 'https://www.facebook.com/' . $post->id;
            $post_date = $this->humanizeDate($post->created_time);

            $out .= '<div class="article">';
            $out .= '  <div class="article-head">';
            $out .= '    <h2><a href="' . $link . '">';
            $out .= '      Posted on ' . $post_date;
            $out .= '    </a></h2>';
            $out .= '  </div>';
            $out .= '  <div class="article-content">';
            $out .= '    <p>' . $message . '</p>';
            $out .= '    <p class="original-post">';
            $out .= '      <a href="' .$link .'">';
            $out .= '        <span class="socicon-facebook"></span>';
            $out .= '        Original post';
            $out .= '      </a>';
            $out .= '    </p>';
            $out .= '  </div>';
            $out .= '</div>';

            $limit -= 1;
            if ($limit == 0) {
                break;
            }
        }

        $out .= '</div>';


        echo $out;
    }
}
