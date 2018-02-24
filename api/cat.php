<?php
import("api/api");


class Cat extends Api
{

    public $table="cat";
    public $rule = [
        'title' => 'max_length:24|min_length:3|unique:title',
    ];

}