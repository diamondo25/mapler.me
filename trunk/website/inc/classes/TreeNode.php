<?php

require_once __DIR__.'/../functions.datastorage.php';

class TreeNode extends ArrayObject {
	public $name;
	private $parentNode;
	public $isroot;

	
	public function __construct($name, $parent = null) {
		parent::__construct();
		$this->name = $name;
		$this->parentNode = $parent;
		$this->isroot = $parentNode === null ? true : false;
	}
	
	public function offsetGet($index) {
		if ($index == '..') {
			return $this->parentNode;
		}
		
		if (!parent::offsetExists($index)) return null;
		
		$result = parent::offsetGet($index);
		if (is_string($result) && strpos($result, '{UOL}') !== false) {
			$splits = explode('/', substr($result, 5));
			if (count($splits) == 1) return $result;
			$curnode = $this;
			for ($i = 0; $i < count($splits); $i++) {
				$o = $splits[$i];
				if (!isset($curnode[$o])) {
					//echo ' > NOT SET: '.$o."\r\n";
					if ($curnode->isroot && $o == '..' && $i + 1 != count($splits)) {
						$i++;
						// Well.. shit
						$temp = GetItemWZInfo(intval($splits[$i]));
						if ($temp == null) return null;
						$curnode = $temp;
					}
					else {
						return null;
					}
				}
				else {
					//echo 'SET: '.$o."\r\n";
					$curnode = $curnode[$o];
				}
			}
			//if ($curnode !== $this) {
				return $curnode;
			//}
		}
		return $result;
	}
	
	public function offsetSet($index, $value) {
		if ($index == '..') {
			throw new Exception('Unable to set parent object.'); // !!!
		}
		
		if ($value instanceof TreeNode) {
			//$value->parent = $this;
		}
		
		return parent::offsetSet($index, $value);
	}
	
	public function offsetExists($index) {
		$ret = false;
		if ($index == '..') {
			$ret = $this->parentNode !== null;
		}
		else {
			$ret = parent::offsetExists($index);
		}
		return $ret;
	}
	
	public function IsUOL($index) {
		if ($index == '..') return false;
		
		if (!parent::offsetExists($index)) return false;
		
		$result = parent::offsetGet($index);
		return is_string($result) && strpos($result, '{UOL}') !== false;
	}
	
	public function ToAbsoluteURI() {
		$ret = '';
		$curnode = $this;
		while (true) {
			$curnode = $curnode->parentNode;
			if ($curnode === null) break;
			$ret = '/'.$curnode->name.$ret;
		}
		return $ret;
	}
}

?>