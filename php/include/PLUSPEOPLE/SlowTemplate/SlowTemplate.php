<?php
namespace Pluspeople\SlowTemplate;
class SlowTemplate {
	//############### Properties ####################
	public $root;								//root template path
	protected $templatefile;		//filename of template file
	public $template;						//unparsed template full length - REFACTOR: public do to newsletter templates

	protected $templates = array();	//the template file split into blocks
	protected $assigned = array();	//key, value assigned 
	protected $map = array();       //nested ma

  public $isLoadet = false; //REFACTOR: public do to newsletter templates
  protected $isInitialized = false;

	protected $stripSpace = true;
	protected $useEtag = true; // Enables automatic ETAG headers but costs a little performance
	protected $cacheTpl = false; // Enables tpl caching - mostly relevant for high traffic sites

	//############### Methods #######################
	//# # # # # # # # Initializer # # # # # # # # # #
	function __construct($root, $cacheTpl=false) {
    $this->setRoot($root);
		$this->cacheTpl = (bool)$cacheTpl;
	}

	//# # # # # # # # get/set methods # # # # # # # #
	public function setRoot($path) {
		$path=preg_replace("/\/$/",'',$path);
    $this->root = $_SERVER['DOCUMENT_ROOT'] . "/$path" . '/';
		return true;
	}

  public function getRoot() {
    return $this->root;
  }

	public function setStripSpace($input) {
		$this->stripSpace = (bool)$input;
	}

	public function setTemplate($data) {
		$this->template = $data;
		$this->isLoadet = true;
		return true;
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
		if (isset($this->templates[$block])) {
			$temp = strtr($this->templates[$block], $this->assigned);  // actual keyword substitution
		} else {
			$temp = "";
		}

	  //Remove any non parsed tags.
		$temp = preg_replace("/\{([A-Za-z0-9_]+)\}/", '', $temp);
		if (!isset($this->assigned['{' . $block . '}'])) {
			$this->assigned['{' . $block . '}'] = preg_replace("/\{[A-Za-z0-9_]+\}/", '', $temp);
		} else  {
			$this->assigned['{' . $block . '}'] .= preg_replace("/\{[A-Za-z0-9_]+\}/", '', $temp);
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

 	public function slowPrint($block = 'main', $etag = true) {
		$fullBlock = '{' . $block . '}';
		if (isset($this->assigned[$fullBlock])) {
			if ($this->useEtag AND $etag) {
				$etagmd5 = '"' . md5($this->assigned[$fullBlock]) . '"';
				if (isset($_SERVER['HTTP_IF_NONE_MATCH']) AND $_SERVER['HTTP_IF_NONE_MATCH'] == $etagmd5) {
					header("HTTP/1.1 304 Not Modified");
					exit();
				}
				header("ETag: $etagmd5");
			}
			print $this->assigned[$fullBlock];
			return true;
		}
		return false;
	}

	# # # # # # # # private methods # # # # # # # #
	protected function loadTemplate() {
	  if (!$this->isLoadet) {
	    $filename = $this->root . $this->templatefile;

			if ($this->cacheTpl) {
				$cacheFilename = $filename . "_cache";
				if (file_exists($filename) AND file_exists($cacheFilename)) {
					$temp = stat($filename);
					$tplTime = $temp[9];
					$temp = stat($cacheFilename);
					$cacheTime = $temp[9];

					if ($cacheTime > $tplTime) {
						$temp = unserialize(file_get_contents($cacheFilename));
						$this->templates = $temp[0];
						$this->map = $temp[1];
						unset($temp);

						$this->isLoadet = true;
						$this->isInitialized = true;
						return true;
					}
				}
		  }
			
	    $this->template = file_get_contents($filename);
	    $this->isLoadet = true;
			return true;
	  }
	}

	protected function initTemplate() {
	  if (!$this->isInitialized) {
			if ($this->stripSpace) {
		    $this->template = preg_replace("/[\t ]{2,}/", ' ', $this->template);
			}
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

			if ($this->cacheTpl) {
				file_put_contents($this->root . $this->templatefile . '_cache', serialize(array($this->templates, $this->map)));
			}
	  }
	}

}

?>
