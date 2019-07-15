<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable=['name','faculty_id'];

    public function faculty() {
        return $this->belongsTo(Faculty::class,'faculty_id','id');
    }
}