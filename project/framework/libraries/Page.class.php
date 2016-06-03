<?php
/**
 *================================================================== 
 * page.class.php 分页类，实现数据分页效果
 * @author 秦佳奇
 * @version 1.0
 * 2016年6月3日
 *==================================================================
 */

class Page {

	private $_total;	// 总记录数
	private $_pageNum;	// 总页数
	private $_pageSize;	// 每页的记录数
	private $_current;	// 当前页
	private $_url;		// URL
	private $_first;	// 首页
	private $_last;		// 末页
	private $_prev;		// 上一页
	private $_next;		// 下一页

	/**
	 * 构造函数
	 * @param $total number 总的记录数
	 * @param $pageSize number 每页的记录数
	 * @param $current number 当前所在页
	 * @param $url string 当前请求的脚本名称,默认为空 ex:index.php?a=xx&b=xx
	 */

	public function __construct($total, $pageSize, $current , $url){
		$this->_total = $total;
		$this->_pageSize = $pageSize;
		$this->_pageNum = $this->_getNum();
		$this->_current = $current;

		//设置URL
		$this->_url = $url . '&page=';

		$this->_first = $this->_getFirst();
		$this->_last = $this->_getLast();
		$this->_prev = $this->_getPrev();
		$this->_next = $this->_getNext();

	}

	//获得总页数
	private function _getNum(){
		return ceil($this->_total / $this->_pageSize);
	}

	private function _getFirst(){
		if($this->_current == 1){
			return '[首页]';
		}else{
			return "<a href='{$this->_url}1'>[首页]</a>";
		}
	}

	private function _getLast(){
		if($this->_current == $this->_pageNum){
			return '[末页]';
		}else{
			return "<a href='{$this->_url}{$this->_pageNum}'>[末页]</a>";
		}
	}

	private function _getPrev(){
		if($this->_current == 1){
			return '[上一页]';
		}else{
			return "<a href='{$this->_url}" . ($this->_current-1) . "'>[上一页]</a>";
		}
	}

	private function _getNext(){
		if($this->_current == $this->_pageNum){
			return '[下一页]';
		}else{
			return "<a href='{$this->_url}" . ($this->_current+1) . "'>[下一页]</a>";
		}
	}

	/**
	 * getPage方法，得到分页信息
	 * @return string 分页信息字符串,用于显示分页情况，上下页连接
	 */
	public function showPage(){
		if ($this->_pageNum > 1){
			return "共有 {$this->_total} 条记录,每页显示 {$this->_pageSize} 条记录， 当前为 {$this->_current}/{$this->_pageNum} {$this->_first} {$this->_prev} {$this->_next} {$this->_last}";
		}else{
			return "共有 {$this->_total} 条记录";
		}
	}


}











