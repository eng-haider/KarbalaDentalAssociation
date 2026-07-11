<?php

namespace App\Http\Controllers;

use App\Support\SiteData;

class HomeController extends Controller
{
    public function index()
    {
        return view('home', [
            'org'           => SiteData::org(),
            'nav'           => SiteData::nav(),
            'stats'         => SiteData::stats(),
            'heroSlides'    => SiteData::heroSlides(),
            'news'          => SiteData::news(),
            'announcements' => SiteData::announcements(),
            'eduCategories' => SiteData::eduCategories(),
            'playlists'     => SiteData::playlists(),
            'services'      => SiteData::services(),
            'events'        => SiteData::events(),
            'gallery'       => SiteData::gallery(),
            'publications'  => SiteData::publications(),
            'about'         => SiteData::about(),
        ]);
    }
}
