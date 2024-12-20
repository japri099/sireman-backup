<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder; // Builder digunakan untuk membuat query
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    // Daftar atribut yang dapat diisi secara massal
    protected $fillable = ['kode_menu', 'kategori', 'deskripsi', 'harga'];

    public function scopeFilterBySearch(Builder $query, ?string $searchTerm): Builder
    {
        // Periksa apakah searchTerm tidak null atau kosong
        return $query->when($searchTerm, function ($q) use ($searchTerm) {
            // Terapkan pencarian pada kolom 'kode_menu', 'kategori', 'deskripsi', dan 'harga'
            $q->where('kode_menu', 'LIKE', '%' . $searchTerm . '%')
              ->orWhere('kategori', 'LIKE', '%' . $searchTerm . '%')
              ->orWhere('deskripsi', 'LIKE', '%' . $searchTerm . '%')
              ->orWhere('harga', 'LIKE', '%' . $searchTerm . '%');
        });
    }
}
