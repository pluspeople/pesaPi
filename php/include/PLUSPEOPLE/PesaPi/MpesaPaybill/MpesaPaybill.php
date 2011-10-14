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
use PLUSPEOPLE\PesaPi\Base\Database;
use PLUSPEOPLE\PesaPi\Base\TransactionFactory;

class MpesaPaybill extends \PLUSPEOPLE\PesaPi\Base\Account { 

	public function availableBalance($time = null) {
		$time = (int)$time;
		$lastSyncSetting = \PLUSPEOPLE\PesaPi\Base\SettingFactory::FactoryByName("LastSync");
		$lastSync = $lastSyncSetting->getValue();

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

	public function locateByReceipt($receipt) {
		$payment = TransactionFactory::factoryByReceipt($this, $receipt);
		if ($payment == null) {
			$this->forceSyncronisation();
			$payment = TransactionFactory::factoryByReceipt($this, $receipt);
		}
		return $payment;
	}


	public function locateByPhone($phone, $from=0, $until=0) {
		$lastSyncSetting = \PLUSPEOPLE\PesaPi\Base\SettingFactory::FactoryByName("LastSync");
		$lastSync = $lastSyncSetting->getValue();
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
		$lastSyncSetting = \PLUSPEOPLE\PesaPi\Base\SettingFactory::FactoryByName("LastSync");
		$lastSync = $lastSyncSetting->getValue();
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
		$lastSyncSetting = \PLUSPEOPLE\PesaPi\Base\SettingFactory::FactoryByName("LastSync");
		$lastSync = $lastSyncSetting->getValue();
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
		$lastSyncSetting = \PLUSPEOPLE\PesaPi\Base\SettingFactory::FactoryByName("LastSync");
		$lastSync = $lastSyncSetting->getValue();
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
		return true;

		// determine the start time
		$lastSyncSetting = \PLUSPEOPLE\PesaPi\Base\SettingFactory::FactoryByName("LastSync");
		$lastSync = $lastSyncSetting->getValue();
		$config = \PLUSPEOPLE\PesaPi\Configuration::instantiate();
		$initSyncDate = strtotime($config->getConfig('MpesaInitialSyncDate'));

		if ($lastSync <= $initSyncDate) {
			$startSyncTime = $initSyncDate;
		} else {
			$startSyncTime = $lastSync;
		}
		$startSyncTime -= 1;
		$now = time();

		// perform file fetch
		$loader = new MpesaPaybill\Loader();
		$pages = $loader->retrieveData($startSyncTime);

		// perform analysis/scrubbing
		$scrubber = new MpesaPaybill\Scrubber();
		foreach ($pages AS $page) {
			$rows = $scrubber->scrubRows($page);
			// save data to database
			foreach ($rows AS $row) {
				$payment = Transaction::import($row);
				if (is_object($payment)) {
					$this->handleCallback($payment);
				}
			}
		}

		// save last entry time as last sync
		$this->lastSyncSetting->setValueDate($now);
		$this->lastSyncSetting->update();
	}


	public function initTransaction($id, $initValues = null) {
		return new Transaction($id, $initValues);
	}


}

?>