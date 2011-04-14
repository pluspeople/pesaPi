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
namespace PLUSPEOPLE\Mpesapi;

/*
	This is the main interface to the Mpesa API.
	Features are collected here for simple interfacing by the user.
 */
class Mpesapi {
	protected $initSyncDate = 0;
	protected $lastSyncSetting = null;

	public function __construct() {
		$config = Configuration::instantiate();
		$this->initSyncDate = strtotime($config->getConfig('MpesaInitialSyncDate'));
		$this->lastSyncSetting = SettingFactory::FactoryByName("LastSync");
	}

	/* This method returns the balance of the mpesa account at the specified point in time.
		 If there are not transactions later than the specified time, then we can not gurantee 100%
		 that is is the exact balance - since there might be a transaction prior to the specified time
		 which we have not yet been informed about.
		 The specified time is represented in a unix timestamp.
	*/
	public function availableBalance($time = null) {
		$time = (int)$time;
		$lastSync = $this->lastSyncSetting->getValue();
		$amount = 0;

		if ($lastSync < $time) {
			// we must have data all the way up to the specified time.
			$this->forceSyncronisation();
		}
		
		$db = Database::instantiate(Database::TYPE_READ);
		$query = "SELECT post_balance
							FROM  mpesapi_payment
              WHERE time <= FROM_UNIXTIME('$time')
              ORDER BY time DESC
              LIMIT 0,1";

		if ($result = $db->query($query)) {
			if ($foo = $db->fetchObject($result)) {
				$amount = $foo->post_balance;
			}
			$db->freeResult($result);
		}

		return $amount;
	}

	/* Locate a particular transaction using the unique reciept number
		 that is send to the mobile user.
		 It is expected that this method will be the primary metod used 
		 for e-commerce shops
		 For extra security you might consider confirming that the phonenumber
		 of the returned transaction match the users phonenumber.
	*/
	public function locateByReciept($reciept) {
		$payment = PaymentFactory::factoryByReciept($reciept);
		if ($payment == null) {
			$this->forceSyncronisation();
			$payment = PaymentFactory::factoryByReciept($reciept);
		}
		return $payment;
	}

	/* This method locates all payments performed by a given phonenumber
		 within a given time-period (all payments from a particular phone).
		 If at all possible try not to use an until value all the way up 
		 until now since that will greatly enhance performance. 
	*/
	public function locateByPhone($phone, $from=0, $until=0) {
		$lastSync = $this->lastSyncSetting->getValue();

		// never go before initial sync date (not reliable to do so)
		if ($from <= 0 OR $from < $this->initSyncDate) {
			$from = $this->initSyncDate;
		}

		// default is up until last sync, and no later to enhance default performance
		if ($until <= 0) {
			$until = $lastSync;
		}

		if ($until > $lastSync) {
			$this->forceSyncronisation();
		}
		return PaymentFactory::factoryByPhone($phone, $from, $until);
	}

	/* this method locates all the payments by a specific client name
		 within a given time-period. The name is the name that Mpesa
		 has in its database.
		 Be alert that mobile users might have there records changed i.e. 
		 if Safaricom mistyped there name.
	*/
	public function locateByName($name, $from=0, $until=0) {
		$lastSync = $this->lastSyncSetting->getValue();

		// never go before initial sync date (not reliable to do so)
		if ($from <= 0 OR $from < $this->initSyncDate) {
			$from = $this->initSyncDate;
		}
		if ($until <= 0) {
			$until = $lastSync;
		}

		// default is up until last sync, and no later to enhance default performance
		if ($until > $lastSync) {
			$this->forceSyncronisation();
		}
		return PaymentFactory::factoryByName($name, $from, $until);
	}

	/* When using the paybill metod of a commercial account, the mobile user
		 enters an account-number. This method locates all payments in which 
		 a particular account name have been entered within a given timeframe.
		 Be alert that it is higly likely that users mistype the account 
     number: ie. "bb 123" vs. "bb123"
	*/
	public function locateByAccount($account, $from=0, $until=0) {
		$lastSync = $this->lastSyncSetting->getValue();

		// never go before initial sync date (not reliable to do so)
		if ($from <= 0 OR $from < $this->initSyncDate) {
			$from = $this->initSyncDate;
		}
		if ($until <= 0) {
			$until = $lastSync;
		}

		// default is up until last sync, and no later to enhance default performance
		if ($until > $lastSync) {
			$this->forceSyncronisation();
		}
		return PaymentFactory::factoryByAccount($account, $from, $until);
	}

	/* The method locates all payments within a particular time interval
		 plain and simple.
	*/
	public function locateByTimeInterval($from, $until, $type) {
		$type = (int)$type;
		$lastSync = $this->lastSyncSetting->getValue();

		// never go before initial sync date (not reliable to do so)
		if ($from <= 0 OR $from < $this->initSyncDate) {
			$from = $this->initSyncDate;
		}
		if ($until <= 0) {
			$until = $lastSync;
		}

		// default is up until last sync, and no later to enhance default performance
		if ($until > $lastSync) {
			$this->forceSyncronisation();
		}
		return PaymentFactory::factoryByTimeInterval($from, $until, $type);
	}

	/* This method performs a syncronisation between the safaricom database
		 and the local database. 
		 Warning: Although possible, you should never ever have to call this method directly
	*/
	public function forceSyncronisation() {
		// determine the start time
		$lastSync = $this->lastSyncSetting->getValue();
		if ($lastSync <= $this->initSyncDate) {
			$startSyncTime = $this->initSyncDate;
		} else {
			$startSyncTime = $lastSync;
		}
		$startSyncTime -= 1;
		$now = time();

		// perform file fetch
		$loader = new loader\Loader();
		$pages = $loader->retrieveData($startSyncTime);

		// perform analysis/scrubbing
		$scrubber = new scrubber\Scrubber();
		foreach ($pages AS $page) {
			$rows = $scrubber->scrubRows($page);
			// save data to database
			foreach ($rows AS $row) {
				Payment::import($row);
			}
		}

		// save last entry time as last sync
		$this->lastSyncSetting->setValueDate($now);
		$this->lastSyncSetting->update();
	}


	public function getErrorCode() {
		return 0;
	}

	public function getErrorMessage() {
		return "";
	}

}
?>