<?php

namespace MVCApp\Controller;

class UploadsController extends BaseController {

    public function fetchAction() {
        $filePath = $this->getConfigVal('app.uploads_dir') . $this->vars['file'];

        if(file_exists($filePath)) {
            $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($filePath);
            header('Content-Type: ' . $mime);
            readfile($filePath);
        } else {
            throw new \Exception('File not found', 404);
        }
    }

}