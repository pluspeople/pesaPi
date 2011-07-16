<?php
/*	Copyright (c) 2011, PLUSPEOPLE Kenya Limited. 
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
 */
namespace PLUSPEOPLE\Pesapi\simulator;

require_once("../include/Configuration.php");
require_once("SlowTemplate.php");
require_once("WebUtility.php");

$slow = new SlowTemplate('template');
$slow->setTemplateFile('index.tpl');
session_start();

//////////////////////////////////////////////////////////////////////////////
// handle the submission
if ($_SERVER["REQUEST_METHOD"] == 'POST') {
	if ($_POST['__VIEWSTATE'] == $_SESSION['VIEWSTATE'] AND
			$_POST['LoginCtrl$UserName'] == 'test' AND 
			$_POST['LoginCtrl$Password'] == 'best' AND 
			$_POST['LoginCtrl$txtOrganisationName'] == 'PesaPi') {

		if ($_GET['ReturnUrl'] != "") {
			WebUtility::redirect($_GET['ReturnUrl']);
		} else {
			// if no return url we pretend they gave this one - since the simulator does not have the normal "entry" one (yet)
			WebUtility::redirect('/ke/Main/home2.aspx?MenuID=1826');
		}
	}
}

//////////////////////////////////////////////////////////////////////////////
// display the page
$view = WebUtility::viewstate(152);
$_SESSION['VIEWSTATE'] = $view;

$slow->assign(array("VIEWSTATE" => $view));


$slow->parse();
$slow->slowPrint();


?>