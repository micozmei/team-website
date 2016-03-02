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

    public function handleVideo($post, $width, $height) {
        $url = $post->source;
        if (strpos($url, 'fbcdn') === false) {
            // Interestingly, this appears to work for both Vimeo and YouTube.
            $url = str_replace('autoplay=1', 'autoplay=0', $url);
            return '<div class="video-wrapper"><iframe width="' . $width . '" height="' . $height . '" src="' . $url . '?autoplay=0" frameborder="0" allowfullscreen></iframe></div>';
        } else {
            // Not quite sure how to handle fb yet
            /*list($id_a, $id_b) = explode("_", $post->id);
            $fb_video_url = '/uwfsae/videos/vb.' . $id_a . '/' . $id_b . '/?type=3';

            $out = '<div id="fb-root"></div><script>(function(d, s, id) {  var js, fjs = d.getElementsByTagName(s)[0];  if (d.getElementById(id)) return;  js = d.createElement(s); js.id = id;  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.3";  fjs.parentNode.insertBefore(js, fjs);}(document, \'script\', \'facebook-jssdk\'));</script>';

            $out .= '<div class="fb-video" data-allowfullscreen="1" data-href="' . $fb_video_url .'"></div>';
            return $out;*/

            return '<p>View full video on <a href="' . $post->link . '">Facebook</a>!</p>';
        }
    }

    public function handleMediaAttachment($post) {
        $out = '';
        if (property_exists($post, 'source')) {
            $out .= $this->handleVideo($post, 540, 360);
        } else {
            $out .= '<img src="' . $post->full_picture . '" />';
        }
        $out .= '<p class="description">' . $post->description . '</p>';
        return $out;
    }

    public function twigAddFacebookPosts($pageId, $appToken, $limit) {
        $request_url = 'https://graph.facebook.com/v2.5/' . $pageId . '/posts?access_token=' . $appToken . '&fields=message,full_picture,object_id,attachments,source,picture,link,description,caption,created_time';
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
            $out .= '      ' . $post_date;
            $out .= '    </a></h2>';
            $out .= '  </div>';
            $out .= '  <div class="article-content">';
            $out .= '    <p>' . $message . '</p>';
            if (property_exists($post, 'source') || property_exists($post, 'full_picture')) {
                $out .= '    <div class="media-attachment">';
                $out .= $this->handleMediaAttachment($post);
                $out .= '    </div>';
            }
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
