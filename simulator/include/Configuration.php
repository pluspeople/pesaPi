<?php
namespace PLUSPEOPLE\Pesapi\simulator;

set_include_path("/Users/kaal/work/2011/PesaPi/simulator/include:" . get_include_path());
date_default_timezone_set('Africa/Nairobi');



class Configuration {
	static public $singleton = null;

	protected $configArray=array(
															 // Database settings follow - please note that they are repeated twice
															 "DatabaseHostRead"						=> "localhost",
															 "DatabaseUserRead"						=> "root",
															 "DatabasePasswordRead"				=> "ture",
															 "DatabaseDatabaseRead"				=> "2011_simulator",
															 "DatabaseHostWrite"						=> "localhost",
															 "DatabaseUserWrite"						=> "root",
															 "DatabasePasswordWrite"				=> "ture",
															 "DatabaseDatabaseWrite"				=> "2011_simulator",
															 );

	/////////////////////////////////////////////
	public function getConfig($argument) {
		return $this->configArray[$argument];
	}

	public static function instantiate() {
		if (self::$singleton == null) {
			self::$singleton = new Configuration();
		}
		return self::$singleton;
	}
}

?>