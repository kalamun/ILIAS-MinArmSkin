<?php
/**
 * Generic layout functions
 */

require_once __DIR__ . "/tabs.php";

class minDefSkin_layout
{

    public static function apply_custom_placeholders($html)
    {
        global $DIC;
        $user = $DIC->user();
        
        if (strpos($html, "{BODY_CLASS}") !== false) {
            $body_class = [];
            
            $is_login_page = strpos($_SERVER['REQUEST_URI'], "login.php") !== false || strtolower($_GET['cmdClass']) == "ilstartupgui" || strtolower($_GET['baseClass']) == "ilstartupgui";
            if ($is_login_page) {
                $body_class[] = "is_login";
            }
    
            if (minDefSkin_tabs::getRootCourse($_GET['ref_id']) !== false) {
                $body_class[] = "is_course";
            }
            
            if ($_GET['cmdClass'] == "ilmailfoldergui" || $_GET['cmdClass'] == "showMail") {
                $body_class[] = "is_inbox";
            }
    
            $html = str_replace("{BODY_CLASS}", implode(" ", $body_class), $html);
        }

        $html = str_replace("{SKIN_URI}", "/Customizing/global/skin/minarm", $html);
        $html = str_replace("{HOMEPAGE_URL}", "/goto.php?target=root_1&client_id=default", $html);
        $html = str_replace("{LANGUAGE_SELECTOR}", minDefSkin_menu::get_language_selector(), $html);
        
        $menu_orientation = $DIC['ilias']->getSetting("menu_orientation");
        if (empty($menu_orientation)) $menu_orientation = "vertical";
        $html = str_replace("{MENU_ORIENTATION}", $menu_orientation, $html);
        
        $title_long = $DIC['ilias']->getSetting("inst_name");
        $html = str_replace("{MAIN_TITLE}", $title_long, $html);

        // short codes
        $name = $user->getFirstName();
        $html = str_replace("[USER_NAME]", $name, $html);
        
        return $html;
    }

    public static function remove_breadcrumbs($html)
    {
        /* not possible to use xpath here without breaking ILIAS */
        if (strpos($html, '"breadcrumb_wrapper"') !== false) {
            preg_match('/(<nav aria-label="Breadcrumbs" class="breadcrumb_wrapper".*?>.*?<\/nav>)/s', $html, $breadcrumbs_wrapper);
            if (!empty($breadcrumbs_wrapper[1])) {
                $crumbs = substr_count($breadcrumbs_wrapper[1], '"crumb"');
                if ($crumbs <= 1) {
                    $html = str_replace($breadcrumbs_wrapper[1], "", $html);
                }
            }
        }
    
        return $html;
    }

    public static function add_custom_logo($html)
    {
        /* not possible to use xpath here without breaking ILIAS */
        $placeholder = '{LOGO}';
        if (strpos($html, $placeholder) !== false) {
            $file_path = './minarm_logo.png';
            if (file_exists($file_path)) {
                $html = str_replace($placeholder, '<img class="custom-logo" src="' . $file_path . '" title="Logo" />', $html);
            }
        }

        return $html;
    }

    public static function add_login_thumbnail($html)
    {
        /* not possible to use xpath here without breaking ILIAS */
        $placeholder = '{LOGIN_THUMBNAIL}';
        $placeholder_status = '{LOGIN_THUMBNAIL_STATUS}';
        if (strpos($html, $placeholder) !== false || strpos($html, $placeholder_status) !== false) {
            $file_path = './minarm_login.jpg';
            if (file_exists($file_path)) {
                $html = str_replace($placeholder, '<img class="login-thumbnail" src="' . $file_path . '" />', $html);
                $html = str_replace($placeholder_status, 'visible', $html);
            } else {
                $html = str_replace($placeholder, '<img class="login-thumbnail" src="' . $file_path . '" />', $html);
                $html = str_replace($placeholder_status, 'hidden', $html);
            }
        }
    
        return $html;
    }

    public static function cleanup_dead_code($html)
    {
        $dom = new DomDocument();
        $internalErrors = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
        libxml_use_internal_errors($internalErrors);
        $finder = new DomXPath($dom);

        // remove card container
        $card_container = $finder->query('//a[contains(@id, "ilPageShowAdvContent")]');
        if (!empty($card_container[0])) {
            $card_container[0]->parentNode->removeChild($card_container[0]);
        }

        return str_replace('<?xml encoding="utf-8" ?>', "", $dom->saveHTML());
    }

}
