<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Food;
use Illuminate\Http\Request;

class FoodController extends Controller
{
public function all(Request $request)
{
    $id = $request->input('id');
    $limit = $request->input('limit', 6);
    $name = $request->input('name');
    $types = $request->input('types');

    $price_form = $request->input('price_from');
    $price_to = $request->input('price_to');

    $rate_form = $request->input('rate_from');
    $rate_to = $request->input('rate_to');

    if($id)
    {
        $food =Food::find($id);

        if($food)
        {
            return ResponseFormatter::success($food,'Data Produk Berhasil Diambil');
        } else{
            return ResponseFormatter::error(null,'Data Produk Tidak Ada' ,404);
        }
    }

    $food = Food::query();
    if($name)
    {
        $food->where('name','like','%' . $name . '%');
    }

    if($types)
    {
        $food->where('name','like','%' . $types . '%');
    }

    if($price_form)
    {
        $food->where('price','>=',$price_form);
    }

    if($price_to)
    {
        $food->where('price','<=',$price_to );
    }

    if($rate_form)
    {
        $food->where('rate','>=',$rate_form);
    }

    if($rate_to)
    {
        $food->where('rate','>=',$rate_to);
    }


    return ResponseFormatter::success(
        $food->paginate($limit),
        'Data List Produk berhasil diambil'
    );




}
}
