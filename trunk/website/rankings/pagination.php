<?php

error_reporting(0);

class PS_Pagination {
	var $php_self;
	var $rows_per_page = 5;
	var $total_rows = 0;
	var $links_per_page = 5;
	var $append = "";
	var $sql = "";
	var $debug = false;
	var $conn = false;
	var $page = 1;
	var $max_pages = 0;
	var $offset = 0;
	
	function PS_Pagination($connection, $sql, $rows_per_page = 10, $links_per_page = 5, $append = "") {
		$this->conn = $connection;
		$this->sql = $sql;
		$this->rows_per_page = (int)$rows_per_page;
		if (intval($links_per_page ) > 0) {
			$this->links_per_page = (int)$links_per_page;
		} else {
			$this->links_per_page = 5;
		}
		$this->append = $append;
		$this->php_self = strip_tags($_SERVER);
		if (isset($_GET['page'] )) {
			$this->page = intval($_GET['page'] );
		}
	}

	function paginate() {
		//Find total number of rows
		$all_rs = $this->conn->query($this->sql );
		if (! $all_rs) {
			if ($this->debug)
				echo "SQL query failed. Check your query.<br /><br />Error Returned: " . mysql_error();
			return false;
		}
		$this->total_rows = $all_rs->num_rows;
		@mysql_close($all_rs );
		
		//Return FALSE if no rows found
		if ($this->total_rows == 0) {
			if ($this->debug)
				echo "Query returned zero rows.";
			return FALSE;
		}
		
		//Max number of pages
		$this->max_pages = ceil($this->total_rows / $this->rows_per_page );
		if ($this->links_per_page > $this->max_pages) {
			$this->links_per_page = $this->max_pages;
		}
		
		//Check the page value just in case someone is trying to input an aribitrary value
		if ($this->page > $this->max_pages || $this->page <= 0) {
			$this->page = 1;
		}
		
		//Calculate Offset
		$this->offset = $this->rows_per_page * ($this->page - 1);
		
		//Fetch the required result set
		$rs = $this->conn->query($this->sql . " LIMIT {$this->offset}, {$this->rows_per_page}" );
		if (! $rs) {
			if ($this->debug)
				echo "Pagination query failed. Check your query.<br /><br />Error Returned: " . mysql_error();
			return false;
		}
		return $rs;
	}
	
	function renderFirst($tag = 'First') {
		if ($this->total_rows == 0)
			return FALSE;
		
		if ($this->page == 1) {
			return "$tag ";
		} else {
			return '<a href="' . $this->php_self . '' . $this->append . '">' . $tag . '</a> ';
		}
	}
	
	function renderLast($tag = 'Last') {
		if ($this->total_rows == 0)
			return FALSE;
		
		if ($this->page == $this->max_pages) {
			return $tag;
		} else {
			return ' <a href="' . $this->php_self . '' . $this->max_pages . '' . $this->append . '">' . $tag . '</a>';
		}
	}
	
	function renderNext($tag = '[Next 5]') {
		if ($this->total_rows == 0)
			return FALSE;
		
		if ($this->page < $this->max_pages) {
			return '<a href="' . $this->php_self . '' . ($this->page + 1) . '' . $this->append . '">' . $tag . '</a>';
		} else {
			return $tag;
		}
	}
	
	function renderPrev($tag = '[Previous 5]') {
		if ($this->total_rows == 0)
			return FALSE;
		
		if ($this->page > 1) {
			return ' <a href="' . $this->php_self . '' . ($this->page - 1) . '' . $this->append . '">' . $tag . '</a>';
		} else {
			return " $tag";
		}
	}
	
	function renderNav($prefix = '<span class="page_link">', $suffix = '</span>') {
		if ($this->total_rows == 0)
			return FALSE;
		
		$batch = ceil($this->page / $this->links_per_page );
		$end = $batch * $this->links_per_page;
		if ($end == $this->page) {
			//$end = $end + $this->links_per_page - 1;
			//$end = $end + ceil($this->links_per_page/2);
		}
		if ($end > $this->max_pages) {
			$end = $this->max_pages;
		}
		$start = $end - $this->links_per_page + 1;
		$links = '';
		
		for($i = $start; $i <= $end; $i ++) {
			if ($i == $this->page) {
				$links .= $prefix . ' '.$i.' ' . $suffix;
			} else {
				$links .= ' ' . $prefix . '<a href="' . $this->php_self . '' . $i . '' . $this->append . '">' . $i . '</a>' . $suffix . ' ';
			}
		}
		
		return $links;
	}
	
	function renderFullNav() {
		return $this->renderFirst() . '&nbsp;' . $this->renderPrev() . '&nbsp;' . $this->renderNav() . '&nbsp;' . $this->renderNext() . '&nbsp;' . $this->renderLast();
	}

	function setDebug($debug) {
		$this->debug = $debug;
	}
}
?>
