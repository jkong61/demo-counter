<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\LoginSession;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\LoginProcessorService;
use Exception;

class LoginSessionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'data' => ['required','json'],
            'branch' => ['required','string','size:2']
        ]);

        // can be decoded into array
        $array = json_decode($request->data);
        try {
            $branch = Branch::getIdByName($request->branch);
            if(is_null($branch)){
                // Create new branch if is null
                $branch = new Branch();
                $branch->name = $request->branch;
                $branch->save();
                $branch->fresh();
            }

            if (LoginProcessorService::ProcessLoginRequest($array, $branch)){
                return response()->json([], 200);
            } else {
                return response()->json([], 204);
            }

        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\LoginSession  $LoginSession
     * @return \Illuminate\Http\Response
     */
    public function show(LoginSession $LoginSession)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\LoginSession  $LoginSession
     * @return \Illuminate\Http\Response
     */
    public function edit(LoginSession $LoginSession)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\LoginSession  $LoginSession
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LoginSession $LoginSession)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\LoginSession  $LoginSession
     * @return \Illuminate\Http\Response
     */
    public function destroy(LoginSession $LoginSession)
    {
        //
    }
}
