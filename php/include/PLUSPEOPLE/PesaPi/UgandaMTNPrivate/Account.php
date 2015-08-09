<?php
/*	Copyright (c) 2015, PLUSPEOPLE Kenya Limited. 
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
namespace PLUSPEOPLE\PesaPi\UgandaMTNPrivate;
use PLUSPEOPLE\PesaPi\Base\Database;
use PLUSPEOPLE\PesaPi\Base\TransactionFactory;

class Account extends \PLUSPEOPLE\PesaPi\Base\Account { 
	public function getFormatedType() {
		return "Uganda - MTN";
	}

	public function availableBalance($time = null) {
		$time = (int)$time;
		if (0 == $time) {
			$time = time();
		}

		$balance = \PLUSPEOPLE\PesaPi\Base\TransactionFactory::factoryOneByTime($this, $time);
		if (is_object($balance)) {
			return $balance->getPostBalance();
		}
		return 0;
	}

	public function locateByReceipt($receipt) {
		return TransactionFactory::factoryByReceipt($this, $receipt);
	}

	public function initTransaction($id, $initValues = null) {
		return new Transaction($id, $initValues);
	}

	public function importTransaction($message) {
		if ($message != "") {
			$parser = new Parser();
			$temp = $parser->parse($message);

			$transaction = Transaction::createNew($this->getId(), $temp['SUPER_TYPE'], $temp['TYPE']);
			$transaction->setReceipt($temp['RECEIPT']);
			$transaction->setTime($temp["TIME"]);
			$transaction->setPhonenumber($temp['PHONE']);
			$transaction->setName($temp['NAME']);
			$transaction->setAccount($temp['ACCOUNT']);
			$transaction->setStatus($temp['STATUS']);
			$transaction->setAmount($temp['AMOUNT']);
			$transaction->setPostBalance($temp['BALANCE']);
			$transaction->setNote($temp['NOTE']);
 			$transaction->setTransactionCost($temp['COST']);
			
			$transaction->update();

			// Callback if needed
			$this->handleCallback($transaction);

			return $transaction;
		}
		return null;
	}

}

?>