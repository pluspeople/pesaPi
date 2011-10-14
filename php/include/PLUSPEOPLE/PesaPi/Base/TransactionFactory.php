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
namespace PLUSPEOPLE\PesaPi\Base;

class TransactionFactory {
  //############### Properties ####################
  const SELECTLIST = "
SELECT id,
type,
super_type,
receipt,
UNIX_TIMESTAMP(time) AS time,
phonenumber,
name,
account,
status,
amount,
post_balance,
note,
transaction_cost ";

  //# # # # # # # # misc methods # # # # # # # #

  static public function factoryOne($id) {
    $db = Database::instantiate(Database::TYPE_READ);
    $id = (int)$id;

	  $query = TransactionFactory::SELECTLIST . "
							FROM  pesapi_payment
							WHERE	id = '$id' ";
		
		if ($result = $db->query($query) AND $foo = $db->fetchObject($result)) {
		  $returnval = new Transaction($foo->id, $foo);
		  $db->freeResult($result);
		  return $returnval;
		}
		return null;
  }

  static public function factoryOneByTime($account, $time) {
    $db = Database::instantiate(Database::TYPE_READ);
    $time = (int)$time;

		if (is_object($account)) {
			$query = TransactionFactory::SELECTLIST . "
							  FROM  pesapi_payment
                WHERE time <= FROM_UNIXTIME('$time')
                AND   account_id = '" . $account->getId() . "'
                ORDER BY time DESC
                LIMIT 0,1";
		
			if ($result = $db->query($query) AND $foo = $db->fetchObject($result)) {
				$returnval = $account->initTransaction($foo->id, $foo);
				$db->freeResult($result);
				return $returnval;
			}
		}
		return null;
  }

  static public function factoryByReceipt($account, $receipt, $phone=null) {
    $db = Database::instantiate(Database::TYPE_READ);
		$tempArray = array();

		if (is_object($account) AND $receipt != "") {
			$query = TransactionFactory::SELECTLIST . "
							FROM  pesapi_payment
							WHERE	account_id='" . $account->getId() . "'
              AND   receipt = '" . $db->dbIn($receipt) . "' ";
			if ($phone != null) {
				$query .= " AND phonenumber = '" . $db->dbIn($phone) . "' ";
			}
			$query .= " ORDER BY time DESC ";

			if ($result = $db->query($query)) {
				while($foo = $db->fetchObject($result)) {
					$tempArray[] = $account->initTransaction($foo->id, $foo);
				}
				$db->freeResult($result);
			}
		}
		return $tempArray;
  }

  static function factoryAll($account) {
    $db = Database::instantiate(Database::TYPE_READ);
		$tempArray = array();

		if (is_object($account)) {
			$query = TransactionFactory::SELECTLIST . "
							 FROM  pesapi_payment
               WHERE account_id = '" . $account->getId() . "'
               ORDER BY time DESC";
		
			if ($result = $db->query($query)) {
				while($foo = $db->fetchObject($result)) {
					$tempArray[] = $account->initTransaction($foo->id, $foo);
				}
				$db->freeResult($result);
			}
		}
		return $tempArray;
  }

  static function factoryByPhone($account, $phone, $from, $until) {
		$from = (int)$from;
		$until = (int)$until;
		$tempArray = array();

		if (is_object($account) AND $from > 0 AND $until > 0) {
			$db = Database::instantiate(Database::TYPE_READ);
			$query = TransactionFactory::SELECTLIST . "
							FROM  pesapi_payment
              WHERE phonenumber = '" . $db->dbIn($phone) . "'
              AND   account_id = '" . $account->getId() . "'
              AND   time >= FROM_UNIXTIME('$from')
              AND   time <= FROM_UNIXTIME('$until')
              ORDER BY time DESC ";
			
			if ($result = $db->query($query)) {
				while($foo = $db->fetchObject($result)) {
					$tempArray[] = $account->initTransaction($foo->id, $foo);
				}
				$db->freeResult($result);
			}
		}
		return $tempArray;
  }

  static function factoryByName($account, $name, $from, $until) {
		$from = (int)$from;
		$until = (int)$until;
		$tempArray = array();

		if (is_object($account) AND $from > 0 AND $until > 0) {
			$db = Database::instantiate(Database::TYPE_READ);
			$query = TransactionFactory::SELECTLIST . "
							FROM  pesapi_payment
              WHERE name = '" . $db->dbIn($name) . "'
              AND   account_id = '" . $account->getId() . "'
              AND   time >= FROM_UNIXTIME('$from')
              AND   time <= FROM_UNIXTIME('$until')
              ORDER BY time DESC ";

			if ($result = $db->query($query)) {
				while($foo = $db->fetchObject($result)) {
					$tempArray[] = $account->initTransaction($foo->id, $foo);
				}
				$db->freeResult($result);
			}
		}
		return $tempArray;
  }

  static function factoryByAccount($account, $accountString, $from, $until) {
		$from = (int)$from;
		$until = (int)$until;
		$tempArray = array();

		if (is_object($account) AND $from > 0 AND $until > 0 AND $accountString != "") {
			$db = Database::instantiate(Database::TYPE_READ);
			$query = TransactionFactory::SELECTLIST . "
							FROM  pesapi_payment
              WHERE account = '" . $db->dbIn($accountString) . "'
              AND   account_id = '" . $account->getId() . "'
              AND   time >= FROM_UNIXTIME('$from')
              AND   time <= FROM_UNIXTIME('$until')
              ORDER BY time DESC ";

			if ($result = $db->query($query)) {
				while($foo = $db->fetchObject($result)) {
					$tempArray[] = $account->initTransaction($foo->id, $foo);
				}
				$db->freeResult($result);
			}
		}
		return $tempArray;
  }

  static function factoryByTimeInterval($account, $from, $until, $type) {
		$from = (int)$from;
		$until = (int)$until;
		$type = (int)$type;
		$tempArray = array();

		if (is_object($account) AND $from > 0 AND $until > 0) {
			$db = Database::instantiate(Database::TYPE_READ);
			$query = TransactionFactory::SELECTLIST . "
							FROM  pesapi_payment
              WHERE time >= FROM_UNIXTIME('$from')
              AND   account_id = '" . $account->getId() . "'
              AND   time <= FROM_UNIXTIME('$until') ";
			if ($type > 0) {
				$query .= " AND type = '$type' ";
			}
			$query .= " ORDER BY time DESC ";

			if ($result = $db->query($query)) {
				while($foo = $db->fetchObject($result)) {
					$tempArray[] = $account->initTransaction($foo->id, $foo);
				}
				$db->freeResult($result);
			}
		}
		return $tempArray;
  }

}
?>