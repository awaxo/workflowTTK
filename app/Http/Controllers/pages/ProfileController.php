<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\Delegation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\EmployeeRecruitment\App\Services\DelegationService;

class ProfileController extends Controller
{
    public function index()
    {
        $service = new DelegationService();
        $delegations = $service->getAllDelegations(Auth::user());
        $users = User::where('deleted', 0)->get();

        Log::info($delegations);

        return view('content.pages.profile', compact('delegations', 'users'));
    }

    public function getAllDelegations()
    {
        $delegations = Delegation::where('original_user_id', Auth::id())
                        ->where('deleted', 0)
                        ->with('delegateUser')
                        ->get()
                        ->map(function ($delegation) {
            $service = new DelegationService();
            $allDelegations = $service->getAllDelegations(Auth::user());

            $key = array_search($delegation->type, array_column($allDelegations, 'type'));
            $readable_type = $key !== false ? $allDelegations[$key]['readable_name'] : $delegation->type;

            return [
                'id' => $delegation->id,
                'readable_type' => $readable_type,
                'delegate_name' => $delegation->delegateUser->name,
                'start_date' => $delegation->start_date,
                'end_date' => $delegation->end_date,
            ];
        });

        return response()->json(['data' => $delegations]);
    }

    public function create()
    {
        $delegation = new Delegation();
        $delegation->type = request('type');
        $delegation->original_user_id = Auth::id();
        $delegation->delegate_user_id = request('delegated_user');
        $delegation->start_date = str_replace('.', '-', request('start_date'));
        $delegation->end_date = str_replace('.', '-', request('end_date'));
        $delegation->created_by = Auth::id();
        $delegation->updated_by = Auth::id();
        $delegation->save();

        return response()->json(['message' => 'Delegation added successfully']);
    }

    public function delete($id)
    {
        $delegation = Delegation::find($id);
        $delegation->deleted = 1;
        $delegation->save();
        return response()->json(['message' => 'Delegation deleted successfully']);
    }
}