<?php

namespace MVCApp\Controller;

use MVCApp\Lib\{Hash, Paginator};

class TasksController extends BaseController {

    static $defaultLimit = 3;

    public function indexAction() {
        $currentPage = $this->getRequestParam('page', 1);

        $order = $this->getRequestParam('order', 'created_at');
        if(!in_array(strtolower($order), ['created_at', 'name', 'email', 'is_done'])) {
            $order = 'created_at';
        }

        $direction = $this->getRequestParam('direction', 'desc');
        if(!in_array(strtolower($direction), ['asc', 'desc'])) {
            $direction = 'desc';
        }

        $filter = [
            'order'         => $order,
            'direction'     => $direction,
            'offset'        => ($currentPage - 1) * self::$defaultLimit,
            'limit'         => self::$defaultLimit,
        ];

        $tasks = $this->model('Task')->findAll($filter);
        $totalItems = $this->model('Task')->count($filter);

        $urlPattern = "?page=(:num)&order={$order}&direction={$direction}";

        $paginator = new Paginator($totalItems, self::$defaultLimit, $currentPage, $urlPattern);

        $tasks = Hash::map($tasks, '{n}', [$this, 'listFormatter']);

        return $this->render('list', [
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