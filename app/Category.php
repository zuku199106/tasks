<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';
	protected $primaryKey = 'id';

	public function task() {
		return $this->hasMany('App\Task');
	}
}
