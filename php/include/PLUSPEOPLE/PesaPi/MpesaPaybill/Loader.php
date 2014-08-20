<?php
/*	Copyright (c) 2011-2014, PLUSPEOPLE Kenya Limited. 
		All rights reserved.

		Redistribution and use in source and binary forms, with or without
		modification, are permitted provided that the following conditions
		are met:
		1. Redistributions of source code must retain the above copyright
		   notice, this list of conditions and the following disclaimer.
		2. Redistributions in binary form must reproduce the above copyright
		   notice, this list of conditions and the following disclaimer in the
		   documentation and/or other materials provided with the distribution.
		3. Neither the name of PLUSPEOPLE nor the names of its contributors 
		   may be used to endorse or promote products derived from this software 
		   without specific prior written permission.
		
		THIS SOFTWARE IS PROVIDED BY THE REGENTS AND CONTRIBUTORS ``AS IS'' AND
		ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
		IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
		ARE DISCLAIMED.  IN NO EVENT SHALL THE REGENTS OR CONTRIBUTORS BE LIABLE
		FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
		DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS
		OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
		HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
		LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
		OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF
		SUCH DAMAGE.

		File originally by Michael Pedersen <kaal@pluspeople.dk>
 */
namespace PLUSPEOPLE\PesaPi\MpesaPayBill;

class Loader {
	protected $baseUrl = "https://ke.m-pesa.com";
	protected $config = null;
	protected $curl = null;
	protected $cookieFile = null;
	protected $account = null;
	protected $settings = array();

	public function __construct($account) {
		$this->account = $account;
		$this->settings = $account->getSettings();
		$this->config = \PLUSPEOPLE\PesaPi\Configuration::instantiate();
		if ($this->config->getConfig("SimulationMode")) {
			$this->baseUrl = "http://www.pesapi.ke";
		}

		$this->cookieFile = tmpfile();

		$this->curl = curl_init($this->baseUrl);
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->curl, CURLOPT_COOKIESESSION, true);
		curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->cookieFile);
		curl_setopt($this->curl, CURLOPT_HEADER, true);
		curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);

		if (TRUE OR !$this->config->getConfig("SimulationMode")) {
			curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($this->curl, CURLOPT_SSLCERT, $this->settings["CERTIFICATE"]);
			curl_setopt($this->curl, CURLOPT_SSLCERTTYPE, "PEM");
		}
	}

	public function retrieveData($fromTime) {
		$fromTime = (int)$fromTime;
		$pages = array();
		if ($fromTime > 0) {
			$search = $this->loadSearchPage();
			if (preg_match("/<h3>Operator Password Change<\/h3>/", $search) > 0) {
				$search = $this->changePassword($search);
			} elseif (preg_match("/Your account has been locked\.\s+Please contact your administrator to have your account unlocked\./", $search) > 0) {
				$toEmail = $this->config->getConfig("AdminEmail");
				if ($toEmail != "") {
					$message = "Your paybill account has been locked - you need to take manual action to have it unlocked";
					mail($toEmail, "PesaPi alert", $message);
				}
				exit();
			}
			$pages = $this->loadResults($search, $fromTime);
		}
		// return the reverse array - we want the oldest data first.
		return array_reverse($pages);
	}

  ////////////////////////////////////////////////////////////////
  // private functions
  ////////////////////////////////////////////////////////////////
	private function loadSearchPage() {
		$postData = 
			'__LASTFOCUS=' .
			'&__EVENTTARGET=' .
			'&__EVENTARGUMENT=' .
			'&__VIEWSTATE=' . 
			'&LoginCtrl%24UserName=' . urlencode($this->settings["LOGIN"]) . 
			'&LoginCtrl%24Password=' . urlencode($this->settings["PASSWORD"]) .
			'&LoginCtrl%24txtOrganisationName=' . urlencode($this->settings["ORGANISATION"]) . 
			'&LoginCtrl%24LoginButton=Log+In';

		curl_setopt($this->curl, CURLOPT_URL, $this->baseUrl . "/ke/default.aspx?ReturnUrl=%2fke%2fMain%2fhome2.aspx%3fMenuID%3d1826&MenuID=1826");
		curl_setopt($this->curl, CURLOPT_POST, true);
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postData); 
		curl_setopt($this->curl, CURLOPT_COOKIEFILE, $this->cookieFile); 

		$searchPage = curl_exec($this->curl);
		// 		file_put_contents("STEP0_FORM.txt", $searchPage);
		// TODO: missing error detection
		return $searchPage;
	}

	private function loadResults($searchPage, $fromTime) {
		$fromTime = (int)$fromTime;
		$pages = array();
		if ($fromTime > 0) {
			$viewState = $this->getViewState($searchPage);
			$accounts = $this->findAccounts($searchPage);
			$account = $accounts[2];
			$now = time();

			$postData = 
				'__VIEWSTATE=' . urlencode($viewState) .
				'&ctl00$Main$ctl00$ctlDatePicker$dlpagesize_Input=' . '500' .
				'&ctl00$Main$ctl00$ctlDatePicker$dlpagesize_text=' . '500' .
				'&ctl00$Main$ctl00$ctlDatePicker$dlpagesize_value=' . '500' .
				'&ctl00$Main$ctl00$ctlDatePicker$dlpagesize_index=' . '3' .
				'&ctl00_Main_ctl00_ctlDatePicker_datePickerStartDate=' . date("Y-m-d", $fromTime) . 
				'&ctl00$Main$ctl00$ctlDatePicker$datePickerStartDate$dateInput=' . urlencode(date("Y-m-d H:i:s", $fromTime)) .
				'&ctl00$Main$ctl00$ctlDatePicker$datePickerStartDate$dateInput_TextBox=' . date("Y-m-d", $fromTime) .
				'&ctl00_Main_ctl00_ctlDatePicker_datePickerStartDate_calendar_SD=' . urlencode('[]') .
				'&ctl00_Main_ctl00_ctlDatePicker_datePickerEndDate=' . date("Y-m-d", $now) . 
				'&ctl00$Main$ctl00$ctlDatePicker$datePickerEndDate$dateInput=' . urlencode(date("Y-m-d H:i:s", $now)) .
				'&ctl00$Main$ctl00$ctlDatePicker$datePickerEndDate$dateInput_TextBox=' . date("Y-m-d", $now) .
				'&ctl00_Main_ctl00_ctlDatePicker_datePickerEndDate_calendar_SD=' . urlencode('[]') .
				'&ctl00$Main$ctl00$cbAccountType_Input=' . urlencode($account[1]) .
				'&ctl00$Main$ctl00$cbAccountType_text=' . urlencode($account[1]) .
				'&ctl00$Main$ctl00$cbAccountType_value=' . urlencode($account[2]) . 
				'&ctl00$Main$ctl00$cbAccountType_index=' . $account[0] . 
				'&ctl00$Main$ctl00$rblTransType=' . 'All' .
				'&ctl00$Main$ctl00$btnSearch=' . 'Search' .
				'&ctl00$Main$ctl00$cpeExpandedFilter_ClientState=' . '' . // unkown 
				'&ctl00_Main_ctl00_AccountStatementGrid1_dgStatementPostDataValue=' . '' // unkown
				;
			
			curl_setopt($this->curl, CURLOPT_URL, $this->baseUrl . "/ke/Main/home2.aspx?MenuID=1826");
			curl_setopt($this->curl, CURLOPT_POST, true);
			curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postData); 
			curl_setopt($this->curl, CURLOPT_COOKIEFILE, $this->cookieFile); 

			// TODO: needs to retrieve the following pages, in case there is more than 500 entries
			$result = curl_exec($this->curl);
			//  		file_put_contents("STEP1_STATEMENT.txt", $result);
			// TODO: missing error detection
			$pages[] = $result;
		}
		return $pages;
	}

	private function getViewState($input) {
		$temp = array();
		preg_match("/(?<=__VIEWSTATE\" value=\")(?<val>.*?)(?=\")/", $input, $temp);
		return isset($temp[1]) ? $temp[1] : "";
	}

	/*
		Finds all the accounts available.
		Returns a 2-dimensional array with the following format:
		[[offset, name, account-no]]
	 */
	private function findAccounts($input) {
		$results = array();
		$temp = array();
		preg_match('/ctl00\$Main\$ctl00\$cbAccountType.+ScrollDownDisabled\.gif"\},(\[.+])\);<\/script>/U', $input, $temp);

		if (isset($temp[1])) {
			$accounts = json_decode($temp[1]);
			$count = 0;
			foreach ($accounts AS $account) {
				$results[] = array($count++, $account->Text, $account->Value);
			}
		}
		return $results;
	}

	private function changePassword($page) {
		$viewState = $this->getViewState($page);
		$oldPassword = $this->getPassword();
		
		$toEmail = $this->config->getConfig("AdminEmail");
		if ($toEmail != "") {
			$message = "PesaPi is changing your password";
			mail($toEmail, "PesaPi information", $message);
		}

		// generate new pw - NOT very secure!
		$temp = array();
		preg_match_all("/(.*)(\d+)$/U", $oldPassword, $temp);
		if (isset($temp[1][0]) AND isset($temp[2][0])) {
			$newPassword = $temp[1][0] . (string)($temp[2][0] + 1);
		} else {
			$newPassword = $oldPassword . "2";
		}
		$this->setPassword($newPassword);

		$postData = 
			'__VIEWSTATE=' . urlencode($viewState) .
			'&OperatorPasswordChangeControl1$txtPassword=' . urlencode($newPassword) . 
			'&OperatorPasswordChangeControl1$txtConfirm=' . urlencode($newPassword) . 
			'&OperatorPasswordChangeControl1$txtSecurityQuestion=' . 'favorite country' . 
      '&OperatorPasswordChangeControl1$txtSecurityAnswer=' . 'kenya' . // HARDCODED
			'&btnUpdatePassword=' . urlencode('Update Password'); 
		
		curl_setopt($this->curl, CURLOPT_URL, $this->baseUrl . "/ke/default.aspx?ReturnUrl=%2fke%2fMain%2fhome2.aspx%3fMenuID%3d1826&amp;MenuID=1826");
		curl_setopt($this->curl, CURLOPT_POST, true);
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postData); 
		curl_setopt($this->curl, CURLOPT_COOKIEFILE, $this->cookieFile); 

		$result = curl_exec($this->curl); // could potentially detect if the new pw is refused
		return $result;
	}

	private function getPassword() {
		return $this->settings["PASSWORD"];
	}

	private function setPassword($input) {
		$this->settings["PASSWORD"] = $input;
		$this->account->setSettings($this->settings);
		return $this->account->update();
	}
	
}

?>