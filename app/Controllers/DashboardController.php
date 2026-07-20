<?php

namespace App\Controllers;

class DashboardController extends BaseController
{
    public function dashboard(): string
    {
        return view('dashboard');
    }

    public function calculGain($idTypeOperation, $dateMin = null, $dateMax = null){
        
    }
}
