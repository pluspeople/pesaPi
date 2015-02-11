<?php
/*	Copyright (c) 2011-2015, PLUSPEOPLE Kenya Limited. 
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
		
		THIS SOFTWARE IS PROVIDED BY THE REGENTS AND CONTRIBUTORS ''AS IS'' AND
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
namespace PLUSPEOPLE\PesaPi\MpesaPaybill;
use PLUSPEOPLE\PesaPi\Base\TransactionFactory;

class MpesaPaybill extends \PLUSPEOPLE\PesaPi\Base\Account { 
	public function getFormatedType() {
		return "Kenya - MPESA Paybill";
	}

	public function availableBalance($time = null) {
		$time = (int)$time;
		$settings = $this->getSettings();
		$lastSync = $settings["LAST_SYNC"];

		if ($lastSync < $time) {
			// we must have data all the way up to the specified time.
			$this->forceSyncronisation(); // NOT DONE: Needs to be on the account
		}

		$balance = TransactionFactory::factoryOneByTime($this, $time);
		if (is_object($balance)) {
			return $balance->getPostBalance();
		}
		return $amount;
	}

	public function locateByReceipt($receipt, $autoSync = true) {
		$payment = TransactionFactory::factoryByReceipt($this, $receipt);
		if ($payment == null AND $autoSync) {
			$this->forceSyncronisation();
			$payment = TransactionFactory::factoryByReceipt($this, $receipt);
		}
		return $payment;
	}


	public function locateByPhone($phone, $from=0, $until=0) {
		$settings = $this->getSettings();
		$lastSync = $settings["LAST_SYNC"];
		$config = \PLUSPEOPLE\PesaPi\Configuration::instantiate();
		$initSyncDate = strtotime($config->getConfig('MpesaInitialSyncDate'));

		// never go before initial sync date (not reliable to do so)
		if ($from <= 0 OR $from < $initSyncDate) {
			$from = $initSyncDate;
		}

		// default is up until last sync, and no later to enhance default performance
		if ($until <= 0) {
			$until = $lastSync;
		}

		if ($until > $lastSync) {
			$this->forceSyncronisation();
		}
		return TransactionFactory::factoryByPhone($phone, $from, $until);
	}

	public function locateByName($name, $from = 0, $until = 0) {
		$settings = $this->getSettings();
		$lastSync = $settings["LAST_SYNC"];
		$config = \PLUSPEOPLE\PesaPi\Configuration::instantiate();
		$initSyncDate = strtotime($config->getConfig('MpesaInitialSyncDate'));

		// never go before initial sync date (not reliable to do so)
		if ($from <= 0 OR $from < $initSyncDate) {
			$from = $initSyncDate;
		}
		if ($until <= 0) {
			$until = $lastSync;
		}

		// default is up until last sync, and no later to enhance default performance
		if ($until > $lastSync) {
			$this->forceSyncronisation();
		}
		return TransactionFactory::factoryByName($this, $name, $from, $until);
	}

	public function locateByAccount($account, $from=0, $until=0) {
		$settings = $this->getSettings();
		$lastSync = $settings["LAST_SYNC"];
		$config = \PLUSPEOPLE\PesaPi\Configuration::instantiate();
		$initSyncDate = strtotime($config->getConfig('MpesaInitialSyncDate'));

		// never go before initial sync date (not reliable to do so)
		if ($from <= 0 OR $from < $initSyncDate) {
			$from = $initSyncDate;
		}
		if ($until <= 0) {
			$until = $lastSync;
		}

		// default is up until last sync, and no later to enhance default performance
		if ($until > $lastSync) {
			$this->forceSyncronisation();
		}
		return TransactionFactory::factoryByAccount($this, $account, $from, $until);
	}

	public function locateByTimeInterval($from, $until, $type) {
		$type = (int)$type;
		$settings = $this->getSettings();
		$lastSync = $settings["LAST_SYNC"];
		$config = \PLUSPEOPLE\PesaPi\Configuration::instantiate();
		$initSyncDate = strtotime($config->getConfig('MpesaInitialSyncDate'));

		// never go before initial sync date (not reliable to do so)
		if ($from <= 0 OR $from < $initSyncDate) {
			$from = $initSyncDate;
		}
		if ($until <= 0) {
			$until = $lastSync;
		}

		// default is up until last sync, and no later to enhance default performance
		if ($until > $lastSync) {
			$this->forceSyncronisation();
		}
		return TransactionFactory::factoryByTimeInterval($from, $until, $type);
	}


	public function forceSyncronisation() {
		// determine the start time
		$settings = $this->getSettings();
		$lastSync = $settings["LAST_SYNC"];
		$now = time();

		// perform file fetch
		$loader = new Loader($this);
		$pages = $loader->retrieveData($lastSync);
		// perform analysis/scrubbing
		$scrubber = new Scrubber();
		foreach ($pages AS $page) {
			$rows = $scrubber->scrubRows($page);
			// save data to database
			foreach ($rows AS $row) {
				$tuple = Transaction::updateData($row, $this);
				if ($tuple[1] AND is_object($tuple[0])) {
					$this->handleCallback($tuple[0]);
				}
			}
		}

		// save last entry time as last sync - but only if any is found.
		// this way we are safeguarded against MPESA fallouts like the one of 3-5 November 2014
		$lastFound = \PLUSPEOPLE\PesaPi\Base\TransactionFactory::factoryOneByTime($this, $now);
		if (is_object($lastFound)) {
			$settings["LAST_SYNC"] = $lastFound->getTime();
			$this->setSettings($settings);
			$this->update();
		}
	}


	public function initTransaction($id, $initValues = null) {
		return new Transaction($id, $initValues);
	}

	public function importIPN($get) {
		if (strpos($get['text'], ' received from ') !== FALSE) {
			$temp = array("SUPER_TYPE" => Transaction::MONEY_IN,
										"TYPE" => Transaction::MPESA_PAYBILL_PAYMENT_RECIEVED,
										"RECEIPT" => $get['mpesa_code'],
										"TIME" => Scrubber::dateInput($get['tstamp']),
										"PHONE" => '0' . substr($get['mpesa_msisdn'], -9),
										"NAME" => $get['mpesa_sender'],
										"ACCOUNT" => $get['mpesa_acc'],
										"STATUS" => Transaction::STATUS_COMPLETED,
										"AMOUNT" => Scrubber::numberInput($get['mpesa_amt']),
										"BALANCE" => 0,
										"NOTE" => $get['text'],
										"COST" => 0);

			if ($temp['AMOUNT'] > 0 AND $temp['RECEIPT'] != "") {
				$tuple = Transaction::updateData($temp, $this);
				
				if ($tuple[1]) {
					// Callback if needed
					$this->handleCallback($tuple[0]);
				}
				return $tuple[0];
			}
			return null;

		} elseif (strpos($get['text'], ' transferred from Utility Account to Working Account.') !== FALSE) {
			$temp = array("SUPER_TYPE" => Transaction::MONEY_IN,
										"TYPE" => Transaction::MPESA_PAYBILL_TRANSFER_FROM_UTILITY,
										"RECEIPT" => $get['mpesa_code'],
										"TIME" => Scrubber::dateInput($get['tstamp']),
										"PHONE" => '',
										"NAME" => '',
										"ACCOUNT" => '',
										"STATUS" => Transaction::STATUS_COMPLETED,
										"AMOUNT" => Scrubber::numberInput($get['mpesa_amt']), // NOT DONE
										"BALANCE" => 0, // NOT DONE
										"NOTE" => $get['text'],
										"COST" => 0);

			if ($temp['AMOUNT'] > 0 AND $temp['RECEIPT'] != "") {
				$tuple = Transaction::updateData($temp, $this);
				
				if ($tuple[1]) {
					// Callback if needed
					$this->handleCallback($tuple[0]);
				}
				return $tuple[0];
			}
			return null;

		} else {
			// Unknown transaction.
			$temp = array("SUPER_TYPE" => Transaction::MONEY_NEUTRAL,
										"TYPE" => Transaction::MPESA_PAYBILL_UNKOWN,
										"RECEIPT" => $get['mpesa_code'],
										"TIME" => Scrubber::dateInput($get['tstamp']),
										"PHONE" => '',
										"NAME" => '',
										"ACCOUNT" => '',
										"STATUS" => Transaction::STATUS_COMPLETED,
										"AMOUNT" => 0,
										"BALANCE" => 0,
										"NOTE" => serialize($get),
										"COST" => 0);

			if ($temp['AMOUNT'] > 0 AND $temp['RECEIPT'] != "") {
				$tuple = Transaction::updateData($temp, $this);
				
				if ($tuple[1]) {
					// Callback if needed
					$this->handleCallback($tuple[0]);
				}
				return $tuple[0];
			}
			return null;
		}
	}

}

?>