<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Services\SalesReceiptService;
use Exception;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    //
    public function index(Request $request)
    {

    }

    public function store(Request $request, SalesReceiptService $service)
    {

        $request->validate([
            'data' => ['required','json'],
            'branch' => ['required','exists:branches,name','size:2']
        ]);

        $branch = Branch::getIdByName($request->get('branch'));

        try 
        {
            if($service->handleSalesReceipts(json_decode($request->get('data')), $branch))
            {
                return response()->json(['message' => "ok"], 200);
            }
            return response()->json(['message' => "notok"], 400);
        } 
        catch (Exception $ex)
        {
            // throw $ex;
            return response()->json(['message' => "notok"], 422);
        }
    }
}
