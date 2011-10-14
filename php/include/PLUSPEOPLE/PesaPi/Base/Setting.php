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

class Setting {
  ############### Properties ####################
  const STRING = 1;
	const DATETIME = 2;
	const INT = 3;

  protected $id = 0;
  protected $type = 0;
  protected $name = "";
  protected $valueString = "";
  protected $valueDate = 0;
  protected $valueInt = 0;

  protected $idUpdated = false;
  protected $typeUpdated = false;
  protected $nameUpdated = false;
  protected $valueStringUpdated = false;
  protected $valueDateUpdated = false;
  protected $valueIntUpdated = false;

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

  public function getValueString() {
    $this->retriveData();
    return $this->valueString;
  }
  public function setValueString($input) {
    $this->valueString = $input;
    return $this->valueStringUpdated = true;
  }

  public function getValueDate() {
    $this->retriveData();
    return $this->valueDate;
  }
  public function setValueDate($input) {
    $this->valueDate = (int)$input;
    return $this->valueDateUpdated = true;
  }

  public function getValueInt() {
    $this->retriveData();
    return $this->valueInt;
  }
  public function setValueInt($input) {
    $this->valueInt = (int)$input;
    return $this->valueIntUpdated = true;
  }

	public function getValue() {
		switch ($this->getType()) {
		case Setting::STRING:
			return $this->getValueString();
			break;
		case Setting::DATETIME:
			return $this->getValueDate();
			break;
		case Setting::INT:
			return $this->getValueInt();
			break;
		}
	}

  # # # # # # # # misc methods # # # # # # # #
  public function delete() {
    if ($this->getId() > 0) {
			$db = Database::instantiate(Database::TYPE_WRITE);

      $query="DELETE	FROM pesapi_setting
	       WHERE	id='" . $this->getId() . "'";
      
      return ($db->query($query));
    } else {
      return false;
    }
  }

  public function update() {
    if ($this->getId() > 0) {
			$db = Database::instantiate(Database::TYPE_WRITE);

      $query = "UPDATE	 pesapi_setting
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
                     name, 
                     value_string, 
                     UNIX_TIMESTAMP(value_date) AS value_date, 
                     value_int 
               FROM  pesapi_setting 
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
      $this->valueString = $db->dbOut($foo->value_string);
      $this->valueDate = $db->dbOut($foo->value_date);
      $this->valueInt = $db->dbOut($foo->value_int);

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

    if ($this->valueStringUpdated) {
      $query.=" ,value_string='" . $db->dbIn($this->valueString) . "' ";
      $this->valueStringUpdated=false;
    }

    if ($this->valueDateUpdated) {
      $query.=" ,value_date=FROM_UNIXTIME('$this->valueDate') ";
      $this->valueDateUpdated=false;
    }

    if ($this->valueIntUpdated) {
      $query.=" ,value_int='" . (string)$this->valueInt . "' ";
      $this->valueIntUpdated=false;
    }

    return $query;
  }
}
?>