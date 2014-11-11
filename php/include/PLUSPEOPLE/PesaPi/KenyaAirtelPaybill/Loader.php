<?php
/*	Copyright (c) 2014, PLUSPEOPLE Kenya Limited. 
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
namespace PLUSPEOPLE\PesaPi\KenyaAirtelPaybill;

class Loader {
	protected $baseUrl = "https://41.223.56.58:7556";
	protected $config = null;
	protected $curl = null;
	protected $cookieFile = null;
	protected $account = null;
	protected $settings = array();

	public function __construct($account) {
		$this->account = $account;
		$this->settings = $account->getSettings();

		$this->cookieFile = tmpfile();

		$this->curl = curl_init($this->baseUrl);
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->curl, CURLOPT_COOKIESESSION, true);
		curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->cookieFile);
		curl_setopt($this->curl, CURLOPT_HEADER, true);
		curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($this->curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.6; rv:33.0) Gecko/20100101 Firefox/33.0");
		curl_setopt($this->curl, CURLOPT_HTTPHEADER, array('Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', 
																											 'Accept-Language: en-US,en;q=0.5'));

	}

	public function retrieveData($fromTime) {
		////  DEBUG
		//		$rawtext = file_get_contents('STEP3_RESULT.txt');
		//		return array($rawtext);

		$fromTime = (int)$fromTime;
		$pages = array();
		if ($fromTime > 0) {
			$login = $this->login();
			if (false) {
				// change password
				$this->changePassword($login);
			}
			$searchForm = $this->loadSearchForm($login);
 			$pages = $this->loadResults($searchForm, $fromTime);
		}
		// return the reverse array - we want the oldest data first.
		return array_reverse($pages);
	}

  ////////////////////////////////////////////////////////////////
  // private functions
  ////////////////////////////////////////////////////////////////
	private function login() {
		curl_setopt($this->curl, CURLOPT_URL, $this->baseUrl . "/Login.aspx");
		curl_setopt($this->curl, CURLOPT_POST, false);
		$first = curl_exec($this->curl);
 		file_put_contents("STEP0_LOGIN.txt", $first);

		$viewState = $this->getHidden($first);
		$postData = 
			'__EVENTTARGET=' . 
			'&__EVENTARGUMENT=' . 
			'&__VIEWSTATE=' . urlencode($viewState) .
			'&__PREVIOUSPAGE=VmAx5lrT-DxfU3L5JTkDo6-MsSLxDbUDPbv3Jf2rLJYNK6S-D0Layu8v0-Q4iqdhjdj-1r_OJ1bI3G7gZuFwoxJpbxd11LwAHWC9dsytjZk1' .
			'&__EVENTVALIDATION=%2FwEWDwK4vrG1BgKz5rnaCgKNodiVBgKxhoC7DAKg7duGAQLV4%2BWwBwLvuoeDDQKUrv2cBgLao5eqDQK%2BnYa6CgLxhcC%2FCwLvuo%2FqAgLJ4frZBwL90KKTCAKO9e%2BRAcr3OVzyza%2FzZgW90K8j%2BWLzSSl53QHO1SCkXe%2BPR9ND' .
			'&ctl00%24ContentPlaceHolder1%24txtCompany=' . urlencode($this->settings["COMPANY"]) . 
			'&ctl00%24ContentPlaceHolder1%24txtNickname=' . urlencode($this->settings["NICKNAME"]) .
			'&ctl00%24ContentPlaceHolder1%24txtUsername=' . urlencode($this->settings["USERNAME"]) . 
			'&ctl00%24ContentPlaceHolder1%24txtPassword=' . urlencode($this->settings["PASSWORD"]) . 
			'&ctl00%24ContentPlaceHolder1%24btnLogin.x=' . (String)rand(1,69) . 
			'&ctl00%24ContentPlaceHolder1%24btnLogin.y=' . (String)rand(1,19);

		curl_setopt($this->curl, CURLOPT_URL, $this->baseUrl . "/Login.aspx");
		curl_setopt($this->curl, CURLOPT_POST, true);
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postData); 
		curl_setopt($this->curl, CURLOPT_COOKIEFILE, $this->cookieFile); 

		$login = curl_exec($this->curl);
 		file_put_contents("STEP1_POST_LOGIN.txt", $login);
		// TODO: missing error detection
		return $login;
	}

	private function loadSearchForm($searchPage) {
		$viewState = $this->getHidden($searchPage);
		$prevPage = $this->getHidden($searchPage, '__PREVIOUSPAGE');
		$eventValidation = $this->getHidden($searchPage, '__EVENTVALIDATION');

		$postData = 
			'__EVENTTARGET=ctl00%24LnkReports' .
			'&__EVENTARGUMENT=' .
			'&__VIEWSTATE=' . urlencode($viewState) .
			'&__PREVIOUSPAGE=' . urlencode($prevPage) . 
			'&__EVENTVALIDATION=' . urlencode($eventValidation);

		curl_setopt($this->curl, CURLOPT_URL, $this->baseUrl . "/NewTransctionsReport.aspx");
		curl_setopt($this->curl, CURLOPT_POST, true);
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postData); 
		curl_setopt($this->curl, CURLOPT_COOKIEFILE, $this->cookieFile); 
		curl_setopt($this->curl, CURLOPT_REFERER, 'https://41.223.56.58:7556/NewZapMerchant.aspx');

		// TODO: needs to retrieve the following pages, in case there is more than 500 entries
		$searchForm = curl_exec($this->curl);
		file_put_contents("STEP2_STATEMENT.txt", $searchForm);

		return $searchForm;
	}

	private function loadResults($searchPage, $fromTime) {
		$pages = array();
		$viewState = $this->getHidden($searchPage);
		$prevPage = $this->getHidden($searchPage, '__PREVIOUSPAGE');
		$eventValidation = $this->getHidden($searchPage, '__EVENTVALIDATION');

		$postData = 
			'__EVENTTARGET=' .
			'&__EVENTARGUMENT=' .
			'&__LASTFOCUS=' .
			'&__VIEWSTATE=' . urlencode($viewState) .
			'&__PREVIOUSPAGE=' . urlencode($prevPage) . 
			'&__EVENTVALIDATION=' . urlencode($eventValidation) .
			'&ctl00%24ContentPlaceHolder1%24rdReportType=My+Reports' .
			'&ctl00%24ContentPlaceHolder1%24rdbTrans=FUNDS'.
			'&ctl00_ContentPlaceHolder1_dtFromDate_Raw=1414713600000' . // not done
			'&ctl00%24ContentPlaceHolder1%24dtFromDate=31-Oct-2014' . // not done
			'&ctl00_ContentPlaceHolder1_dtFromDate_DDDWS=0%3A0%3A12000%3A787%3A270%3A0%3A-10000%3A-10000' . // not done
			'&ctl00_ContentPlaceHolder1_dtFromDate_DDD_C_FNPWS=0%3A0%3A-1%3A-10000%3A-10000%3A0%3A0px%3A-10000' . // not done
			'&ctl00%24ContentPlaceHolder1%24dtFromDate%24DDD%24C=11%2F01%2F2014%3A10%2F31%2F2014' . // not done
			'&ctl00_ContentPlaceHolder1_dtTODate_Raw=1415491200000' . // not done
			'&ctl00%24ContentPlaceHolder1%24dtTODate=09-Nov-2014' . // not done
			'&ctl00_ContentPlaceHolder1_dtTODate_DDDWS=0%3A0%3A12000%3A1005%3A270%3A0%3A-10000%3A-10000' . // not done
			'&ctl00_ContentPlaceHolder1_dtTODate_DDD_C_FNPWS=0%3A0%3A-1%3A-10000%3A-10000%3A0%3A0px%3A-10000' . // not done
			'&ctl00%24ContentPlaceHolder1%24dtTODate%24DDD%24C=11%2F09%2F2014%3A11%2F09%2F2014' . // not done
			'&ctl00%24ContentPlaceHolder1%24btnViewAudit.x=' . (String)rand(1,79) .
			'&ctl00%24ContentPlaceHolder1%24btnViewAudit.y=' . (String)rand(1,24) .
			'&DXScript=1_23%2C2_21%2C2_28%2C2_20%2C1_27%2C1_44%2C1_41%2C2_15'; // not done

		curl_setopt($this->curl, CURLOPT_URL, $this->baseUrl . "/NewTransctionsReport.aspx");
		curl_setopt($this->curl, CURLOPT_POST, true);
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postData); 
		curl_setopt($this->curl, CURLOPT_COOKIEFILE, $this->cookieFile); 
		curl_setopt($this->curl, CURLOPT_REFERER, 'https://41.223.56.58:7556/NewTransctionsReport.aspx');

		// TODO: needs to retrieve the following pages, in case there is more than 500 entries
		$pages[] = curl_exec($this->curl);
		file_put_contents("STEP3_RESULT.txt", $pages[0]);

		return $pages;
	}

	private function getHidden($input, $tag="__VIEWSTATE") {
		$temp = array();
		preg_match("/(?<=" . $tag . "\" value=\")(?<val>.*?)(?=\")/", $input, $temp);
		return isset($temp[1]) ? $temp[1] : "";
	}

	private function changePassword($page) {
		$viewState = $this->getHidden($page);
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