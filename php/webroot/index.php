<?php
namespace Pluspeople\Pesapi;
set_include_path("../local_include:../include:" . get_include_path());
require_once("PLUSPEOPLE/autoload.php");

if (version_compare(PHP_VERSION, '5.3.0') == -1) {
	print "<h1>PesaPi requires at least PHP Version 5.3.0 - you are running PHP Version " . PHP_VERSION . "</h1>";
	exit();
}

class configtool extends \PLUSPEOPLE\SlowTemplate\Template {
	public function getTemplateFile() {
		return substr($_SERVER['PHP_SELF'], 1, -3) . "tpl";
	}

	public function AJAXCreateDb() {
		if ($this->credentialsOk() AND !$this->dbStructureOk()) {
			$this->createDbStructure();
			print "OK";
		} else {
			print "FAIL";
		}
	}

	public function AJAXAddPrivateAccount() {
		$type = (int)$_POST["type"];
		$identifier = $_POST["identifier"];
		
		$account = \PLUSPEOPLE\PesaPi\Base\Account::createNew($type, $identifier);
		if (is_object($account)) {
			$settings = $account->getSettings();

			$pushIn = $_POST["pushIn"] == 1;
			$pushOut = $_POST["pushOut"] == 1;
			$pushNeutral = $_POST["pushNeutral"] == 1;
			$account->setName($_POST["name"]);
			$account->setPushIn($pushIn);
			$account->setPushOut($pushOut);
			$account->setPushNeutral($pushNeutral);
			
			// Settings.
			if ($pushIn) {
				$settings["PUSH_IN_URL"] = $_POST["pushInUrl"];
				$settings["PUSH_IN_SECRET"] = $_POST["pushInSecret"];
			} else {
				$settings["PUSH_IN_URL"] = "";
				$settings["PUSH_IN_SECRET"] = "";
			}

			if ($pushOut) {
				$settings["PUSH_OUT_URL"] = $_POST["pushOutUrl"];
				$settings["PUSH_OUT_SECRET"] = $_POST["pushOutSecret"];
			} else {
				$settings["PUSH_OUT_URL"] = "";
				$settings["PUSH_OUT_SECRET"] = "";
			}

			if ($pushNeutral) {
				$settings["PUSH_NEUTRAL_URL"] = $_POST["pushNeutralUrl"];
				$settings["PUSH_NEUTRAL_SECRET"] = $_POST["pushNeutralSecret"];
			} else {
				$settings["PUSH_NEUTRAL_URL"] = "";
				$settings["PUSH_NEUTRAL_SECRET"] = "";
			}

			$account->setSettings($settings);
			$account->update();
			print "OK";
		} else {
			print "FAIL";
		}
	}

	public function AJAXAddMpesaPaybill() {
		$type = (int)$_POST["type"];
		$identifier = $_POST["identifier"];
		
		$account = \PLUSPEOPLE\PesaPi\Base\Account::createNew($type, $identifier);
		if (is_object($account)) {
			$settings = $account->getSettings();

			$pushIn = $_POST["pushIn"] == 1;
			$pushOut = $_POST["pushOut"] == 1;
			$pushNeutral = $_POST["pushNeutral"] == 1;
			$account->setName($_POST["name"]);
			$account->setPushIn($pushIn);
			$account->setPushOut($pushOut);
			$account->setPushNeutral($pushNeutral);
			
			// Settings.
			if ($pushIn) {
				$settings["PUSH_IN_URL"] = $_POST["pushInUrl"];
				$settings["PUSH_IN_SECRET"] = $_POST["pushInSecret"];
			} else {
				$settings["PUSH_IN_URL"] = "";
				$settings["PUSH_IN_SECRET"] = "";
			}

			if ($pushOut) {
				$settings["PUSH_OUT_URL"] = $_POST["pushOutUrl"];
				$settings["PUSH_OUT_SECRET"] = $_POST["pushOutSecret"];
			} else {
				$settings["PUSH_OUT_URL"] = "";
				$settings["PUSH_OUT_SECRET"] = "";
			}

			if ($pushNeutral) {
				$settings["PUSH_NEUTRAL_URL"] = $_POST["pushNeutralUrl"];
				$settings["PUSH_NEUTRAL_SECRET"] = $_POST["pushNeutralSecret"];
			} else {
				$settings["PUSH_NEUTRAL_URL"] = "";
				$settings["PUSH_NEUTRAL_SECRET"] = "";
			}

			$settings["CERTIFICATE"] = $_POST["certificate"];
			$settings["ORGANISATION"] = $_POST["organisation"];
			$settings["LOGIN"] = $_POST["login"];
			$settings["PASSWORD"] = $_POST["password"];
			$settings["LAST_SYNC"] = 1293872400; // dummy value to start somewhere
			
			$account->setSettings($settings);
			$account->update();
			print "OK";
		} else {
			print "FAIL";
		}
	}

	public function AJAXGetAccountList() {
		$slow = $this->getTemplate();

		$accounts = \PLUSPEOPLE\PesaPi\Base\AccountFactory::factoryAll();
		foreach ($accounts AS $account) {
			$settings = $account->getSettings();

			$slow->assign(array("DOMAIN" => $_SERVER["SERVER_NAME"],
													"ID" => $account->getId(),
													"NAME" => $account->getName(),
													"TYPE" => $account->getFormatedType(),
													"IDENTIFIER" => $account->getIdentifier(),
													"PUSH_IN" => $account->getPushIn() ? $settings["PUSH_IN_URL"] . " (secret: " . $settings["PUSH_IN_SECRET"] . ")" : "OFF",
													"PUSH_OUT" => $account->getPushOut() ? $settings["PUSH_OUT_URL"] . " (secret: " . $settings["PUSH_OUT_SECRET"] . ")" : "OFF",
													"PUSH_NEUTRAL" => $account->getPushNeutral() ? $settings["PUSH_NEUTRAL_URL"] . " (secret: " . $settings["PUSH_NEUTRAL_SECRET"] . ")" : "OFF",
													"SYNC_SECRET" => $settings["SYNC_SECRET"]
													));

			if ($account->getType() == \PLUSPEOPLE\PesaPi\Base\Account::MPESA_PAYBILL) {
				$slow->assign(array("CERTIFICATE" => $settings["CERTIFICATE"],
														"ORGANISATION" => $settings["ORGANISATION"],
														"LOGIN" => $settings["LOGIN"],
														"PASSWORD" => $settings["PASSWORD"],
														"IPN" => "NOP"));

				// Check if certificate file exists and is readable
				if (trim($settings["CERTIFICATE"]) != "") {
					$certificate = @file_get_contents($settings["CERTIFICATE"]);

					if ($certificate != "") {
						$slow->parse("Account_mpesa_paybill_certificate_exists");
						$slow->parse("Account_mpesa_paybill_certificate_test");
					} else {
						$slow->parse("Account_mpesa_paybill_certificate_exists_not");
					}
				}

				$slow->parse("Account_mpesa_paybill");
				
			} else {
				$slow->parse("Account_smssync");
			}
			
			$slow->parse("Account");
		}


		$slow->parse("Accounts_wrap");
		$slow->slowPrint("Accounts_wrap");
	}

	public function AJAXTestCertificate() {
		$identifier = $_POST["identifier"];

		$account = \PLUSPEOPLE\PesaPi\Base\AccountFactory::factoryByIdentifier($identifier);
		if (is_object($account)) {
			$settings = $account->getSettings();

			if (trim($settings["CERTIFICATE"]) != "") {
				$cookieFile = tmpfile();

				$curl = curl_init("https://ke.m-pesa.com");
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($curl, CURLOPT_COOKIESESSION, true);
				curl_setopt($curl, CURLOPT_COOKIEJAR, $cookieFile);
				curl_setopt($curl, CURLOPT_HEADER, true);
				curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

				curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($curl, CURLOPT_SSLCERT, $settings["CERTIFICATE"]);
				curl_setopt($curl, CURLOPT_SSLCERTTYPE, "PEM");

				curl_setopt($curl, CURLOPT_URL, "https://ke.m-pesa.com/ke/");
				curl_setopt($curl, CURLOPT_POST, false);
				curl_setopt($curl, CURLOPT_COOKIEFILE, $cookieFile); 

				$searchPage = curl_exec($curl);

				if ($searchPage != "" AND stripos($searchPage, 'Welcome to the M-PESA Administration Website') !== FALSE) {
					print "OK";
					exit();
				} else {
					print "FAIL";
					exit();
				}
			}
		}
		print "FAIL";
		exit();
	}

	public function request() {
		$slow = $this->getTemplate();


		if (!$this->credentialsOk()) {

			$slow->parse("Setup_credentials");
		} elseif (!$this->dbStructureOk()) {

			$slow->parse("Setup_database");
		} else {

			$slow->parse("Setup_ok");
		}

	}

	protected function credentialsOk() {
		$config = \PLUSPEOPLE\PesaPi\Configuration::instantiate();
		$dbId = @mysql_pconnect($config->getConfig("DatabaseHostRead"), $config->getConfig("DatabaseUserRead"), $config->getConfig("DatabasePasswordRead"), true);
		
		if ($dbId <= 0) {
			return false;
		}

		if (!mysql_select_db($config->getConfig("DatabaseDatabaseRead"), $dbId)) {
			return false;
		}
		
		return true;
	}

	protected function dbStructureOk() {
		$db = \PLUSPEOPLE\PesaPi\Base\Database::instantiate(\PLUSPEOPLE\PesaPi\Base\Database::TYPE_WRITE);

		$required = array("pesapi_account", "pesapi_payment");
		$tables = array();
		if ($result = $db->query("SHOW TABLES;")) {
			while ($row = $result->fetch_row()) {
				$tables[] = $row[0];
			}
		}

		foreach ($required AS $table) {
			if (!in_array($table, $tables)) {
				return false;
			}
		}

		return true;
	}

	protected function createDbStructure() {
		$query1 = 'CREATE TABLE IF NOT EXISTS `pesapi_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `identifier` varchar(255) NOT NULL,
  `push_in` tinyint(1) NOT NULL,
  `push_out` tinyint(1) NOT NULL,
  `push_neutral` tinyint(1) NOT NULL,
  `settings` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `type_index` (`type`),
  KEY `definedby` (`identifier`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;';

		$query2 = 'CREATE TABLE IF NOT EXISTS `pesapi_payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `super_type` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `receipt` varchar(255) NOT NULL,
  `time` datetime NOT NULL,
  `phonenumber` varchar(45) NOT NULL,
  `name` varchar(255) NOT NULL,
  `account` varchar(255) NOT NULL,
  `status` int(11) NOT NULL,
  `amount` bigint(20) NOT NULL,
  `post_balance` bigint(20) NOT NULL,
  `note` varchar(255) NOT NULL,
  `transaction_cost` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `type_index` (`type`),
  KEY `name_index` (`name`),
  KEY `phone_index` (`phonenumber`),
  KEY `time_index` (`time`),
  KEY `super_index` (`super_type`),
  KEY `fk_mpesapi_payment_account_idx` (`account_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;';

		$query3 = 'ALTER TABLE `pesapi_payment` ADD CONSTRAINT `fk_mpesapi_payment_account` FOREIGN KEY (`account_id`) REFERENCES `pesapi_account` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;';

		$db = \PLUSPEOPLE\PesaPi\Base\Database::instantiate(\PLUSPEOPLE\PesaPi\Base\Database::TYPE_WRITE);

		return $db->query($query1) AND $db->query($query2) AND $db->query($query3);
	}

}
$template = new configtool();
$template->handleRequest();

?>

