<?php

function GetObjectLength($input) {
	if (is_array($input)) return count($input);
	return strlen($input);
}


class PNGReader {
	private $handle, $statinfo;
	private $chunks;

	public function __construct($filename) {
		$this->handle = fopen($filename, 'rb');
		$this->statinfo = fstat($this->handle);
		$this->chunks = array();
	}
	
	public function IsPNG() {
		if ($this->statinfo['size'] < 8 + 4 + 4) { // Header + IHDR + IEND
			return false;
		}

		fseek($this->handle, 0);
		$header = fread($this->handle, 8);
		$correct_header = array(0x89, 0x50, 0x4E, 0x47, 0x0D, 0x0A, 0x1A, 0x0A);
		for ($i = 0; $i < 8; $i++) {
			if (ord($header[$i]) != $correct_header[$i]) {
				echo ord($header[$i]).' - '.$correct_header[$i]."\r\n";
				return false;
			}
		}
		
		return true;
	}
	
	public function LoadChunks() {
		global $__crc;
		fseek($this->handle, 8); // Skip header
		while (true) {
			if (ftell($this->handle) >= $this->statinfo['size']) break;
			
			$len = $this->ReadInt();
			$name = fread($this->handle, 4);
			echo '>>> '.$len.' > '.$name."<br />";
			if ($len == 0)
				$data = null;
			else
				$data = fread($this->handle, $len);
			$crc = $this->ReadInt();
			
			$this->chunks[$name] = array($len, $crc, $data, crc32($name.$data));
			$fullblock = null;
		}
		
		var_dump($this->chunks);
		
	}
	
	public function ReadInt() {
		$derp = 0;
		$tmp = fread($this->handle, 4);
		$this->PrintBytes($tmp);
		$derp += (ord($tmp[3]) << 0);
		$derp += (ord($tmp[2]) << 8);
		$derp += (ord($tmp[1]) << 16);
		$derp += (ord($tmp[0]) << 24);
		
		return $derp;
	}
	
	public function PrintBytes($input) {
		$buff = '('.GetObjectLength($input).') ';
		for ($i = 0; $i < GetObjectLength($input); $i++) {
			$val = ord($input[$i]);
			$hex = dechex($val);
			if ($val < 0xF)
				$hex = '0'.$hex;
			$buff .= $hex.' ';
		}
		
		echo strtoupper(trim($buff)).'<br />';
	}
	
	public function GetChunk($name) {
		if (!isset($this->chunks[$name])) return null;
		return $this->chunks[$name];
	}
	
	public function Close() {
		fclose($this->handle);
		unset($this->chunks);
	}
}


class PNGWriter {
	private $handle, $statinfo;
	private $chunks;

	public function __construct($filename) {
		$this->handle = fopen($filename, 'wb');
		$this->chunks = array();
	}

	public function WriteHeader($width, $height) {
		$png_header = array(0x89, 0x50, 0x4E, 0x47, 0x0D, 0x0A, 0x1A, 0x0A);
		foreach ($png_header as $character) {
			fwrite($this->handle, chr($character), 1);
		}

		$temp = '';
		$temp .= pack('N', $width);
		$temp .= pack('N', $height);
		$temp .= pack('c', 0x08);
		$temp .= pack('c', 0x06);
		$temp .= pack('c', 0x00);
		$temp .= pack('c', 0x00);
		$temp .= pack('c', 0x00);
		
		$this->WriteChunk('IHDR', $temp);
		
	}
	
	public function WriteChunk($name, $data) {
		$temp = '';
		$temp .= pack('N', strlen($data));
		$temp .= $name;
		$temp .= $data;
		$temp .= pack('N', crc32($name.$data));
		
		var_dump($temp);
		echo "<br /><br />";

		fwrite($this->handle, $temp);
	}
	
	public function PrintBytes($input) {
		$buff = '('.GetObjectLength($input).') ';
		for ($i = 0; $i < GetObjectLength($input); $i++) {
			$val = ord($input[$i]);
			$hex = dechex($val);
			if ($val < 0xF)
				$hex = '0'.$hex;
			$buff .= $hex.' ';
		}
		
		echo strtoupper(trim($buff)).'<br />';
	}
	
	public function Close() {
		fclose($this->handle);
	}

}

class APNGWriter extends PNGWriter {
	public function WriteAnimationControl($frames, $loops = 0) {
		$temp = '';
		$temp .= pack('NN', $frames, $loops);
		
		$this->WriteChunk('acTL', $temp);
	}

	public function WriteFrameControl($frameID, $width, $height, $offsetx, $offsety, $delay) {
		$temp = '';
		$temp .= pack('N', $frameID);

		$temp .= pack('NN', $width, $height);
		$temp .= pack('NN', $offsetx, $offsety);
		
		$temp .= pack('nn', 200, 1000);
		
		$temp .= pack('cc', 0x00, 0x00);
		
		
		$this->WriteChunk('fcTL', $temp);
	}

	public function WriteFrameData($frameID, $idata_data) {
		$temp = '';
		$temp .= pack('N', $frameID);
		$temp .= $idata_data;
		
		$this->WriteChunk('fdAT', $temp);
	}

}


?>