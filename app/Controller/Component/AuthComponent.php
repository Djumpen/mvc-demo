<?php

namespace MVCApp\Controller\Component;

use Josantonius\Session\Session;
use Pimple\Container;

class AuthComponent implements ComponentInterface {

    /**
     * @var Container
     */
    private $di;

    private $user = null;

    public function __construct(Container $di) {
        $this->di = $di;
        Session::init();

        $this->user = Session::get('user');
    }

    public function isAdmin(): bool {
        if($this->user) {
            return (bool)$this->user['is_admin'] ?? false;
        }
        return false;
    }

    public function setUser($user): void {
        $this->user = $user;
        Session::set('user', $user);
    }

    public function getUser(): ?array {
        return $this->user;
    }

}