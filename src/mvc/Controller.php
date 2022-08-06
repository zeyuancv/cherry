<?php
namespace Zeyuan\Cherry\mvc;

class Controller{
	
	protected $_appName  = '';
	protected $_actionName = '';
	protected $_controllerName = '';
	protected $_view;
	
	function __construct($data){
		$this->_appName = $data['appName'];
		$this->_actionName = $data['actionName'];
		$this->_controllerName = $data['controllerName'];
		$this->_view = new \Zeyuan\Cherry\mvc\View($this->_appName, $this->_controllerName, $this->_actionName);
		
	}
	
	// 分配变量
    public function assign($name, $value)
    {
        $this->_view->assign($name, $value);
    }	
	
    // 渲染视图
    public function render()
    {
        $this->_view->render();
    }	
}