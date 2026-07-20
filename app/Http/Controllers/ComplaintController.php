<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreComplaintRequest;
use App\Models\Complaint;
use Illuminate\Http\RedirectResponse;

class ComplaintController extends Controller
{
    public function store(StoreComplaintRequest $request): RedirectResponse
    {
        Complaint::create($request->validated());

        return redirect()
            ->to(url('/').'#complaint')
            ->with('complaint_ok', true);
    }
}
