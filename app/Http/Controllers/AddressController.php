<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    /**
     * Store a newly created address in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'area_id' => 'required|exists:areas,id',
            'content' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        $address = new Address();
        $address->area_id = $request->area_id;
        $address->content = $request->content;
        $address->phone = $request->phone;
        $address->user_id = Auth::id();
        $address->save();

        return response()->json([
            'message' => 'Address created successfully.',
            'address' => $address->load('area.gov'),
        ], 201);
    }

    /**
     * Get all areas for address form.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAreas()
    {
        $areas = Area::with('gov')->get();

        return response()->json([
            'areas' => $areas
        ]);
    }
}
