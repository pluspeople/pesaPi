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

class Account {
  //############### Properties ####################
	const MPESA_PAYBILL = 1;
	const MPESA_PRIVATE = 2;
	const KENYA_YU_PRIVATE = 3; // Yu have folded - this will be removed.
	const GHANA_AIRTEL_PRIVATE = 4;
	const RWANDA_MTN_PRIVATE = 5;
	const TANZANIA_MPESA_PRIVATE = 6;
	const TANZANIA_TIGO_PRIVATE = 7;
	const KENYA_AIRTEL_PRIVATE = 8;
	const KENYA_AIRTEL_PAYBILL = 9;
	const SOMALIA_GOLIS_PRIVATE = 10;
	const SOMALIA_TELESOME_PRIVATE = 11;
	const SOMALIA_HORMUUD_PRIVATE = 12;
	const GHANA_MTN_PRIVATE = 13;
	const DR_CONGO_MPESA_PRIVATE = 14;
	const UGANDA_MTN_PRIVATE = 15;
	
  protected $id = 0;
  protected $type = 0;
  protected $name = "";
  protected $identifier = "";
	protected $pushIn = false;
	protected $pushOut = false;
	protected $pushNeutral = false;
	protected $settings = array();

  protected $idUpdated = false;
  protected $typeUpdated = false;
  protected $nameUpdated = false;
  protected $identifierUpdated = false;
	protected $pushInUpdated = false;
	protected $pushOutUpdated = false;
	protected $pushNeutralUpdated = false;
	protected $settingsUpdated = false;

  protected $isDataRetrived = false;

	protected $config = null;

  //# # # # # # # # Initializer # # # # # # # # # #
  public function __construct($id, $initValues=NULL) {
    $this->id = (int)$id;
		$this->config = \PLUSPEOPLE\PesaPi\Configuration::instantiate();
    #initValues is an object with values for fast restoring state (optimisation)
    if (isset($initValues)) {
      $this->assignValues($initValues);
    }
  }
  //# # # # # # # # get/set methods # # # # # # # #
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

  public function getName() {
    $this->retriveData();
    return $this->name;
  }
  public function setName($input) {
    $this->name = $input;
    return $this->nameUpdated = true;
  }

  public function getIdentifier() {
    $this->retriveData();
    return $this->identifier;
  }
  public function setIdentifier($input) {
    $this->identifier = $input;
    return $this->identifierUpdated = true;
  }

  public function getPushIn() {
    $this->retriveData();
    return $this->pushIn;
  }
  public function setPushIn($input) {
    $this->pushIn = (bool)$input;
    return $this->pushInUpdated = true;
  }

  public function getPushOut() {
    $this->retriveData();
    return $this->pushOut;
  }
  public function setPushOut($input) {
    $this->pushOut = (bool)$input;
    return $this->pushOutUpdated = true;
  }

  public function getPushNeutral() {
    $this->retriveData();
    return $this->pushNeutral;
  }
  public function setPushNeutral($input) {
    $this->pushNeutral = (bool)$input;
    return $this->pushNeutralUpdated = true;
  }

  public function getSettings() {
    $this->retriveData();
    return $this->settings;
  }
  public function setSettings($input) {
    $this->settings = $input;
    return $this->settingsUpdated = true;
  }

  //# # # # # # # # misc methods # # # # # # # #
	static public function createNew($type, $identifier) {
		$type = (int)$type;
		
		if ($type > 0 AND $identifier != "") {
      $db = Database::instantiate(Database::TYPE_WRITE);

			$newSettings = array("PUSH_IN_URL" => "",
													 "PUSH_IN_SECRET" => "",
													 "PUSH_OUT_URL" => "",
													 "PUSH_OUT_SECRET" => "",
													 "PUSH_NEUTRAL_URL" => "",
													 "PUSH_NEUTRAL_SECRET" => "",
													 "SYNC_SECRET" => Utility::generatePassword(8));
			$settings = serialize($newSettings);

			$query = "INSERT INTO   pesapi_account(
                              type,
                              name,
                              identifier,
                              push_in,
                              push_out,
                              push_neutral,
                              settings)
                VALUES(
                              '$type',
                              '',
                              '" . $db->dbIn($identifier) . "',
                              0,
                              0,
                              0,
                              '" . $db->dbIn($settings) . "')";

			if ($db->query($query)) {
				return AccountFactory::createEntry($type, $db->insertId());
			}
		}
		return null;
	}

  public function delete() {
    if ($this->getId() > 0) {
			$db = Database::instantiate(Database::TYPE_WRITE);

      $query="DELETE	FROM pesapi_account
	       WHERE	id='" . $this->getId() . "'";
      
      return ($db->query($query));
    } else {
      return false;
    }
  }

  public function update() {
    if ($this->getId() > 0) {
			$db = Database::instantiate(Database::TYPE_WRITE);

      $query = "UPDATE	 pesapi_account
	        SET	 id=id ";

      $query .= $this->generateUpdateQuery();
      $query .= " WHERE	id='" . $this->getId() . "'";

      return $db->query($query);
    } else {
      return false;
    }
  }

	protected function handleCallback($transaction) {
		switch ($transaction->getSuperType()) {
		case Transaction::MONEY_IN:
			if ($this->getPushIn()) {
				$settings = $this->getSettings();
				$url = trim($settings["PUSH_IN_URL"]);
				$secret = $settings["PUSH_IN_SECRET"];
				if ($url != "") {
					return $this->performCallback($transaction, $url, $secret);
				}
			}
			break;
		case Transaction::MONEY_OUT:
			if ($this->getPushOut()) {
				$settings = $this->getSettings();
				$url = trim($settings["PUSH_OUT_URL"]);
				$secret = $settings["PUSH_OUT_SECRET"];
				if ($url != "") {
					return $this->performCallback($transaction, $url, $secret);
				}
			}
			break;
		case Transaction::MONEY_NEUTRAL:
			if ($this->getPushNeutral()) {
				$settings = $this->getSettings();
				$url = trim($settings["PUSH_NEUTRAL_URL"]);
				$secret = $settings["PUSH_NEUTRAL_SECRET"];
				if ($url != "") {
					return $this->performCallback($transaction, $url, $secret);
				}
			}
			break;
		}
		return false;
	}

	protected function performCallback($transaction, $url, $secret) {
		// TODO: Needs to be able to automatically re-sent in case "OK" is not received
		if ($url != "") {
			$postData = 
				'type=' . $transaction->getType() .
				'&receipt=' . $transaction->getReceipt() .
				'&time=' . $transaction->getTime() .
				'&phonenumber=' . urlencode($transaction->getPhonenumber()) . 
				'&name=' . urlencode($transaction->getName()) . 
				'&account=' . urlencode($transaction->getAccount()) . 
				'&amount=' . $transaction->getAmount() . 
				'&postbalance=' . $transaction->getPostBalance() .
				'&transactioncost=' . $transaction->getTransactionCost() .
				'&note=' . urlencode($transaction->getNote()) .
        '&secret=' . urlencode($secret);
			
			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $postData); 
			
			$status = curl_exec($curl);
			// check if creation went ok.
			if ($status == "OK") { // expect an OK returned from the recepiant
				// feeback is ok.
				return true;
			}
		}
		return false;
	}

	public function forceSyncronisation() {
	}

  # # # # # # # # private methods # # # # # # # #
  protected function retriveData() {
    if (!$this->isDataRetrived) {
			$db = Database::instantiate(Database::TYPE_READ);	
		
      $query="SELECT  type, 
                     name, 
                     identifier,
                     push_in,
                     push_out,
                     push_neutral,
                     settings
               FROM  pesapi_account 
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
      $this->name = $db->dbOut($foo->name);
      $this->identifier = $db->dbOut($foo->identifier);
      $this->pushIn = $db->dbOut($foo->push_in);
      $this->pushOut = $db->dbOut($foo->push_out);
      $this->pushNeutral = $db->dbOut($foo->push_neutral);
      $this->settings = unserialize($db->dbOut($foo->settings));
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

    if ($this->nameUpdated) {
      $query.=" ,name='" . $db->dbIn($this->name) . "' ";
      $this->nameUpdated=false;
    }

    if ($this->identifierUpdated) {
      $query.=" ,identifier='" . $db->dbIn($this->identifier) . "' ";
      $this->identifierUpdated=false;
    }

    if ($this->pushInUpdated) {
			$query.=" ,push_in=" . ($this->pushIn ? 1 : 0);
      $this->pushInUpdated=false;
    }

    if ($this->pushOutUpdated) {
			$query.=" ,push_out=" . ($this->pushOut ? 1 : 0);
      $this->pushOutUpdated=false;
    }

    if ($this->pushNeutralUpdated) {
			$query.=" ,push_neutral=" . ($this->pushNeutral ? 1 : 0);
      $this->pushNeutralUpdated=false;
    }

    if ($this->settingsUpdated) {
			$query.=" ,settings='" . $db->dbIn(serialize($this->settings)) . "' ";
      $this->settingsUpdated=false;
    }

    return $query;
  }
}
?>