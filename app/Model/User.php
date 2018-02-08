<?php

namespace MVCApp\Model;

class User extends BaseModel {

    static $table = 'user';

    public function findFirstByLogin($login) {

        $user = $this->db->select(self::$table, [
            'login',
            'password',
            'is_admin'
        ], [
            'login' => $login,
            'LIMIT' => 1,
        ]);

        return $user[0] ?? null;
    }

}