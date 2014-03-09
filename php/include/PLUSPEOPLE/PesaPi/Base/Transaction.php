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

class Transaction {
  //############### Properties ####################
	const MONEY_IN = 1;
	const MONEY_OUT = 2;
	const MONEY_NEUTRAL = 3;
	
	const STATUS_COMPLETED = 1;
	const STATUS_DECLINED = 2;
	const STATUS_CANCELLED = 3;
	const STATUS_ATTEMPTED = 4;

  protected $id = 0;
  protected $type = 0;
	protected $superType = 0;
  protected $receipt = "";
  protected $time = "";
  protected $phonenumber = "";
  protected $name = "";
  protected $account = "";
  protected $status = 0;
  protected $amount = 0;
  protected $postBalance = 0;
	protected $note = "";
	protected $transactionCost = 0;

  protected $idUpdated = false;
  protected $typeUpdated = false;
	protected $superTypeUpdated = false;
  protected $receiptUpdated = false;
  protected $timeUpdated = false;
  protected $phonenumberUpdated = false;
  protected $nameUpdated = false;
  protected $accountUpdated = false;
  protected $statusUpdated = false;
  protected $amountUpdated = false;
  protected $postBalanceUpdated = false;
	protected $noteUpdated = false;
	protected $transactionCostUpdated = false;

  protected $isDataRetrived = false;

  # # # # # # # # Initializer # # # # # # # # # #
  public function __construct($id, $initValues=NULL) {
    $this->id = (int)$id;
    #initValues is an object with values for fast restoring state (optimisation)
    if (isset($initValues)) {
      $this->assignValues($initValues);
    }
  }
  # # # # # # # # get/set methods # # # # # # # #
  public function getId() {
    return $this->id;
  }
  public function getType() {
    $this->retriveData();
    return $this->type;
  }
  public function setType($input) {
    $this->type = (int)$input;
    return $this->typeUpdated = true;
  }

  public function getSuperType() {
    $this->retriveData();
    return $this->superType;
  }
  public function setSuperType($input) {
    $this->superType = (int)$input;
    return $this->superTypeUpdated = true;
  }

  public function getReceipt() {
    $this->retriveData();
    return $this->receipt;
  }
  public function setReceipt($input) {
    $this->receipt = $input;
    return $this->receiptUpdated = true;
  }

  public function getTime() {
    $this->retriveData();
    return $this->time;
  }
  public function setTime($input) {
    $this->time = $input;
    return $this->timeUpdated = true;
  }

  public function getPhonenumber() {
    $this->retriveData();
    return $this->phonenumber;
  }
  public function setPhonenumber($input) {
    $this->phonenumber = $input;
    return $this->phonenumberUpdated = true;
  }

  public function getName() {
    $this->retriveData();
    return $this->name;
  }
  public function setName($input) {
    $this->name = $input;
    return $this->nameUpdated = true;
  }

  public function getAccount() {
    $this->retriveData();
    return $this->account;
  }
  public function setAccount($input) {
    $this->account = $input;
    return $this->accountUpdated = true;
  }

  public function getStatus() {
    $this->retriveData();
    return $this->status;
  }
  public function setStatus($input) {
    $this->status = (int)$input;
    return $this->statusUpdated = true;
  }

  public function getAmount() {
    $this->retriveData();
    return $this->amount;
  }
  public function setAmount($input) {
    $this->amount = (int)$input;
    return $this->amountUpdated = true;
  }

  public function getPostBalance() {
    $this->retriveData();
    return $this->postBalance;
  }
  public function setPostBalance($input) {
    $this->postBalance = (int)$input;
    return $this->postBalanceUpdated = true;
  }

  public function getNote() {
    $this->retriveData();
    return $this->note;
  }
  public function setNote($input) {
    $this->note = $input;
    return $this->noteUpdated = true;
  }

  public function getTransactionCost() {
    $this->retriveData();
    return $this->transactionCost;
  }
  public function setTransactionCost($input) {
    $this->transactionCost = (int)$input;
    return $this->transactionCostUpdated = true;
  }

  // # # # # # # # misc methods # # # # # # # #
	public static function createNew($accountId, $superType, $type) {
		$accountId = (int)$accountId;
		$superType = (int)$superType;
		$type = (int)$type;

		if ($accountId > 0 AND $type > 0 AND $superType > 0) {
      $db = Database::instantiate(Database::TYPE_WRITE);

			$query = "INSERT INTO   pesapi_payment(
                              account_id,
                              type,
                              super_type,
                              receipt,
                              time,
                              phonenumber,
                              name,
                              account,
                              status,
                              amount,
                              post_balance,
                              note,
                              transaction_cost)
                VALUES(
                              '$accountId',
                              '$type',
                              '$superType',
                              '',
                              '0000-00-00',
                              '',
                              '',
                              '',
                              0,
                              0,
                              0,
                              '',
                              0)";

			if ($db->query($query)) {
				return new Transaction($db->insertId());
			}
		}
		return null;
	}

  public function delete() {
    if ($this->getId() > 0) {
			$db = Database::instantiate(Database::TYPE_WRITE);

      $query="DELETE	FROM pesapi_payment
	       WHERE	id='" . $this->getId() . "'";
      
      return ($db->query($query));
    } else {
      return false;
    }
  }

  public function update() {
    if ($this->getId() > 0) {
			$db = Database::instantiate(Database::TYPE_WRITE);

      $query = "UPDATE	 pesapi_payment
	        SET	 id=id ";

      $query .= $this->generateUpdateQuery();
      $query .= " WHERE	id='" . $this->getId() . "'";

      return $db->query($query);
    } else {
      return false;
    }
  }

  # # # # # # # # private methods # # # # # # # #
  protected function retriveData() {
    if (!$this->isDataRetrived) {
			$db = Database::instantiate(Database::TYPE_READ);	
		
      $query="SELECT  type, 
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
                     transaction_cost
               FROM  pesapi_payment 
               WHERE id='" . $this->getId() . "';";

      if ($result = $db->query($query) AND $foo = $db->fetchObject($result)) {
				$this->assignValues($foo);
				unset($foo);
        $db->freeResult($result);
      }

    }
  }


  protected function assignValues($foo) {
    if (is_object($foo)) {
			$db = Database::instantiate(Database::TYPE_READ);
      $this->type = $foo->type;
			$this->superType = $foo->super_type;
      $this->receipt = $foo->receipt;
      $this->time = $foo->time;
      $this->phonenumber = $foo->phonenumber;
      $this->name = $db->dbOut($foo->name);
      $this->account = $db->dbOut($foo->account);
      $this->status = $foo->status;
      $this->amount = $foo->amount;
      $this->postBalance = $foo->post_balance;
			$this->note = $foo->note;
			$this->transactionCost = $foo->transaction_cost;

      $this->isDataRetrived = true;
    }
  }

  protected function generateUpdateQuery() {
		$db = Database::instantiate(Database::TYPE_READ);
    $query = "";

    if ($this->typeUpdated) {
      $query.=" ,type='$this->type' ";
      $this->typeUpdated = false;
    }

    if ($this->superTypeUpdated) {
      $query.=" ,super_type='$this->superType' ";
      $this->superTypeUpdated = false;
    }

    if ($this->receiptUpdated) {
      $query.=" ,receipt='$this->receipt' ";
      $this->receiptUpdated = false;
    }

    if ($this->timeUpdated) {
      $query.=" ,time=FROM_UNIXTIME('$this->time') ";
      $this->timeUpdated = false;
    }

    if ($this->phonenumberUpdated) {
      $query.=" ,phonenumber='$this->phonenumber' ";
      $this->phonenumberUpdated = false;
    }

    if ($this->nameUpdated) {
      $query.=" ,name='" . $db->dbIn($this->name) . "' ";
      $this->nameUpdated=false;
    }

    if ($this->accountUpdated) {
      $query.=" ,account='" . $db->dbIn($this->account) . "' ";
      $this->accountUpdated=false;
    }

    if ($this->statusUpdated) {
      $query.=" ,status='" . (string)$this->status . "' ";
      $this->statusUpdated=false;
    }

    if ($this->amountUpdated) {
      $query.=" ,amount='$this->amount' ";
      $this->amountUpdated = false;
    }

    if ($this->postBalanceUpdated) {
      $query.=" ,post_balance='$this->postBalance' ";
      $this->postBalanceUpdated = false;
    }

    if ($this->noteUpdated) {
      $query.=" ,note='" . $db->dbIn($this->note) . "' ";
      $this->noteUpdated=false;
    }

    if ($this->transactionCostUpdated) {
      $query.=" ,transaction_cost='$this->transactionCost' ";
      $this->transactionCostUpdated = false;
    }
    return $query;
  }
}
?>