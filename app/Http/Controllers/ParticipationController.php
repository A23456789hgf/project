<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\Participation;
use Illuminate\Http\Request;

class ParticipationController extends Controller
{
    // GET /participation
    public function index()
    {
        $this->authorize('viewAny', Participation::class);
        $participants = Participant::orderBy('id', 'desc')->get();

        return view('configuration.participation.index', compact('participants'));
    }

    // GET /participation/create
    public function create()
    {
        $this->authorize('create', Participation::class);

        return view('configuration.participation.create');
    }

    // POST /participation
    public function store(Request $request)
    {
        $this->authorize('create', Participation::class);
        $request->validate([
            'name' => 'required|unique:participants,name',
        ]);

        Participant::create([
            'name' => $request->name,
            'is_active' => true,
        ]);

        return redirect()->route('participation.index')->with('success', 'تمت الإضافة بنجاح');
    }

    // GET /participation/{id}/edit
    public function edit(Participant $participant)
    {
        $this->authorize('update', Participation::class);

        return view('configuration.participation.edit', compact('participant'));
    }

    // PUT /participation/{id}
    public function update(Request $request, Participant $participant)
    {
        $this->authorize('update', Participation::class);
        $request->validate([
            'name' => 'required|unique:participants,name,'.$participant->id,
            'is_active' => 'required|boolean',
        ]);

        $participant->update($request->only('name', 'is_active'));

        return redirect()->route('participation.index')->with('success', 'تم التحديث بنجاح');
    }

    // PATCH /participation/{id} (تحديث جزئي، يمكن استخدام update بنفس الطريقة)
    public function patchUpdate(Request $request, Participant $participant)
    {
        $participant->update($request->only('name', 'is_active'));

        return redirect()->route('participation.index')->with('success', 'تم التحديث الجزئي بنجاح');
    }

    // DELETE /participation/{id}
    public function destroy(Participant $participant)
    {
        $this->authorize('delete', Participation::class);
        $participant->delete();

        return redirect()->route('participation.index')->with('success', 'تم الحذف بنجاح');
    }
}
