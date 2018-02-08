<?php

namespace MVCApp\Controller;

use Josantonius\Session\Session;
use MVCApp\Lib\{Hash, Paginator};
use Respect\Validation\Exceptions\ValidationException;

class AdminController extends BaseController {

    public function beforeAction() {
        parent::beforeAction();

        if(!$this->Auth->isAdmin()) {
            $loginUri = '/admin/login';
            if($this->vars['_uri'] != $loginUri) {
                $this->redirect($loginUri);
            }
        }
    }

    public function loginAction() {
        if($this->Auth->getUser()) {
            $this->redirect('/admin');
            return false;
        }

        $templateData = [];

        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $validators = [
                    'login' => \v::regex('/^[A-z0-9_]+$/')->length(3, 50),
                    'password' => \v::length(3, 50)
                ];
                $requestData = $this->getRequestParams();
                $this->validate($validators, $this->getRequestParams());

                $user = $this->model('User')->findFirstByLogin($requestData['login']);

                if($user && password_verify($requestData['password'], $user['password'])) {
                    $this->Auth->setUser($user);
                    $this->redirect('/admin');
                    return false;
                } else {
                    $templateData['error'] = 'Login invalid';
                }
            } catch (ValidationException $e) {
                $templateData['error'] = $e->getMessage();
            }
        }
        return $this->render('login', $templateData);
    }

    public function logoutAction() {
        Session::destroy();
        $this->redirect('/');
        return false;
    }

    public function listAction() {
        $defaultLimit = 5;

        $currentPage = $this->getRequestParam('page', 1);

        $order = $this->getRequestParam('order', 'created_at');
        if(!in_array(strtolower($order), ['created_at', 'name', 'email'])) {
            $order = 'created_at';
        }

        $direction = $this->getRequestParam('direction', 'desc');
        if(!in_array(strtolower($direction), ['asc', 'desc'])) {
            $direction = 'desc';
        }

        $filter = [
            'order'         => $order,
            'direction'     => $direction,
            'offset'        => ($currentPage - 1) * $defaultLimit,
            'limit'         => $defaultLimit,
        ];

        $tasks = $this->model('Task')->findAll($filter);
        $totalItems = $this->model('Task')->count($filter);

        $urlPattern = "?page=(:num)&order={$order}&direction={$direction}";

        $paginator = new Paginator($totalItems, $defaultLimit, $currentPage, $urlPattern);

        $tasks = Hash::map($tasks, '{n}', [$this, 'listFormatter']);

        return $this->render('tasks/list', [
            'title'     => 'MVC Tasks',
            'tasks'     => $tasks,
            'paginator' => $paginator,
            'page'      => $currentPage,
            'order'     => $order,
            'direction' => strtolower($direction) == 'asc' ? 'desc' : 'asc'
        ]);
    }

    public function listFormatter($item) {
        if($item['image_path']) {
            $path = $this->getConfigVal('app.uploads_dir') . $item['image_path'];
            if(file_exists($path)) {
                $item['image'] = $this->getConfigVal('app.uploads_path') . $item['image_path'];
                return $item;
            }
        }
        $item['image'] = $this->getConfigVal('app.no_image');
        return $item;
    }

}