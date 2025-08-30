<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

// Models

// Helpers
use App\Helpers\Generator;
use App\Helpers\Audit;

class EmbedController extends Controller
{
    public function distribution_inventory_category()
    {
        return view('embed.distribution_inventory_category');
    }

    public function distribution_inventory_room()
    {
        return view('embed.distribution_inventory_room');
    }

    public function distribution_inventory_favorite()
    {
        return view('embed.distribution_inventory_favorite');
    }

    public function distribution_inventory_merk()
    {
        return view('embed.distribution_inventory_merk');
    }

    public function inventory_created_per_month($year)
    {
        return view('embed.inventory_created_per_month')->with('year',$year);
    }
}
