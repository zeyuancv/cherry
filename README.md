# cherry
composer

	第一步
	composer require zeyuan/cherry
	composer require thingengineer/mysqli-database-class:dev-master
	composer require nikic/fast-route
	
	// 第二步
    "autoload": {
        "psr-4": {
            "Zeyuan\\Cherry\\": "src/",
			"App\\":"app/"
        }
    }
	
	// 第三步
	解压附件
