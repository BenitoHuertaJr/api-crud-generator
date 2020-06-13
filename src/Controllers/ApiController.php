<?php

namespace iamx\ApiCrudGenerator\Controllers;

use iamx\ApiCrudGenerator\Traits\ApiResponserTrait;
use App\Http\Controllers\Controller;

class ApiController extends Controller
{
    use ApiResponserTrait;

    public function __construct(){}
}