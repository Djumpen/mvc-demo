<?php

namespace MVCApp\Controller;

abstract class RestController extends BaseController {

    protected function jsonResponse200($response = null) {
        return $this->jsonResponse(200, $response);
    }

    protected function jsonResponse201($response) {
        return $this->jsonResponse(201, $response);
    }

    protected function jsonResponse400($message) {
        if(is_string($message)){
            return $this->jsonResponse(400, [
                'message' => $message
            ]);
        } elseif (is_array($message)) {
            return $this->jsonResponse(400, $message);
        }
    }

    protected function jsonResponse404($message) {
        return $this->jsonResponse(404, [
            'message' => $message
        ]);
    }

    private function setDefaultHeaders() {
        $allowHeaders = [
            'Origin',
            'X-Requested-With',
            'Content-Type',
            'Accept',
            'Cache-Control',
            'Pragma',
            'If-Modified-Since',
        ];

        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: ' . implode(',', $allowHeaders));
        header('Access-Control-Allow-Methods: OPTIONS, GET, POST, PATCH, PUT, DELETE');
        header('Access-Control-Allow-Credentials: true');
    }

    protected function jsonResponse($status, $response = null) {
        $this->setDefaultHeaders();

        http_response_code($status);

        if ($response && is_array($response)) {
            header('Content-Type: application/json');
            return json_encode($response);
        } else {
            header('Content-Type: text/html');
            return $response;
        }
    }
}