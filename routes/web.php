<?php

use App\Models\Project;
use App\Models\Inventory;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $projects = Project::all();
    $inventory = Inventory::all();
    return view('dashboard', compact('projects', 'inventory'));
});
