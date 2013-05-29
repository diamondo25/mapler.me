<?php

class TreeNode extends ArrayObject {
	public $name;
	private $parent;

	
	public function __construct($name, $parent = null) {
		parent::__construct(array());
		$this->name = $name;
		$this->parent = $parent;
	}
	
	public function offsetGet($index) {
		if ($index == '..') {
			return $this->parent;
		}
		
		if (!parent::offsetExists($index)) return null;
		
		$result = parent::offsetGet($index);
		if (is_string($result) && strpos($result, '{UOL}') !== false) {
			$splits = explode('/', substr($result, 5));
			if (count($splits) == 1) return $result;
			$curnode = $this;
			foreach ($splits as $o) {
				if (!isset($curnode[$o])) {
					//echo ' > NOT SET: '.$o."\r\n";
					return null;
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
			$ret = $this->parent !== null;
		}
		else {
			$ret = parent::offsetExists($index);
		}
		return $ret;
	}
	
	public function ToAbsoluteURI() {
		$ret = '';
		$curnode = $this;
		while (true) {
			$curnode = $curnode->parent;
			if ($curnode === null) break;
			$ret = '/'.$curnode->name.$ret;
		}
		return $ret;
	}
}

?>