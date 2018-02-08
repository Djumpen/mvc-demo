<?php

namespace MVCApp\Controller\Component;

use MVCApp\Exception\MissingParameterException;
use MVCApp\Lib\Hash;
use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Validator;

/**
 * Validation component
 *
 * @see https://github.com/Respect/Validation/tree/master/docs
 */
class JsonValidationComponent implements ComponentInterface {

    public function required($fields, $inputData, $errorCallback = ''){
        if(!$inputData){
            $error = 'Input data expected';
            $this->throwError($error, $errorCallback);
        }
        $this->checkStep($fields, $inputData, '', $errorCallback, true);
    }

    public function optional($fields, $inputData, $errorCallback = ''){
        $this->checkStep($fields, $inputData, '', $errorCallback, false);
    }

    private function checkStep($fields, $inputData, $path, $errorCallback, $isRequired){
        foreach ($fields as $key => $val){
            if(is_array($val)){
                $newPath = $path ? ($path . '.' . $key) : $key;
                $this->checkStep($val, $inputData, $newPath, $errorCallback, $isRequired);
            } else {
                $this->validateParameter($key, $val, $inputData, $path, $errorCallback, $isRequired);
            }
        }
    }

    private function validateParameter($key, $val, $inputData, $path, $errorCallback, $isRequired){
        if(is_int($key)){
            $fieldName = $val;
            $validator = null;
        } else {
            $fieldName = $key;
            $validator = $val;
        }

        $data = Hash::extract($inputData, $path);

        if(!$path){
            $data = $inputData;
        } elseif(!$data){
            $error = "'$path' expected";
            if($isRequired){
                $this->throwError($error, $errorCallback);
            } else {
                return;
            }
        }

        $error = "'$fieldName' expected";
        if($path)
            $error = "'$path.$fieldName' expected";

        if (!isset($data[$fieldName])) {
            if($isRequired){
                $this->throwError($error, $errorCallback);
            } else {
                return;
            }
        } elseif (!is_array($data[$fieldName]) && strlen($data[$fieldName]) == 0) {
            if($isRequired){
                $this->throwError($error, $errorCallback);
            } else {
                return;
            }
        }

        if($validator){
            $varName = "'$fieldName'";
            if($path)
                $varName = "'$path.$fieldName'";
            $this->execValidator($validator, $data[$fieldName], $varName, $errorCallback);
        }
    }

    private function execValidator($validator, $value, $name, $callback){
        if($validator instanceof Validator){
            try {
                if(!$validator->getName()){
                    $validator->setName($name);
                }
                $validator->check($value);
            } catch (ValidationException $e){
                $this->throwError($e->getMessage(), $callback);
            }
        }
        return true;
    }

    private function throwError($errorMessage, $callback){
        if(is_object($callback) && get_class($callback) == 'Closure')
            $callback($errorMessage);
        throw new MissingParameterException($errorMessage);
    }
}