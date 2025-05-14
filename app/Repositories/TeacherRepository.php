<?php

namespace App\Repositories;

use App\Interfaces\TeacherRepositoryInterface;
use App\Models\Teacher;

class TeacherRepository implements TeacherRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function index(){
        return Teacher::with(['educationLevel'])->orderBy('created_at', 'desc')->get();
    }

    public function show($id){
       return Teacher::with(['educationLevel'])->findOrFail($id);
    }

    public function store(array $data){
       return Teacher::create($data);
    }

    public function update(array $data,$id){
       return Teacher::whereId($id)->update($data);
    }

    public function delete($id){
        Teacher::destroy($id);
    }
}
