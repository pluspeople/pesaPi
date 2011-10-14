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

class Account {
  //############### Properties ####################
	const MPESA_PAYBILL = 1;
	const MPESA_PRIVATE = 2;

  protected $id = 0;
  protected $type = 0;
  protected $name = "";
  protected $identifier = "";

  protected $idUpdated = false;
  protected $typeUpdated = false;
  protected $nameUpdated = false;
  protected $identifierUpdated = false;

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


  # # # # # # # # misc methods # # # # # # # #
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
		// NOT DONE
						/*
						if ($this->config->getConfig("PaymentReceivedPostback") AND $url != "") {

							$postData = 
								'amount=' . $payment->getAmount() . 
								'&account=' . urlencode($payment->getAccount()) . 
								'&name=' . urlencode($payment->getName()) . 
								'&mobile=' . urlencode($payment->getPhonenumber()) . 
								'&time=' . $payment->getTime();
							$postData .= $this->config->getConfig("PaymentReceivedSecret");

							$curl = curl_init($url);

							curl_setopt($curl, CURLOPT_URL, $url);
							curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
							curl_setopt($curl, CURLOPT_COOKIESESSION, false);
							curl_setopt($curl, CURLOPT_HEADER, false);
							curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
							curl_setopt($curl, CURLOPT_POST, true);
							curl_setopt($curl, CURLOPT_POSTFIELDS, $postData); 

							$status = curl_exec($curl);
							// check if creation went ok.
							if ($status == "OK") { // expect an OK returned from the recepiant
								// feeback is ok.
							}
						}
						*/
	}

	public function forceSyncronisation() {
	}

  # # # # # # # # private methods # # # # # # # #
  protected function retriveData() {
    if (!$this->isDataRetrived) {
			$db = Database::instantiate(Database::TYPE_READ);	
		
      $query="SELECT  type, 
                     name, 
                     identifier 
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

    return $query;
  }
}
?>