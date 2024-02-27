<?php
/**
 * Convert standard menus to customized ones
 * also adding progress indications
 */

class minDefSkin_menu {

  public static function apply_mainbar($html) {
    $html = self::remove_empty_elements($html);
    return $html;
  }

  /* return language selector HTML code, currently used on layout.php */
  public static function get_language_selector() {
    global $DIC;
    $language = $DIC->language();

    // LANGUAGE MENU
    $current_language = $DIC->language()->getContentLanguage();
    $available_languages = $DIC->language()->getInstalledLanguages();
    $query_variables = parse_url($_SERVER['REQUEST_URI'])['query'];

    $language_labels = [
      "en" => "English",
      "fr" => "Français",
    ];

    ob_start();

    ?>
    <div class="minarm-language-selector">
      <?php
      foreach ($available_languages as $language) {
        ?>
        <a href="?<?= $query_variables; ?>&set_language=<?= $language; ?>" <?= $language == $current_language ? 'class="selected"' : ''; ?>>
          <?= $language_labels[$language] ?? $language; ?>
        </a>
        <?php
      }
      ?>
    </div>
    <?php

    return ob_get_clean();
  }

  public static function apply_metabar($html) {
    self::check_for_language_update();
    $html = self::remove_empty_elements($html);

    global $DIC;
    $html = minDefSkin_layout::apply_custom_placeholders($html);
    $is_login_page = strpos($_SERVER['REQUEST_URI'], "login.php") !== false || strtolower($_GET['cmdClass']) == "ilstartupgui" || strtolower($_GET['baseClass']) == "ilstartupgui";

    $dom = new DomDocument();
    $internalErrors = libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
    libxml_use_internal_errors($internalErrors);
    $finder = new DomXPath($dom);

    foreach ($finder->query('//form[contains(@id, "mm_search_form")]') as $menu_element) {
      $div_container = $menu_element->parentNode->parentNode->parentNode;
      $li = $div_container->parentNode;
      if (!$is_login_page) $li->appendChild($menu_element);
      $li->removeChild($div_container);
      $li->setAttribute('class', $li->getAttribute("class") . " search");

      $search_button = $finder->query('.//input[contains(@class, "btn-default")]', $li)->item(0);
      if ($search_button) {
        $fragment = $search_button->ownerDocument->createDocumentFragment();
        $fragment->appendXML('<button><span class="glyphicon glyphicon-search"></span></button>');
        $search_button->parentNode->replaceChild($fragment, $search_button);
      }

      $search_input = $finder->query('.//input[contains(@id, "main_menu_search")]', $li)->item(0);
      if ($search_input) {
        $search_input->setAttribute("placeholder", "Rechercher");
      }
    }

    $html = str_replace('<?xml encoding="utf-8" ?>', "", $dom->saveHTML());
    $html = str_replace("<html><body>", "", $html);
    $html = str_replace("</body></html>", "", $html);
    return $html;
  }


  public static function check_for_language_update() {
    if (empty((string) $_GET['set_language'])) return false;

    global $DIC;
    $user = $DIC->user();
    $db = $DIC->database();

    $db->manipulateF(
      "UPDATE usr_pref SET value = %s WHERE keyword = 'language' AND usr_id = %d",
      [
        'text',
        'integer'
      ], [
        $db->escape(strtolower($_GET['set_language'])),
        $user->getId()
      ]
    );

    $reload_url = preg_replace("/&set_language=../", "", $_SERVER['REQUEST_URI']);
    header('Location: ' . $reload_url);
  }


  public static function remove_empty_elements($html) {
    $dom = new DomDocument();
    $internalErrors = libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
    libxml_use_internal_errors($internalErrors);
    $finder = new DomXPath($dom);

    foreach ($finder->query('//li[contains(@class, "minarm-mainbar-li")]') as $menu_element) {
      $a = $finder->query('.//a', $menu_element);
      if ($a->length == 0) {
        $menu_element->parentNode->removeChild($menu_element);
      }
    }

    $html = str_replace('<?xml encoding="utf-8" ?>', "", $dom->saveHTML());
    $html = str_replace("<html><body>", "", $html);
    $html = str_replace("</body></html>", "", $html);

    return $html;
  }


  public static function remove_element($short_code, $html) {
    $dom = new DomDocument();
    $internalErrors = libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
    libxml_use_internal_errors($internalErrors);
    $finder = new DomXPath($dom);

    foreach ($finder->query('//li/a[contains(@href, "' . $short_code . '")]') as $menu_element) {
      $menu_element->parentNode->removeChild($menu_element);
    }

    $html = str_replace('<?xml encoding="utf-8" ?>', "", $dom->saveHTML());
    $html = str_replace("<html><body>", "", $html);
    $html = str_replace("</body></html>", "", $html);

    return $html;
  }
}
