<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class UIShowcaseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        if (! auth()->user()->isAdmin()) {
            abort(403, 'غير مصرح لك بالوصول لمعرض مكونات النظام');
        }

        return view('admin.ui-showcase');
    }
}
