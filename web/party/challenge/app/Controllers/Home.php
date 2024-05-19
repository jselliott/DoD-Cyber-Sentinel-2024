<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        $db = db_connect();
        $query = $db->query('SELECT * FROM characters WHERE hidden=0');
        
        return view('builder',array("characters"=>$query->getResult()));
    }
}
