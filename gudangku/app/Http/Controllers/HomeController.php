<?php

namespace App\Http\Controllers;

use App\Models\InventoryModel;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $inventory = InventoryModel::select('*')
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

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
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
