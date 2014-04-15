<?php

$router = new Router($_SERVER['REQUEST_URI'], 'config.json');
$router->run();

/**
 * The Configuration class is responsible flor loading any values from a 
 * JSON formatted file into a singleton-accessible object.
 */
class Configuration {

    /**
     * Default Constructor accepts one parameter, a file path for a JSON
     * formatted file. Attempts to load the file if provided and assign
     * properties and arrays to the Configuration instance.
     *
     * @param string    a file path to a configuration JSON object
     */
    protected function __construct($config = NULL) {
        // skip if null
        if($config !== NULL && file_exists($config)) {
            
            $config = file_get_contents($config);
            if($config === FALSE) {
                trigger_error('Unable to read configuration file', E_USER_WARNING );
            }
            
            $config = json_decode($config);
            if($config === NULL) {
                trigger_error('Unable to read configuration file (possible syntax error)', E_USER_WARNING );
            }
            
            // set properties to this object
            foreach($config as $key => $value) {
                $this->$key = $value;
            }
        }
        if(!isset($this->default_language)) {
            $this->default_language = 'en';
        }
    }

    /**
     * Returns the current instance of the Configuration object if the
     * parameter is null, otherwise it will attempt to create a new 
     * Configuration instance for the given parameter.
     * 
     * @param string    a file path to a configuration JSON object
     * @return object   the Configuration instance
     */
    public static function get_instance($config_file = NULL) {
        static $instance = NULL;
        if($instance === NULL || $config_file !== NULL) {
            $instance =  new Configuration($config_file);
        }
        return $instance;
    }
}

/**
 * The Router accepts a url and a string for the configuration file location and 
 * attempts to locate the files to generate the server response. The Router will 
 * attempt to parse meaningful content from the url segments to assign a controller,
 * action, id value and a language if provided. If no URL is provided, the defaults
 * are Language: en, Controller: home, and Action: index.
 */
class Router {
    protected $controller = 'home';
    protected $action = 'index';
    protected $lang = 'en';
    protected $languages = array('en');
    protected $params = array();
    protected $config;

    /**
     * Default constructor for the Router accepts a string URL and a string
     * for a file path to the given configuration filed (if provided)
     * @param string    a url for the given request
     * @param string    a file path for the Configuration instance
     */
    public function __construct($route = '', $config = NULL) {
        // load configuration values, languages
        $this->config = Configuration::get_instance($config);
        if(isset($this->config->default_language)) {
            $this->lang = $this->config->default_language;
        }
        if(isset($this->config->languages)) {
            $this->languages = $this->config->languages;
        }

        // parse meaningful segments from requested url
        $url = strtolower(trim(parse_url($route, PHP_URL_PATH), '/'));
        $segments = explode('/', $url);
        $controller = NULL;
        $action = NULL;

        // loop through segements and extract lang, controller and action if possible
        foreach($segments as $seg) {
            if(empty($seg) || $seg == 'index.php') {
                continue;
            }
            if(!isset($controller)) {
                if(in_array($seg, $this->languages)) {
                    $this->lang = $seg;
                }
                else {
                    $controller = $seg;
                }
            }
            else if(!isset($action)) {
                $action = $seg;
            }
            else {
                $this->params[] = $seg;
            }
        }

        // if and only if we set a controller and action, override the defaults
        if(isset($controller)) {
            $this->controller = str_replace('-', '_', $controller);
        }
        if(isset($action)) {
            $this->action = str_replace('-', '_', $action);
        }
    }

    /**
     * Attempts to load any include files and execute the destination controller
     * and action.
     */
    public function run() {
        // load any include file paths, lol autoloaders
        if(!empty($this->config->includes)) {
            foreach($this->config->includes as $inc) {
                include $inc;
            }
        }

        // assume the requested controller exists for the given file path
        $fp = 'controllers/' . $this->controller . '.php';
        if(!file_exists($fp)) {
            header('HTTP/1.0 404 Not Found');
            exit;
        }
        include $fp;
        
        // create Controller object, assuming that it's in the found controller
        $c = ucfirst($this->controller) . 'Controller';
        $c = new $c($this->lang, $this->controller, $this->action, $this->params);

        // if the action exists as a method, call it
        if(method_exists($c, $this->action)) {
            call_user_func_array(array($c, $this->action), $this->params);
        }
        else if(method_exists($c, 'index')) {
            // Short circuit action for index
            $c->request->action = 'index';
            $c->render['action'] = 'index';
            call_user_func_array(array($c, 'index'), array_merge(array($this->action), $this->params));
        }
        else {
            header('HTTP/1.0 404 Not Found');
            exit;
        }
    }
}

/**
 * Base Controller class that initializes the request and processes server response.
 */
class Controller {
    public $lang;
    public $request;
    public $meta;
    public $config;
    public $model;
    public $scripts = array();
    public $styles = array();
    public $render = array();
    public $layout = 'views/_layout.php';
    protected $current_session;
    protected $current_user;

    /**
     * The Default Constructor requires the route components extracted
     * and initializes the properties for generating the server response.
     */
    public function __construct($lang, $controller, $action, $params) {
        $this->request = new stdClass();
        $this->request->lang = $lang;
        $this->request->controller = $controller;
        $this->request->action = $action;
        $this->request->params = $params;
        $this->set_render_values($action, $controller);
        $this->config = Configuration::get_instance();
        $this->meta = $this->config->meta;
        if(isset($this->config->master_layout)) {
            $this->layout = $this->config->master_layout;
        }
    }

    /**
     * Shortcut for $_GET, attempts to load the value for the given key
     *
     * @param string    the array key in the query string
     * @return string   the value in $_GET for the given key, NULL if the key is not found    
     */
    public function get($key) {
        if(array_key_exists($key, $_GET))
            return $_GET[$key];
        return NULL;
    }

    /**
     * Shortcut for $_POST, attempts to load the value for the given key
     *
     * @param string    the array key in the form data
     * @return string   the value in $_GET for the given key, NULL if the key is not found    
     */
     public function post($key) {
        if(array_key_exists($key, $_POST))
            return $_POST[$key];
        return NULL;
    }

    /**
     * Assuming that the content body is in JSON format, will attempt to return 
     * an initialized JSON object from the request.
     * 
     * @param boolean   the assoc value to pass into json_decode
     * @return  mixed   an initialized object, NULL if the request failed
     */
    public function request_as_json($assoc = FALSE) {
        return json_decode(file_get_contents("php://input"), $assoc);
    }
    
    /**
     * Set the render action and controller if and only if the value specified 
     * is not null or empty string. This isolates the values should the be changed
     * during render processing.
     * 
     * @param string    the action to use for rendering
     * @param string    the controller to use for rendering
     */
    protected function set_render_values($action = NULL, $controller = NULL) {
        if(!empty($action)) {
            $this->render['action'] = $action;
        }
        if(!empty($controller)) {
            $this->render['controller'] = $controller;
        }
    } 

    /**
     * Renders a page with the instance layout and with the specified model. 
     * Optionally, you can specify a specific action and controller to override
     * the existing values.
     *
     * @param mixed the  model for the view
     * @param string    the action to use for rendering
     * @param string    the controller to use for rendering
     */
    public function view($model = NULL, $action = NULL, $controller = NULL) {
        $this->set_render_values($action, $controller);
        $this->model = $model;
        include $this->layout;
    }
    
    /**
     * Renders a partial page with the specified model. Optionally, you can 
     * specify a specific action and controller.
     *
     * @param mixed the  model for the view
     * @param string    the action to use for rendering
     * @param string    the controller to use for rendering
     */
    public function partial($model = NULL, $action = NULL, $controller = NULL) {
        $this->set_render_values($action, $controller);
        $this->model = $model;
        // assume the file exists, it's the developers responsibility to do it right
        include 'views/' . $this->render['controller'] . '/' . $this->render['action'] . '.php';
    }

    /**
     * Helper function that sets the output headers to values that request 
     * the browser not to cache server responses. Must be called prior to rendering.
     */
    public function no_cache() {
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
    }
    
    /**
     * Creates a response formatted in JSON and exits the application.
     * 
     * @param mixed the object, array, or primitive value to render as JSON
     * @param boolean   if FALSE, the response will be sent with HTTP headers requesting the browser not cache the response
     */
    public function json($model, $cache = FALSE) {
        header('Content-Type: application/json');
        if($cache === FALSE) {
            $this->no_cache();
        }
        echo json_encode($model);
        exit;
    }

    /**
     * A JSON helper overload for sending error responses in a standard format.
     * 
     * @param string    the message to send as an error
     */
    public function json_error($message) {
        $this->json(array('status' => 'error', 'message' => $message));
    }

    /**
     * A JSON helper overload for sending success responses in a standard format.
     */
    public function json_ok() {
        $this->json(array('status' => 'ok'));
    }
    
    /**
     * A JSON helper overload for sending data responses in a standard format.
     *
     * @param mixed The object, array, or primitive to send as data
     * @param boolean   TRUE if the request should be cached
     */
    public function json_data($data, $cache = FALSE) {
        $this->json(array('status' => 'ok', 'data' => $data), $cache);
    }
    
    /**
     * A JSON helper overload for sending session_expired responses in a standard format.
     */
    public function json_session_expired() {
        $this->json(array('status' => 'session_expired'));
    }
    
    /**
     * A JSON helper overload for sending redirect responses in a standard format.
     * 
     * @param string    the url to redirect to
     */
    public function json_redirect($url = null) {
        $this->json(array('status' => 'redirect', 'url' => $url));
    }

    /**
     * Sets the HTTP Response header to HTTP 404 Not Found and immediately exits the application.
     */
    public function not_found() {
        header('HTTP/1.0 404 Not Found');
        exit;
    }

    /**
     * Generates a URL for the specified parameters.
     *
     * @param string    the action to use for create the request
     * @param string    the controller to use for create the request
     * @param mixed an object, primative or array to be used as URL segment(s)
     * @param string    the language to use for the redirect, if null, the current language is used
     */
    public function route_url($action = NULL, $controller = NULL, $params = array(), $lang = NULL) {
        $segments = array();
        if(empty($lang)) {
            $lang = $this->request->lang;
        }
        
        // if we are in the default language, don't append to segment
        if($this->config->default_language != $lang) {
            $segments[] = $lang;
        }
        // set the controller
        $controller = empty($controller) ? $this->request->controller : $controller;
        
        // short circuit for the home/index url
        if($controller == 'home' && (empty($action) || $action == 'index') && empty($params)) {
            return '/' . implode('/', $segments);
        }

        // build the request
        $segments[] = $controller;
        if(!empty($action)) {
            $segments[] = $action;
        }
        if(!is_null($params)) {
            if(is_array($params)) {
                foreach($params as $seg) {
                    $segments[] = $seg;
                }
            }
            else {
                $segments[] = $params;
            }
        }
        return '/' . implode('/', $segments);
    }

    /**
     * Sets the HTTP Response header to HTTP 302 Moved Temporarily 
     * and immediately exits the application, requesting a redirect 
     * to the provided url.
     *
     * @param string    the URL to redirect to
     */
    public function redirect_url($url) {
        header('Location: '. $url);
        exit;
    }

    /**
     * A helper overload to redirect the user to the specified Action, Controller.
     *
     * @param string    the action to redirect to
     * @param string    the controller to redirect to
     * @param mixed    the id to redirect to
     */
    public function redirect($action = NULL, $controller = NULL, $id = NULL) {
        $this->redirect_url($this->route_url($action, $controller, $id));
    }

    /**
     * Outputs the set javascript files to the STDOUT.
     */
    public function render_scripts() {
        if(!empty($this->scripts)) {
            foreach($this->scripts as $script) {
                echo '<script type="text/javascript" src="', $script, '"></script>';
            }
        }
    }
    
    /**
     * Outputs the set css files to the STDOUT.
     */
    public function render_styles() {
        if(!empty($this->styles)) {
            foreach($this->styles as $style) {
                echo '<link rel="stylesheet" type="text/css" href="', $style, '" />';
            }
        }
    }

    /**
     * Outputs the page render to the STDOUT.
     */
    public function render_body() {
        $this->partial($this->model);
    }

    /**
     * Attempts to retrieve a language file in JSON format and return the value 
     * for the specified key. Passing in a lang value will force the language 
     * file to be read. If the file has not been loaded and the lang parameter
     * is not specifed, the file for the current language is read.
     *
     * @param string    the key for the language file
     * @param string    the optional parameter for loading the language
     * @return string   the language value for the specified key
     */
    public function get_lang($key, $lang = NULL) {
        // if loaded, attempt to return the key
        if($lang === NULL && $this->lang !== NULL) {
            return $this->lang->$key;
        }
        // set the lang, if not provided
        if(empty($lang)) {
            $lang = $this->request->lang;
            if(empty($lang)) {
                $lang = $this->config->default_language;
            }
        }
        // load the language file, assume the file exists
        $file = file_get_contents('lang/' . $lang . '.json');
        if($file === FALSE) {
            trigger_error('failed to load file: ' . 'lang/' . $lang . '.json', E_USER_WARNING);
            return NULL;
        }
        $this->lang = json_decode($file);
        if($this->lang === NULL) {
            trigger_error('syntax error in lang file', E_USER_WARNING);
            return NULL;
        }
        return $this->lang->$key;
    }
}

?>