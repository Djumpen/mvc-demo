<?php

namespace MVCApp\Controller;

use MVCApp\Controller\Component\{
    AuthComponent, ComponentInterface, JsonValidationComponent
};
use MVCApp\Lib\Hash;
use MVCApp\Model\BaseModel;
use Pimple\Container;
use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Validator;

/**
 * @property JsonValidationComponent $Validation
 * @property AuthComponent $Auth
 */
abstract class BaseController {

    /**
     * @var Container
     */
    private $di;

    /**
     * @var array
     */
    private $models = [];

    /**
     * @var bool
     */
    private $_isExecute = true;

    /**
     * @var string
     */
    protected $_content = '';

    /**
     * @var array
     */
    protected $vars;

    /**
     * @var \Twig_Environment
     */
    protected $view;

    public function __construct(Container $di, $vars = []) {
        $this->di = $di;
        $this->vars = $vars;

        // Default components
        $this->registerComponent('Auth', new AuthComponent($di));
        $this->registerComponent('Validation', new JsonValidationComponent());

        // View params
        $this->view = $this->di['twig'];

        foreach($this->getConfigVal('template_defaults', true) as $key => $val) {
            $this->view->addGlobal($key, $val);
        }
        $this->view->addGlobal('is_logged', (bool)$this->Auth->getUser());

        $this->_content = $this->beforeAction();
    }

    /**
     * @param string $name
     * @param $component
     * @throws \Exception
     */
    protected function registerComponent($name, $component): void {
        if(property_exists($this, $name) || preg_match('/^[^_A-z]?[^_A-z0-9]*$/', $name)) {
            throw new \Exception('Component name unavailable');
        }
        if(!($component instanceof ComponentInterface)) {
            throw new \Exception('Wrong component class');
        }
        $this->$name = $component;
    }

    /**
     * @param $name
     * @throws \Exception
     */
    private function loadModel($name): void {
        if(isset($this->models[$name]))
            return;

        $className = "MVCApp\\Model\\" . $name;
        if(!class_exists($className)) {
            throw new \Exception('Model not found');
        }

        $this->models[$name] = new $className($this->di);
    }

    /**
     * @param $name
     * @return mixed
     */
    protected function model($name): BaseModel {
        $this->loadModel($name);
        return $this->models[$name];
    }

    /**
     * @return mixed
     */
    protected function getRequestParams(): array {
        if($_SERVER['REQUEST_METHOD'] == 'GET') {
            return $_GET;
        } else {
            parse_str(file_get_contents("php://input"), $requestVars);
            return $requestVars;
        }
    }

    /**
     * @param $name
     * @param null $default
     * @return mixed
     */
    protected function getRequestParam($name, $default = null) {
        return $this->getRequestParams()[$name] ?? $default;
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function getRequestJson(): array {
        $json = json_decode(file_get_contents("php://input"), true);
        if($json == null) {
            throw new \Exception('Common.JSON.expected');
        }
        return $json;
    }

    /**
     * @param string $template
     * @param array $context
     * @return string
     */
    protected function render($template, $context = []): string {
        if(substr($template, 0, 1) != '/') {
            $className = (new \ReflectionClass($this))->getShortName();
            $className = strtolower(str_replace('Controller', '', $className));
            $template = $className . DS . $template;
        }

        if(strpos($template, '.html') === false){
            $template .= '.html';
        }

        return $this->view->render($template, $context);
    }

    /**
     * @param string $path
     * @param bool $isArray
     * @return mixed
     */
    protected function getConfigVal($path, $isArray = false) {
        $arrayVal = Hash::extract($this->di['config'], $path);
        if($isArray && is_array($arrayVal)) {
            return $arrayVal;
        }
        return $arrayVal[0] ?? null;
    }

    /**
     * @param array $validators
     * @param array $data
     * @return bool
     * @throws ValidationException
     * @throws \Exception
     */
    protected function validate(array $validators, array $data) {
        foreach ($validators as $name => $validator) {
            if ($validator instanceof Validator) {
                $value = $data[$name] ?? null;
                if (!$validator->getName()) {
                    $validator->setName($name);
                }
                $validator->check($value);
            } else {
                throw new \Exception('Invalid validator');
            }
        }
    }

    protected function redirect($location) {
        header("Location: " . $location);
        exit;
    }

    protected function beforeAction() {
        return '';
    }

    public function _getContent() {
        return $this->_content;
    }

    protected function _stop() {
        $this->_isExecute = false;
    }

    public function _isExecute(): bool {
        return $this->_isExecute;
    }
}