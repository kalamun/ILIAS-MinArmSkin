<?php

/**
 * Config screen
 */
class ilMinDefSkinConfigGUI extends ilPluginConfigGUI {

    const PLUGIN_CLASS_NAME = ilMinDefSkinPlugin::class;
    const CMD_CONFIGURE = "configure";
    const CMD_UPDATE_CONFIGURE = "updateConfigure";
    const LANG_MODULE = "config";

    protected $dic;
    protected $plugin;
    protected $lng;
    protected $request;
    protected $user;
    protected $ctrl;
    protected $object;
  
    public function __construct()
    {
      global $DIC;
      $this->dic = $DIC;
      $this->plugin = ilMinDefSkinPlugin::getInstance();
      $this->lng = $this->dic->language();
      // $this->lng->loadLanguageModule("assessment");
      $this->request = $this->dic->http()->request();
      $this->user = $this->dic->user();
      $this->ctrl = $this->dic->ctrl();
      $this->object = $this->dic->object();
    }
    
    public function performCommand(/*string*/ $cmd)/*:void*/
    {
        $this->plugin = $this->getPluginObject();

        switch ($cmd)
		{
			case self::CMD_CONFIGURE:
            case self::CMD_UPDATE_CONFIGURE:
                $this->{$cmd}();
                break;

            default:
                break;
		}
    }

    protected function configure()/*: void*/
    {
        global $tpl, $ilCtrl, $lng, $DIC;

        $title_long = $DIC['ilias']->getSetting("inst_name");
        $title_short = $DIC['ilias']->getSetting("short_inst_name");
        $menu_orientation = $DIC['ilias']->getSetting("menu_orientation") ?? "vertical";

		require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));
        $form->setTitle($this->plugin->txt("settings"));
        
        $title_long_input = new ilTextInputGUI($this->plugin->txt("title_long", true), 'title_long');
        $title_long_input->setValue($title_long);
        $form->addItem($title_long_input);
        
        $title_short_input = new ilTextInputGUI($this->plugin->txt("title_short", true), 'title_short');
        $title_short_input->setValue($title_short);
        $form->addItem($title_short_input);
        
        $menu_orientation_input = new ilSelectInputGUI($this->plugin->txt("menu_orientation", true), "menu_orientation");
        $menu_orientation_input->setRequired(true);
        $menu_orientation_input->setOptions([
            "vertical" => $this->plugin->txt("menu_orientation_vertical", true),
            "horizontal" => $this->plugin->txt("menu_orientation_horizontal", true),
        ]);
        $menu_orientation_input->setValue($menu_orientation);
        $form->addItem($menu_orientation_input);

        $logo_image = new ilImageFileInputGUI($this->plugin->txt("logo_image", true), 'logo_image');
        $logo_image->setAllowDeletion(false);
        $image_url = './minarm_logo.png';
        if (file_exists($image_url)) {
            $logo_image->setImage($image_url);
        }
        $form->addItem($logo_image);
        
        $login_image = new ilImageFileInputGUI($this->plugin->txt("login_image"), 'login_image');
        $login_image->setAllowDeletion(false);
        $image_url = './minarm_login.jpg';
        if (file_exists($image_url)) {
            $login_image->setImage($image_url);
        }
        $form->addItem($login_image);
        
        $form->addCommandButton("updateConfigure", $lng->txt("save"));

		$tpl->setContent($form->getHTML());
    }

    protected function updateConfigure()/*: void*/
    {
        global $lng, $DIC;

        if (isset($_POST['title_long'])) {
            $DIC['ilias']->setSetting("inst_name", trim($_POST['title_long']));  
        }
        if (isset($_POST['title_short'])) {
            $DIC['ilias']->setSetting("short_inst_name", trim($_POST['title_short']));  
        }
        if (isset($_POST['menu_orientation'])) {
            $DIC['ilias']->setSetting("menu_orientation", trim($_POST['menu_orientation']));  
        }

        if (!empty($_FILES['logo_image']['name'])) {
            move_uploaded_file($_FILES["logo_image"]["tmp_name"], './minarm_logo.png');
        }
        if (!empty($_FILES['login_image']['name'])) {
            move_uploaded_file($_FILES["login_image"]["tmp_name"], './minarm_login.jpg');
        }

        self::configure();

        ilUtil::sendSuccess($this->plugin->txt("configuration_saved"), true);

    }
}
