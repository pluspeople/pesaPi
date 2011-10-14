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

		File originally by Michael Pedersen <kaal@pluspeople.dk>
 */
namespace PLUSPEOPLE\PesaPi;

class Configuration {
	static protected $singleton = null;

	protected $configArray=array(
		/*************************************************************************
                           General configuration options
		 *************************************************************************/
    // Enable this feature when in production - in order to disable debuginformation
		"ProductionMode"				        => false,

		// Enable this feature when you want to run the API against the simulator
		// The simulator does not use SSL and is more easy to get up and running.
		"SimulationMode"                => true,

		// Database settings follow - please note that they are repeated twice
		"DatabaseHostRead"						=> "localhost",
		"DatabaseUserRead"						=> "DB-USER",
		"DatabasePasswordRead"				=> "DB-PASSWORD",
		"DatabaseDatabaseRead"				=> "DB-DATABASE",
		"DatabaseHostWrite"						=> "localhost",
		"DatabaseUserWrite"						=> "DB-USER",
		"DatabasePasswordWrite"				=> "DB-PASSWORD",
		"DatabaseDatabaseWrite"				=> "DB-DATABASE",

 		"Version"											=> "0.1.0",
		/*************************************************************************
                            Callback feature of PesaPi
		 *************************************************************************/
		// Callback setup
		"PaymentReceivedPostback"     => false,
		"PaymentReceivedUrl"          => "http://www.domain.co.ke/payment_received.php",
		"PaymentReceivedSecret"       => "&secret=somethingspecial",



		/*************************************************************************
                          Payment systems configuration
		 *************************************************************************/
		//////////////////////////////////
		// MPESA PAYBILL ACCOUNTS
		//////////////////////////////////

		// Paybill information
		"MpesaCertificatePath"        => "/PATH/TO/CERTIFICATE.pem",
		"MpesaLoginName"              => "**LOGIN**",
		"MpesaPassword"               => "**PASSWORD**",
		"MpesaCorporation"            => "**ORGANISATION**",
    "MpesaInitialSyncDate"        => "2011-01-01",    
		"CookieFolderPath"            => ".",


		//////////////////////////////////
		// MPESA PRIVATE ACCOUNTS
		//////////////////////////////////

		// The identifier for the account matching the account identifier in the DB
		// for the particular account that you want to use for Private Mpesa transactions
		"MpesaPrivateIdentifier"      => "privatedefault",

		// The secret you have configured SMSSync to provide when posting SMS's into the system.
		"MpesaPrivateSecret"          => "abc",

	);


	/////////////////////////////////////////////
	public function getConfig($argument) {
		return $this->configArray[$argument];
	}

	public static function instantiate() {
		if (self::$singleton == null) {
			self::$singleton = new Configuration();
		}
		return self::$singleton;
	}
}


?>