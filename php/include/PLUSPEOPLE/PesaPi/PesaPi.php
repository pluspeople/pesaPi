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
namespace PLUSPEOPLE\PesaPi;
use PLUSPEOPLE\PesaPi\Base\Database;
use PLUSPEOPLE\PesaPi\MpesaPaybill\Transaction;

/*
	This is the main interface to the Mpesa API.
	Features are collected here for simple interfacing by the user.
 */
class PesaPi {
	protected $config = null;

	public function __construct() {
		$this->config = Configuration::instantiate();
	}

	/* This method returns the balance of the mpesa account at the specified point in time.
		 If there are not transactions later than the specified time, then we can not gurantee 100%
		 that is is the exact balance - since there might be a transaction prior to the specified time
		 which we have not yet been informed about.
		 The specified time is represented in a unix timestamp.
	*/
	public function availableBalance($identifier = "", $time = null) {
		$time = (int)$time;
		$amount = 0;

		// first locate the correct accounts to opperate on.
		$accounts = $this->getAccount($identifier);

		// then sum all the account balances.
		foreach ($accounts AS $account) {
			$amount += $account->availableBalance($time);
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
	public function locateByReceipt($receipt, $identifier = "") {
		$transactions = array();

		// first locate the correct accounts to opperate on.
		$accounts = $this->getAccount($identifier);

		// then locate receipts
		foreach ($accounts AS $account) {
			$transactions = array_merge($transactions, $account->locateByReceipt($receipt));
		}
		return $transactions;
	}

	/* This method locates all payments performed by a given phonenumber
		 within a given time-period (all payments from a particular phone).
		 If at all possible try not to use an until value all the way up 
		 until now since that will greatly enhance performance. 
	*/
	public function locateByPhone($phone, $identifier = "", $from=0, $until=0) {
		$transactions = array();

		// first locate the correct accounts to opperate on.
		$accounts = $this->getAccount($identifier);

		// then locate receipts
		foreach ($accounts AS $account) {
			$transactions = array_merge($transactions, $account->locateByPhone($phone));
		}
		return $transactions;
	}

	/* this method locates all the payments by a specific client name
		 within a given time-period. The name is the name that Mpesa
		 has in its database.
		 Be alert that mobile users might have there records changed i.e. 
		 if Safaricom mistyped there name.
	*/
	public function locateByName($name, $identifier="", $from=0, $until=0) {
		$transactions = array();

		// first locate the correct accounts to opperate on.
		$accounts = $this->getAccount($identifier);

		// then locate receipts
		foreach ($accounts AS $account) {
			$transactions = array_merge($transactions, $account->locateByName($name, $from, $until));
		}
		return $transactions;
	}

	/* When using the paybill metod of a commercial account, the mobile user
		 enters an account-number. This method locates all payments in which 
		 a particular account name have been entered within a given timeframe.
		 Be alert that it is higly likely that users mistype the account 
     number: ie. "bb 123" vs. "bb123"
	*/
	public function locateByAccount($account, $identifier="", $from=0, $until=0) {
		$transactions = array();

		// first locate the correct accounts to opperate on.
		$accounts = $this->getAccount($identifier);

		// then locate receipts
		foreach ($accounts AS $account) {
			$transactions = array_merge($transactions, $account->locateByAccount($name, $from, $until));
		}
		return $transactions;
	}

	/* The method locates all payments within a particular time interval
		 plain and simple.
	*/
	public function locateByTimeInterval($from, $until, $type) {
		$transactions = array();

		// first locate the correct accounts to opperate on.
		$accounts = $this->getAccount($identifier);

		// then locate receipts
		foreach ($accounts AS $account) {
			$transactions = array_merge($transactions, $account->locateByTimeInterval($from, $until, $type));
		}
		return $transactions;
	}

	
	/* This method determines the different names that have been registered
		 using a given phone number
	*/
	public function locateName($phone) {
		$names = array();

		if ($phone != "") {
			$db = Database::instantiate(Database::TYPE_READ);
			$query = "SELECT DISTINCT name
							FROM  pesapi_payment
              WHERE phonenumber = '" . $db->dbIn($phone) . "'
              ORDER BY time DESC
              LIMIT 0,1";

			if ($result = $db->query($query)) {
				while ($foo = $db->fetchObject($result)) {
					$names[] = $db->dbOut($foo->name);
				}
				$db->freeResult($result);
			}
		}			
		return $names;
	}

	/* This method determines the different phone numbers that have been 
		 used by a person with a given name.
		 This might be extended to include someone with a similar name.
	*/
	public function locatePhone($name) {
		$phones = array();

		if ($name != "") {
			$db = Database::instantiate(Database::TYPE_READ);
			$query = "SELECT DISTINCT phonenumber
							FROM  pesapi_payment
              WHERE name = '" . $db->dbIn($name) . "'
              ORDER BY time DESC
              LIMIT 0,1";

			if ($result = $db->query($query)) {
				while ($foo = $db->fetchObject($result)) {
					$phones[] = $db->dbOut($foo->phonenumber);
				}
				$db->freeResult($result);
			}
		}			
		return $phones;
	}


	/* This method performs a syncronisation between the safaricom database
		 and the local database. 
	*/
	public function forceSyncronisation() {
		// first locate accounts.
		$accounts = $this->getAccount("");

		// then syncronise
		foreach ($accounts AS $account) {
			$account->forceSyncronisation();
		}
		return true;
	}


	public function getErrorCode() {
		return 0;
	}

	public function getErrorMessage() {
		return "";
	}

	public function getAccount($identifier) {
		if ($identifier != "") {
			$accounts = array();
			$account =  Base\AccountFactory::factoryByIdentifier($identifier);
			if (is_object($account)) {
				$accounts[] = $account;
			}
		} else {
			$accounts = Base\AccountFactory::factoryAll();
		}
		return $accounts;
	}

}
?>