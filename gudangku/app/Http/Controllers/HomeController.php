<?php

namespace App\Http\Controllers;

use App\Models\InventoryModel;
use App\Models\DictionaryModel;

use App\Helpers\Generator;
use App\Helpers\Audit;

use App\Exports\InventoryExport;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user_id = Generator::getUserId(session()->get('role_key'));

        if($user_id != null){
            $selected = session()->get('toogle_view_inventory');
            if($selected == 'table'){
                $inventory = InventoryModel::select('*')
                    ->where('created_by',$user_id)
                    ->orderBy('is_favorite', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->get();

                return view('home.index')
                    ->with('inventory',$inventory);
            } elseif($selected == 'catalog'){
                $room = DictionaryModel::selectRaw('dictionary_name, COUNT(1) as total')
                    ->leftjoin('inventory','inventory_room','=','dictionary_name')
                    ->where('dictionary_type','inventory_room')
                    ->groupby('dictionary_name')
                    ->orderby('dictionary_name','ASC')
                    ->get();

                $category = DictionaryModel::selectRaw('dictionary_name, COUNT(1) as total')
                    ->leftjoin('inventory','inventory_category','=','dictionary_name')
                    ->where('dictionary_type','inventory_category')
                    ->groupby('dictionary_name')
                    ->orderby('dictionary_name','ASC')
                    ->get();

                $storage = InventoryModel::selectRaw('inventory_storage, COUNT(1) as total')
                    ->whereNotNull('inventory_storage')
                    ->where('created_by', $user_id)
                    ->groupby('inventory_storage')
                    ->orderby('inventory_storage','ASC')
                    ->get();

                return view('home.index')
                    ->with('room',$room)
                    ->with('category',$category)
                    ->with('storage',$storage);
            }
        } else {
            return redirect("/login");
        }
    }

    public function catalog_index($view, $context)
    {
        $user_id = Generator::getUserId(session()->get('role_key'));

        if($user_id != null){
            if($view == "room"){
                $other = DictionaryModel::select('dictionary_name')
                    ->where('dictionary_type','inventory_room')
                    ->orderby('dictionary_name','ASC')
                    ->get();
            } else if($view == "category"){
                $other = DictionaryModel::select('dictionary_name')
                    ->where('dictionary_type','inventory_category')
                    ->orderby('dictionary_name','ASC')
                    ->get();
            } else if($view == "storage"){
                $other = InventoryModel::select('inventory_storage')
                    ->whereNotNull('inventory_storage')
                    ->where('created_by', $user_id)
                    ->groupby('inventory_storage')
                    ->orderby('inventory_storage','ASC')
                    ->get();
            }

            $inventory = InventoryModel::select('*')
                ->where('created_by', $user_id)
                ->where("inventory_$view", $context)
                ->orderBy('is_favorite', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return view('home.catalog.index')
                ->with('other',$other)
                ->with('view',$view)
                ->with('context',$context)
                ->with('inventory',$inventory);
        } else {
            return redirect("/login");
        }
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

    public function save_as_csv(){
        $data = InventoryModel::all();
        $file_name = date('l, j F Y \a\t H:i:s');

        Audit::createHistory('Print item', 'Inventory');

        return Excel::download(new InventoryExport($data), "$file_name-Inventory Data.xlsx");
    }

    public function fav_toogle(Request $request, $id)
    {
        InventoryModel::where('id',$id)->update([
            'is_favorite' => $request->is_favorite
        ]);

        Audit::createHistory('Set to favorite', $request->inventory_name);

        return redirect()->back();
    }

    public function toogle_view(Request $request)
    {
        $request->session()->put('toogle_view_inventory', $request->toogle_view);

        return redirect()->back();
    }
}
