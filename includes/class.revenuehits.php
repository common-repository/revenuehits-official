<?php

require_once "class.revenuehits.helpers.php";

class Revenuehits
{

    protected $version = "6";
    protected $plugin_slug = "revenuehits";

    public function init()
    {
        global $post;

        $show = false;
        $excluded = false;

        $excludedPages = json_decode(get_option('revenuehits_exclude_pages'), true);

        if ($excludedPages) {
            foreach ($excludedPages as $item) {
                if (isset($post->ID) && $item['id'] == $post->ID) {
                    $excluded = true;
                }
            }
        }

        if ((is_home() || is_front_pagesniipet()) && get_option('revenuehits_homepage')) {
            $show = true;
        } elseif (is_category() && get_option('revenuehits_categories')) {
            $show = true;
        } elseif (is_single() && get_option('revenuehits_posts')) {
            $show = true;
        } elseif (!is_home() && !is_front_page() && !is_category() && !is_single() && get_option('revenuehits_other')) {
            $show = true;
        }

        if (get_option('revenuehits_show') != 2 && get_option('revenuehits_userid') && $show && !$excluded) {
            add_filter('wp_footer', array('Revenuehits', 'insertAd'));
        }
    }

    public function insertAd()
    {
        $userId = get_option('revenuehits_userid');


        foreach ($GLOBALS['RH_ZONE_TYPES'] as $k => $v) {
            $type = $v->name;
            if (get_option('revenuehits_position_' . $type)) {
                $tag = get_option('revenuehits_script_' . $type);
                if ($tag != "Failed") {
                    if (stripos($tag, "<script") === false) {
                        $revenueHits_ExternalTagGenerator = new RevenueHits_ExternalTagGenerator($tag, $userId);
                        $tag = $revenueHits_ExternalTagGenerator->getExternalTag();
                    }
                    print $tag;
                }
            }
        }

    }
}


    
    
