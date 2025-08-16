<?php
/* [SJH] */

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use App\Models\User; 

require_once(config('app.root')."/app/Libraries/code.php");
require_once(config('app.root2')."/vwmldbm/config.php");
require_once(config('app.root2')."/vwmldbm/lib/code.php");

class AdminController {
    public function index(){
        return view('admin.AdminDashboard');
    }
}