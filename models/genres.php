<?php

class genres
{
    private $db;

    function __construct($db)
    {
        $this->db = $db;
    }

    function add($data)
    {
        $stmt = $this->db->prepare("SELECT * FROM `genres` WHERE `name_ru`=? OR `name_kz`=?");
        $stmt->bind_param('ss', $data['name_ru'], $data['name_kz']);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        if ($row != null) {
            header("HTTP/1.1 409 libraries with name already exists");
            return 'Такой жанр с названием именем уже есть!';
        } else {
            $stmt = $this->db->prepare("INSERT INTO `genres` (`name_ru`, `name_kz`) VALUES (?, ?)");
            $stmt->bind_param('ss', $data['name_ru'], $data['name_kz']);
            $result = $stmt->execute();
            if ($result) {
                return $result;
            } else {
                header("HTTP/1.1 501 error while inserting");
                return $result;
            }
        }
    }

    function get()
    {
        $arr = [];
        $stmt = $this->db->prepare("SELECT * FROM `genres`");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            array_push($arr, $row);
        }
        return $arr;
    }

    function edit($data)
    {
        $stmt = $this->db->prepare("UPDATE `genres` SET `name_ru` = ?, `name_kz` = ? WHERE `id` = ?");
        $stmt->bind_param('ssi', $data['name_ru'], $data['name_kz'],$data['id']);
        $result = $stmt->execute();
        if ($result) {
            return $result;
        } else {
            header("HTTP/1.1 500 bad request");
            return $result;
        }
    }

    function delete($data)
    {
        $stmt = $this->db->prepare("DELETE FROM `genres` WHERE `id` = ?");
        $stmt->bind_param('i', $data['id']);
        $result = $stmt->execute();
        if ($result) {
            return $result;
        } else {
            header("HTTP/1.1 500 bad request");
            return $result;
        }
    }

}
