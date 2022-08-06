<?php
namespace Zeyuan\Cherry\mvc;

/**
 * 视图基类
 */
class View
{
    protected $variables = array();
    protected $_controller;
    protected $_action;
	protected $_app;

    function __construct($app,$controller, $action)
    {
        $this->_controller = strtolower($controller);
        $this->_action = strtolower($action);
		$this->_app = strtolower($app);
    }

    // 分配变量
    public function assign($name, $value)
    {
        $this->variables[$name] = $value;
    }

    // 渲染显示
    public function render()
    {
        extract($this->variables);
		
        $controllerLayout = APP_PATH . 'template/' . D .$this->_app . D . $this->_controller . '/' . $this->_action . '.php';
	
        //判断视图文件是否存在
        if (is_file($controllerLayout)) {
            include ($controllerLayout);
        } else {
            echo '<h1>无法找到视图文件</h1>';
        }
    }
}