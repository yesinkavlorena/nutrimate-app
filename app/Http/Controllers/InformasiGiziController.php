<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Makanan;

class InformasiGiziController extends Controller
{
    public function index()
    {
        $makanan = Makanan::orderBy('nama_makanan', 'asc')->get();

        return view('informasi-gizi.index', compact('makanan'));
    }
}