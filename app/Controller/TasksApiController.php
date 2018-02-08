<?php

namespace MVCApp\Controller;

use MVCApp\Exception\MissingParameterException;

class TasksApiController extends RestController {

    public function createAction() {
        $jsonData = $this->getRequestJson();

        try {
            $this->validateTask($jsonData);
        } catch (MissingParameterException $e) {
            return $this->jsonResponse400($e->getMessage());
        }

        $task = [
            'name'          => $jsonData['Task']['name'],
            'email'         => $jsonData['Task']['email'],
            'content'       => $jsonData['Task']['content'],
            'image_path'    => $jsonData['Task']['image_path'],
        ];

        $taskId = $this->model('Task')->create($task);

        if(!$taskId) {
            return $this->jsonResponse400('Error while creating');
        }

        return $this->jsonResponse201([
            'Task' => [
                'id' => $taskId
            ]
        ]);
    }

    public function previewAction() {
        $jsonData = $this->getRequestJson();

        try {
            $this->validateTask($jsonData);
        } catch (MissingParameterException $e) {
            return $this->jsonResponse400($e->getMessage());
        }

        $image = $this->getConfigVal('app.no_image');
        if($jsonData['Task']['image_path']){
            $path = $this->getConfigVal('app.uploads_dir') . $jsonData['Task']['image_path'];
            if(file_exists($path)) {
                $image = $this->getConfigVal('app.uploads_path') . $jsonData['Task']['image_path'];
            }
        }

        $task = [
            'name'          => $jsonData['Task']['name'],
            'email'         => $jsonData['Task']['email'],
            'content'       => $jsonData['Task']['content'],
            'is_done'       => 0,
            'image'         => $image,
        ];

        return $this->render('/tasks/list_item', compact('task'));
    }

    /**
     * @param $jsonData
     * @throws MissingParameterException
     */
    private function validateTask($jsonData) {
        $this->Validation->required([
            'Task' => [
                'name' => \v::stringType()->length(1, 50),
                'email' => \v::email(),
                'content' => \v::stringType()->length(1, null),
            ]
        ], $jsonData);

        $this->Validation->optional([
            'Task' => [
                'image_path' => \v::stringType()
            ]
        ], $jsonData);
    }

    public function uploadImageAction() {
        if(empty($_FILES['image'])) {
            return $this->jsonResponse400('"image" required');
        }

        $uploader = new \upload($_FILES['image']);

        $filename = uniqid();

        $uploader->file_new_name_body   = $filename;
        $uploader->image_resize         = true;
        $uploader->image_x              = 320;
        $uploader->image_y              = 240;
        $uploader->allowed = [
            'image/gif',
            'image/jpeg',
            'image/png'
        ];

        if (!$uploader->uploaded) {
            return $this->jsonResponse400($uploader->error);
        }

        $uploader->process($this->getConfigVal('app.uploads_dir'));
        $uploader->clean();

        if (!$uploader->processed) {
            return $this->jsonResponse400($uploader->error);
        }

        return $this->jsonResponse201([
            'image_path' => $uploader->file_dst_name,
            'image_url'  => $this->getConfigVal('app.uploads_path') . $uploader->file_dst_name
        ]);
    }

    public function updateAction() {
        if(!$this->Auth->isAdmin()) {
            return $this->jsonResponse400('Access denied');
        }

        $taskId = $this->vars['id'];
        $jsonData = $this->getRequestJson();

        $task = $this->model('Task')->findFirst($taskId);

        if(!$task) {
            return $this->jsonResponse404('Task not found');
        }

        try {
            $this->Validation->required([
                'Task' => [
                    'content' => \v::stringType()->length(1, null)
                ]
            ], $jsonData);
        } catch (MissingParameterException $e) {
            return $this->jsonResponse400($e->getMessage());
        }

        $data = [
            'content' => $jsonData['Task']['content']
        ];

        $this->model('Task')->updateFirst($taskId, $data);

        return $this->jsonResponse200();
    }

    public function completeAction() {
        if(!$this->Auth->isAdmin()) {
            return $this->jsonResponse400('Access denied');
        }

        $taskId = $this->vars['id'];
        $jsonData = $this->getRequestJson();

        $task = $this->model('Task')->findFirst($taskId);

        if(!$task) {
            return $this->jsonResponse404('Task not found');
        }

        try {
            $this->Validation->required([
                'Task' => [
                    'is_done' => \v::intVal()
                ]
            ], $jsonData);
        } catch (MissingParameterException $e) {
            return $this->jsonResponse400($e->getMessage());
        }

        $data = [
            'is_done' => $jsonData['Task']['is_done']
        ];

        $this->model('Task')->updateFirst($taskId, $data);

        return $this->jsonResponse200();
    }

}