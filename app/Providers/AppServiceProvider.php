<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Models\Wishlist;
use App\Models\About;
use App\Models\Category;
use App\Models\WhatsappSetting;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Menggunakan View Composer untuk membagikan data ke semua view ('*')
        View::composer('*', function ($view) {
            
            // --- DATA YANG DIBUTUHKAN SEMUA HALAMAN (FRONTEND & ADMIN) ---

            // 1. Ambil Nomor WhatsApp
            $whatsappNumber = '6281234567890'; // Nilai default untuk keamanan
            if (Schema::hasTable('whatsapp_settings')) {
                $setting = WhatsappSetting::where('key', 'whatsapp_number')->first();
                if ($setting) {
                    $whatsappNumber = $setting->value;
                }
            }
            $view->with('whatsappNumber', $whatsappNumber);

            // 2. Ambil Data Profil Usaha (untuk Logo)
            $about_us_data = About::first();
            $view->with('about_us_data', $about_us_data);


            // --- DATA KHUSUS HALAMAN DEPAN (TIDAK JALAN DI ADMIN) ---
            if (!request()->is('admin*')) 
            {
                // Data Wishlist
                $wishlistCount = 0;
                if (Auth::check()) {
                    $wishlistCount = Wishlist::where('user_id', Auth::id())->count();
                }
                $view->with('wishlistCount', $wishlistCount);

                // Data Kategori untuk Footer
                $footerCategories = Category::orderBy('name')->take(5)->get();
                $view->with('footerCategories', $footerCategories);
            }
        });
    }
}

