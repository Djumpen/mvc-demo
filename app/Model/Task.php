<?php

namespace MVCApp\Model;

class Task extends BaseModel {

    static $table = 'task';

    public function findAll($filter) {
        $this->setDefaults($filter, [
            'limit'         => 10,
            'offset'        => 0,
            'order'         => 'id',
            'direction'     => 'asc'
        ]);

        $order = self::$table . '.' . $filter['order'];

        $where = [
            'LIMIT'         => [$filter['offset'], $filter['limit']],
            'ORDER'         => [$order => strtoupper($filter['direction'])]
        ];

        return $this->db->select(self::$table, '*', $where);
    }

    public function findFirst($id) {
        $tasks = $this->db->select(self::$table, '*', [
            'id' => $id,
        ]);

        return $tasks[0] ?? null;
    }

    public function count($filter) {
        $where = [];
        if(isset($filter['is_done'])){
            $where['is_done'] = $filter['is_done'];
        }

        return $this->db->count(self::$table, '*', $where);
    }

    public function create($data) {
        $this->setDefaults($data, [
            'is_done'       => 0,
            'created_at'    => $this->datetime(),
            'updated_at'    => $this->datetime()
        ]);

        $this->db->insert(self::$table, $data);

        return $this->db->id();
    }

    public function updateFirst($id, $data) {
        $this->db->update(self::$table, $data, [
            'id' => $id
        ]);
    }

}