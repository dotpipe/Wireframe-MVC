<?php

spl_autoload_register(function ($className)
{
	$path1 = '/src/lib/';
	$path2 = './';

	if (file_exists($path1.$className.'.php'))
		include $path1.$className.'.php';
	else
		include $path2.$className.'.php';
});

    class Actions {

        public $token;
        public $path;
        public $copy;

        /*
        *
        * public function __construct
        * @parameters string, string, string
        *
        */
        function __construct(string $token, string $view_name, string $action_name) {
            $this->copy = $view_name;
            if (!is_dir("$token/view/$view_name") && !mkdir("$token/view/$view_name"))
                echo "Unable to create needed directories";
            $this->path = "$this->token/view/$view_name";
            $this->token = $token;
            $this->createAction($action_name);
        }
        
        /*
        *
        * public function createAction
        * @parameters string
        *
        */
        public function createAction(string $action_name) {
            $this->actions[$action_name] = new PageViews($this->token, $this->copy);
            echo $action_name;
            $this->actions[$action_name]->addPartial("index.php", $this->copy, $action_name);
            return 0;
        }
    }
