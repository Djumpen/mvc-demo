<?php

return function(FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/', 'Tasks.index');
    $r->addRoute('GET', '/uploads/{file:.+}', 'Uploads.fetch');

    $r->addGroup('/api', function (\FastRoute\RouteCollector $r) {
        $r->addRoute('POST', '/tasks/task', 'TasksApi.create');
        $r->addRoute('POST', '/tasks/task/image', 'TasksApi.uploadImage');
        $r->addRoute('POST', '/tasks/task/preview', 'TasksApi.preview');
        $r->addRoute('PUT', '/tasks/task/{id:\d+}', 'TasksApi.update');
        $r->addRoute('PUT', '/tasks/task/{id:\d+}/complete', 'TasksApi.complete');
    });

    $r->addGroup('/admin', function (\FastRoute\RouteCollector $r) {
        $r->addRoute('GET', '', 'Admin.list');
        $r->addRoute(['GET', 'POST'], '/login', 'Admin.login');
        $r->addRoute(['GET'], '/logout', 'Admin.logout');
    });
};
