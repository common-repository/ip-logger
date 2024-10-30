<?php

class ipdetails {
	private $ip = "";
	private $data = array();
	
	function __construct($ip) {	
		$this->ip = $ip;
	}
	
	public function scan() {
		
		$fh = fopen(sprintf("http://geoip.pidgets.com/?ip=%s&format=xml", $this->ip), "r");
		$buffer = "";
		while (!feof($fh))
    		$buffer .= fgets($fh, 4096);
		fclose ($fh);
		
		$xml_parser = xml_parser_create();
		$vals = array();
		$index = array();
		xml_parse_into_struct($xml_parser, $buffer, $vals, $index);
		xml_parser_free($xml_parser);
		
		foreach ($vals as $val)
			$this->data[$val["tag"]] = $val["value"];
	}
	
	public function get_countrycode() {	
		return $this->data["CONTINENT_CODE"];
	}
	
	public function get_country() {
		return $this->data["COUNTRY_CODE"];
	}
	
	public function get_region() {
		return $this->data["REGION"];
	}
	
	public function get_city() {
		return $this->data["CITY"];		
	}
	
	public function get_latitude() {
		return $this->data["LATITUDE"];		
	}
	
	public function get_longitude() {	
		return $this->data["LONGITUDE"];	
	}
	
	public function get_postalcode() {	
		return $this->data["POSTAL_CODE"];	
	}
	
	public function get_dmacode() {		
		return $this->data["DMA_CODE"];
	}
	
	public function get_areacode() {	
		return $this->data["AREA_CODE"];	
	}
	
	public function get_code3() {	
		return $this->data["COUNTRY_CODE3"];	
	}
	
	public function close() {	
		$this->data = null;	
	}
}

?>
