<?php
// 引入自动加载类
require('vendor/autoload.php');

// 应用目录为当前目录
define('APP_PATH', __DIR__ . '/');

// 开启调试模式
define('APP_DEBUG', true);

// 实例化框架类
(new Zeyuan\Cherry\mvc\Init())->run();