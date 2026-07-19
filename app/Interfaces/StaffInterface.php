<?php

namespace App\Interfaces;

interface StaffInterface {
    public function getAllStaff();
    public function findById($id);
    public function create($request);
    public function update($request, $id);
    public function delete($id);
}
