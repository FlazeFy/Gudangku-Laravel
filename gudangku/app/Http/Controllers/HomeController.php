<?php

namespace App\Http\Controllers;

use App\Models\InventoryModel;

use App\Helpers\Generator;
use App\Helpers\Audit;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user_id = Generator::getUserId(session()->get('role_key'));

        $inventory = InventoryModel::select('*')
            ->where('created_by',$user_id)
            ->orderBy('is_favorite', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('home.index')
            ->with('inventory',$inventory);
    }

    public function soft_delete(Request $request, $id)
    {
        InventoryModel::where('id',$id)->update([
            'deleted_at' => date('Y-m-d H:i:s')
        ]);

        Audit::createHistory('Delete item', $request->inventory_name);

        return redirect()->back();
    }

    public function hard_delete(Request $request, $id)
    {
        InventoryModel::destroy($id);

        Audit::createHistory('Permentally delete item', $request->inventory_name);

        return redirect()->back();
    }

    public function recover(Request $request, $id)
    {
        InventoryModel::where('id',$id)->update([
            'deleted_at' => null
        ]);

        Audit::createHistory('Recover item', $request->inventory_name);

        return redirect()->back();
    }

    public function fav_toogle(Request $request, $id)
    {
        InventoryModel::where('id',$id)->update([
            'is_favorite' => $request->is_favorite
        ]);

        Audit::createHistory('Set to favorite', $request->inventory_name);

        return redirect()->back();
    }
}
