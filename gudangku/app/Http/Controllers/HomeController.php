<?php

namespace App\Http\Controllers;

use App\Models\InventoryModel;

use App\Helpers\Generator;

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

    public function soft_delete($id)
    {
        InventoryModel::where('id',$id)->update([
            'deleted_at' => date('Y-m-d H:i:s')
        ]);

        return redirect()->back();
    }

    public function hard_delete($id)
    {
        InventoryModel::destroy($id);

        return redirect()->back();
    }

    public function recover($id)
    {
        InventoryModel::where('id',$id)->update([
            'deleted_at' => null
        ]);

        return redirect()->back();
    }

    public function fav_toogle(Request $request, $id)
    {
        InventoryModel::where('id',$id)->update([
            'is_favorite' => $request->is_favorite
        ]);

        return redirect()->back();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
