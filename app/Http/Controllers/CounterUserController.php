<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\CounterUser;
use App\Services\CounterUserProcessorService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class CounterUserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $validated = $request->validate([
            'counterusername' => 'nullable|max:255',
            'identifier' => 'nullable|numeric|max:255',
            'ordering' => 'nullable|boolean',
        ]);

        $counter_user_query = CounterUser::where('is_active','=',1);

        if(isset($validated['ordering']) && $validated['ordering']) {
            $counter_user_query = $counter_user_query->orderBy('id','desc');
        } else {
            $counter_user_query = $counter_user_query->orderBy('id','asc');
        }

        if(isset($validated['identifier'])){
            $counter_user_query = $counter_user_query->where('id','like', "%".$validated['identifier']."%");
        }

        if(isset($validated['counterusername'])){
            $counter_user_query = $counter_user_query->where('name','like', "%".$validated['counterusername']."%");
        }

        $counter_users = $counter_user_query->paginate(10);
        return view('counteruser.index-counteruser', [
            'counter_users' => $counter_users
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('counteruser.create-counteruser');
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
            'data' => 'required|json',
            'branch' => 'required|string|size:2'
        ]);

        $raw_user_array = json_decode($request->data);
        try {
            $branch = Branch::getIdByName($request->branch);
            if(is_null($branch)){
                // Create new branch if is null
                $branch = new Branch();
                $branch->name = $request->branch;
                $branch->save();
                $branch->fresh();
            }

            return CounterUserProcessorService::ProcessCounterUserRequest($raw_user_array, $branch) 
            ? response()->json(['message' => Response::HTTP_OK], 200)
            : response()->json(['message' => Response::HTTP_BAD_REQUEST], 400);
        } catch (Exception $e) {
            Log::error($e);
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CounterUser  $counterUser
     * @return \Illuminate\Http\Response
     */
    public function show(CounterUser $counterUser)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CounterUser  $counterUser
     * @return \Illuminate\Http\Response
     */
    public function edit(CounterUser $counterUser)
    {
        //
        // return view('counteruser.edit-counteruser', [
        //     'counteruser' => $counterUser
        // ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CounterUser  $counterUser
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CounterUser $counterUser)
    {
        //
        // $request->validate([
        //     'counteruseridentifier' => 'prohibited',
        //     'counterusername' => 'required|min:2|max:255',
        //     'counteruserdisplay' => 'required|min:2|max:32',
        //     'counteruserposition' => 'nullable|min:2|max:255'
        // ]);

        // $counterUser->name = $request->input('counterusername');
        // $counterUser->sname = $request->input('counteruserdisplay');
        // $counterUser->position = $request->input('counteruserposition') ?? null;

        // $counterUser->save();
        // return redirect()->route('counteruser.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CounterUser  $counterUser
     * @return \Illuminate\Http\Response
     */
    public function destroy(CounterUser $counterUser)
    {
        //
        // $counterUser->is_active = false;
        // $counterUser->save();

        // return redirect()->route('counteruser.index');
    }
}
