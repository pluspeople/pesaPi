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
namespace PLUSPEOPLE\Pesapi\simulator;

class SlowTemplate {
	############### Properties ####################
	public $root;								//root template path
	protected $templatefile;		//filename of template file
	public $template;						//unparsed template full length - REFACTOR: public do to newsletter templates

	protected $templates = array();	//the template file split into blocks
	protected $assigned = array();	//key, value assigned 
	protected $map = array();       //nested map

  public $isLoadet = false; //REFACTOR: public do to newsletter templates
  protected $isInitialized = false;

	protected $stripSpace = false;

	############### Methods #######################
	# # # # # # # # Initializer # # # # # # # # # #
	function __construct($root) {
    $this->setRoot($root);
	}

	# # # # # # # # get/set methods # # # # # # # #
	public function setRoot($path) {
		$path=preg_replace("/\/$/",'',$path);
    $this->root = $_SERVER['DOCUMENT_ROOT'] . "/$path" . '/';
		return true;
	}

  public function getRoot() {
    return $this->root;
  }

	public function setTemplateFile($file) {
		$this->templatefile = $file;
		$this->loadTemplate();
	}

	public function getAssigned($block = 'main') {
		if (isset($this->assigned['{' . $block . '}'])) {
			return $this->assigned['{' . $block . '}'];
		} else {
			return '';
		}
	}

	# # # # # # # # misc methods # # # # # # # # # #
	public function parse($block = 'main', $clearNested = true) {
	  $this->initTemplate();
	  $temp = strtr($this->templates[$block], $this->assigned);  // actual keyword substitution

	  //remove any non parsed tags.
	  if ($this->stripSpace) {
	    $temp = preg_replace("/\{([A-Za-z0-9_]+)\}/", '', $temp);
			if (!isset($this->assigned['{' . $block . '}'])) {
		    $this->assigned['{' . $block . '}'] = preg_replace("/[\t ]{2,}/",' ',$temp);
			} else {
		    $this->assigned['{' . $block . '}'] .= preg_replace("/[\t ]{2,}/",' ',$temp);
			}
	  } else {
			if (!isset($this->assigned['{' . $block . '}'])) {
				$this->assigned['{' . $block . '}'] = preg_replace("/\{[A-Za-z0-9_]+\}/", '', $temp);
			} else  {
		    $this->assigned['{' . $block . '}'] .= preg_replace("/\{[A-Za-z0-9_]+\}/", '', $temp);
			}
	  }
	  unset($temp);

	  // clear nested
	  if ($clearNested AND isset($this->map[$block])) {
			array_map(array($this, 'unsetOne'), $this->map[$block]); 
	  }
	}

	public function assign($assignarray) {
	  while ( list ($key,$val) = each ($assignarray) ) {
	    if (!(empty($key))) {
        //    Empty values are allowed Empty Keys are NOT
	      $this->assigned['{' . $key . '}'] = (string)$val; //important that we cast it to a string
	    }
	  }

	}

	public function assignOne($key, $value) {
	  $key = trim($key);
	  if ($key != '') {
	    $this->assigned['{' . $key . '}'] = (string)$value; //important that we cast it to a string
	  }
	}

	public function unsetOne($key) {
	  $this->assignOne($key, '');
	}

	public function unsetAll() {
		$this->assigned = array();
	}


 	public function slowPrint($block = 'main') {
		print $this->assigned['{' . $block . '}'];
		return true;
	}

	# # # # # # # # private methods # # # # # # # #
	protected function loadTemplate() {
	  if (!$this->isLoadet) {
	    $filename = $this->root . $this->templatefile;
	    $this->template = file_get_contents($filename);
	    $this->isLoadet = true;
	  }
	}

	protected function initTemplate() {
	  if (!$this->isInitialized) {
	    $temp = preg_split("/<!-- (BEGIN|END) DYNAMIC BLOCK: ([A-Za-z0-9_]+) -->/m", $this->template, -1, PREG_SPLIT_DELIM_CAPTURE);
	    
	    $stack = array('main');
	    $dims = array('main' => array());
	    $this->templates['main'] = $temp[0];
	    if (count($temp) > 1) {
				$amount = count($temp) - 3;
	      for ($i = 0; $i < $amount; $i += 3) {
					if ($temp[$i + 1] == 'BEGIN') {
						$stackSize = count($stack);
					  $this->templates[$stack[$stackSize - 1]] .= '{' . $temp[$i + 2] . '}';	

		  			$this->map[$temp[$i + 2]] = array(); //build map
		  			$this->map[$stack[$stackSize - 1]][] = $temp[$i + 2];
		  			$stack[] = $temp[$i + 2]; //build stack - push element on stack
					} elseif ($temp[$i + 1] == 'END') {
		  			array_pop($stack);
					}

					if (!isset($this->templates[$stack[count($stack) - 1]])) {
						$this->templates[$stack[count($stack) - 1]] = $temp[$i + 3];	
					} else {
						$this->templates[$stack[count($stack) - 1]] .= $temp[$i + 3];	
					}
	      }
	    }
	    $this->isInitialized = true;
	    unset($temp);
	  }
	}

}

?>
