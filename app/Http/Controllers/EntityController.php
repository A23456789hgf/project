<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

class EntityController extends Controller
{
    // دالة عرض البيانات
    public function index()
    {
        // قراءة ملف JSON من مجلد storage/app/data
        $json = Storage::get('data/entities.json');

        // تحويل JSON إلى مصفوفة
        $entities = json_decode($json, true);

        // تمرير البيانات إلى صفحة Blade
        return view('configuration.entities.index', compact('entities'));
    }
}
