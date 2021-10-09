<?php


namespace App\Utilities;


use Symfony\Component\HttpFoundation\Request;

class Pager
{

    public function __construct($pageKey = 'page', $perPageKey = 'perPage')
    {
    }

    public function processRequest(Request $request)
    {
        return [
            'pageCount' => 0,
            'results' => []
        ];
    }

}