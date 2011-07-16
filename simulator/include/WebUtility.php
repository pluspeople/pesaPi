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

class WebUtility {
	public static function checkSubmit($internal_var) {
	        $postvars = $_POST;
	        $getvars = $_GET;

	        return (
	            isset($GLOBALS[$internal_var]) ||
	            isset($GLOBALS[$internal_var."_x"]) ||
	            isset($GLOBALS[$internal_var."_y"]) ||
	            isset($postvars[$internal_var]) ||
	            isset($postvars[$internal_var."_x"]) ||
	            isset($postvars[$internal_var."_y"]) ||
	            isset($getvars[$internal_var]) ||
	            isset($getvars[$internal_var."_x"]) ||
	            isset($getvars[$internal_var."_y"]));
	}

	public static function redirect($destination) {
		$destination = utf8_encode($destination);
		Header("Location: $destination");
	  exit();
	}

	public static function closePopup($reload = false) {
	  if ($reload) {
  		print '<html><head><script language="Javascript">window.opener.location=window.opener.location;window.close();</script></head></html>';
	  } else {
	  	print '<html><head><script language="Javascript">window.close();</script></head></html>';
	  }
	}

	public static function maxLength($input, $length) {
		if (strlen($input) > $length AND $length > 4) {
			$maxlength = $length - 4;
		        return preg_replace("/(.{0,$maxlength})\s(.*)/s","\\1 ...", $input);
		} else {
			return $input;
		}
	}

	public static function safeFilename($input) {
	  $search  = array(" ", "æ", "ø", "å", "Æ", "Ø", "Å");
	  $replace = array("_", "ae", "oe", "aa", "ae", "oe", "aa");
	  return preg_replace("/[^a-zA-Z0-9_\.]/","", str_replace($search, $replace, $input));
	}

	public static function getBrowserLanguage($langarray) {
	  if (count($langarray) > 0) {
	    $temp = split(",", $_SERVER["HTTP_ACCEPT_LANGUAGE"]);
	    $returnLanguage = $langarray[count($langarray)-1];
    
	    foreach ($temp as $item) {
	      $confset = 0;
	      $item = trim($item);
	      for ($i = 0; $i < count($langarray); $i++) {
# rfc 2616 dictates that a acept_language element consists of a primary-tag and any number of subtags 
		      $tags = split("-", $item);
		      if ($tags[0] == $langarray[$i]) {
		        $returnLanguage = $langarray[$i];
		        $confset = 1;
		        break;
		      }
	      }

	      if ($confset == 1) {
		      break;
	      }
	    }

	    return $returnLanguage;
	  } else {
	    return "";
	  }					
	}

	public static function viewstate($length) {
		$length = (int)$length;

		$chars = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    srand((double)microtime()*1000000);

    $i = 0;
    $view = '/' ;

    while ($i < $length-1) {
        $num = rand() % 60;
        $tmp = substr($chars, $num, 1);
        $view .= $tmp;
        $i++;
    }

    return $view;		
	}

}
?>
