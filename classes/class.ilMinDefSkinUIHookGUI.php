<?php
include_once("./Services/Object/classes/class.ilObjectGUI.php");
require_once(__DIR__ . "/../inc/footer.php");
require_once(__DIR__ . "/../inc/layout.php");
require_once(__DIR__ . "/../inc/tabs.php");
require_once(__DIR__ . "/../inc/menu.php");

/**
 * Class ilMinDefSkinUIHookGUI
 * @author            Kalamun <rp@kalamun.net>
 * @version $Id$
 * @ingroup ServicesUIComponent
 * @ilCtrl_isCalledBy ilMinDefSkinUIHookGUI: ilUIPluginRouterGUI, ilAdministrationGUI, ilRepositoryGUI
 */

class ilMinDefSkinUIHookGUI extends ilUIHookPluginGUI {
  protected $user;
  protected $ctrl;

  protected $is_admin;
  protected $is_tutor;

  public function __construct()
  {
    global $DIC;
    $this->user = $DIC->user();
    $this->ctrl = $DIC->ctrl();

    $this->is_admin = false;
    $this->is_tutor = false;

    $global_roles_of_user = $DIC->rbac()->review()->assignedRoles($DIC->user()->getId());

		foreach ($DIC->rbac()->review()->getGlobalRoles() as $role){
      if (in_array($role, $global_roles_of_user)) {
        $role = new ilObjRole($role);
        if ($role->getTitle() == "Administrator") $this->is_admin = true;
        if ($role->getTitle() == "Tutor") $this->is_tutor = true;
      }
		}
  }

  /**
	 * Modify HTML output of GUI elements. Modifications modes are:
	 * - ilUIHookPluginGUI::KEEP (No modification)
	 * - ilUIHookPluginGUI::REPLACE (Replace default HTML with your HTML)
	 * - ilUIHookPluginGUI::APPEND (Append your HTML to the default HTML)
	 * - ilUIHookPluginGUI::PREPEND (Prepend your HTML to the default HTML)
	 *
	 * @param string $a_comp component
	 * @param string $a_part string that identifies the part of the UI that is handled
	 * @param string $a_par array of parameters (depend on $a_comp and $a_part)
	 *
	 * @return array array with entries "mode" => modification mode, "html" => your html
	 */
	function getHTML($a_comp = false, $a_part = false, $a_par = array()) {
    global $tpl;  
    global $DIC;
    
    /* Prevent any modification to users not using the DCI Skin */
    include_once "Services/Style/System/classes/class.ilStyleDefinition.php";
    if (ilStyleDefinition::getCurrentSkin() !== 'minarm') {
      return ["mode" => ilUIHookPluginGUI::KEEP, "html" => ""];
    }
    
		if (!empty($a_par["html"]) && !$this->ctrl->isAsynch()) {
      $html = $a_par["html"];

      if($a_part == "template_show") {
        // custom placeholders
        $html = minDefSkin_layout::apply_custom_placeholders($html);
      }
      
      if($a_part == "template_get" && strpos($a_par["tpl_id"], "standardpage.html") !== false) {
        $html = minDefSkin_layout::remove_breadcrumbs($html);
      }
      
      /* login */
      if($a_part == "template_add" && strpos($a_par["tpl_id"], "tpl.login.html") !== false) {
        $html = minDefSkin_layout::add_login_thumbnail($html);
      }
      
      /* menu */
      if($a_part == "template_get" && $a_par["tpl_id"] == "src/UI/templates/default/MainControls/tpl.mainbar.html") {
        $html = minDefSkin_menu::apply_mainbar($html);
      }
      if($a_part == "template_get" && $a_par["tpl_id"] == "src/UI/templates/default/MainControls/tpl.metabar.html") {
        $html = minDefSkin_menu::apply_metabar($html);
      }
      
      /* footer */
      if ($a_part == "template_get" && $a_par['tpl_id'] == "src/UI/templates/default/MainControls/tpl.footer.html") {
        $html = minDefSkin_footer::apply($html);
      }
      
      if ($a_part == "template_load") {
        // custom placeholders
        $html = minDefSkin_layout::apply_custom_placeholders($html);
        $html = minDefSkin_layout::add_custom_logo($html);
      }

      return ["mode" => ilUIHookPluginGUI::REPLACE, "html" => $html];
    }

    return ["mode" => ilUIHookPluginGUI::KEEP, "html" => ""];
  }


  /**
	 * Modify GUI objects, before they generate ouput
	 *
	 * @param string $a_comp component
	 * @param string $a_part string that identifies the part of the UI that is handled
	 * @param string $a_par array of parameters (depend on $a_comp and $a_part)
	 */
  function modifyGUI($a_comp, $a_part, $a_par = array()) {
	}

}