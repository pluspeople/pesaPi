<?php
namespace PLUSPEOPLE\SlowTemplate;
require_once("PLUSPEOPLE/autoload.php");

/* Abstract class - used to provide baseline template functionality */
class Template {
  ############### Properties ####################
  protected $template;
	protected $user = NULL;
	protected $solution = NULL;

  ############### Methods #######################
  # # # # # # # # Initializer # # # # # # # # # #
  public function __construct() {
    $this->template = new SlowTemplate("", false);
    $this->template->setTemplateFile($this->getTemplateFile());
	}

  // # # # # # # # get/set methods # # # # # # # #
  // do override this function so it returns the name 
  // of the file you want to include
  public function getTemplateFile() {
    return "";
  }

	public function getCacheable() {
		return false;
	}

	public function getRequireActiveSubscription() {
		return true;
	}

	// indicates wether the output/page can be compressed or not
	public function getCompressable() {
		return true;
	}

	// Two magic levels "None" and "Login"
	public function getRequiredAccessLevel() {
		return "None";
	}

	public function getRequiredType() {
		return null;
	}

  public function getTemplate() {
    return $this->template;
  }

	public function getLoginUrl() {
		return "/login.php";
	}

	public function getUser() {
		if ($this->user == NULL) {
			@session_start();
			$userId = @(int)$_SESSION["GLOBAL_USER_ID"];
			if ($userId > 0) {
				$this->user = \ICTPrices\ProfileFactory::factoryOne($userId);
			}
		}
		return $this->user;
	}

  //# # # # # # # # Misc methods # # # # # # # # # #
	public function displaySelect($dataset, $selectedId, $prefix, $nameFunction="getFormatedName") {
		$slow = $this->getTemplate();
		$up = strtoupper($prefix);
		foreach ($dataset AS $data) {
			
			$slow->assign(array($up . "_VALUE" => (string)$data->getId(),
													$up . "_NAME" => call_user_func(array(&$data, $nameFunction)),
													$up . "_SELECTED" => ""));
			if ($data->getId() == $selectedId) {
				$slow->assignOne($up . "_SELECTED", 'selected="selected"');
			}
			$slow->parse($prefix);
		}
	}

  public function handleRequest() {
		//# Confirm that you actually have the required access level for this page.
		if ($this->getRequiredAccessLevel() != "None") {
			$user = $this->getUser();
			if ($user == NULL) {
        //# user not loged in
					WebUtility::redirect($this->getLoginUrl());
				exit;	

			} else {
        //# loged in lets check for required accesslevels needed
				if ($this->getRequiredType() != null AND $this->getRequiredType() != $user->getType()) {
					//# user does not have access
							WebUtility::redirect($this->getLoginUrl());
						exit;	
				}
				if ($this->getRequiredAccessLevel() != "Login") {
					$access = false;
					$levels = $user->getAccessLevel();
					foreach ($levels AS $level) {
						if ($this->getRequiredAccessLevel() == $level->getName()) {
							$access = true;
							break;
						}
					}
					if (!$access) {
   					// user does not have access
						WebUtility::redirect($this->getLoginUrl());
						exit;	
					}
				}

			}
		}

		if ($this->getCacheable()) {
		}
		$this->generate();
	}


  //# # # # # # # # Private/protected methods # # # #
	protected function checkAccess($accessName) {
		$user = $this->getUser();
		$levels = $user->getAccessLevel();
		$access = false;
		foreach ($levels AS $level) {
			if ($accessName == $level->getName()) {
				$access = true;
				break;
			}
		}
		return $access;
	}

  protected function generate() {
		global $singletonArray;

		try {
			if (isset($_REQUEST["AJAX"])) {
				$method = "ajax" . ucfirst($_REQUEST["AJAX"]);
				if (method_exists($this, $method)) {
					call_user_func_array(array($this, $method), array());
				} else {
					$this->ajax();
				}
				exit();
			} else {
				switch ($_SERVER["REQUEST_METHOD"]) {
				case "POST":
					$this->post();
					break;
				case "HEAD":
					$this->head();
					break;
				case "PUT":
					$this->put();
					break;
				case "GET":
					$this->get();
					break;
				}
				$this->request();
			}

			// free db resources
			if (isset($singletonArray['Database'])) {
				foreach ($singletonArray['Database'] AS $db) {
					$db->disconnect();
				}
			}

      //output
			$this->template->parse();
			$this->template->slowPrint();

		} catch(UhasibuError $ue) {
			# not done.
			print "EXCEPTION: " . $ue->getMessage();
			exit();
		}
	}

  // default implementation (ment to be overridet)
  public function ajax() {
	}

  public function get() {
	}

  public function post() {
	}

  public function put() {
	}

  public function head() {
	}

  public function request() {
	}

}

?>