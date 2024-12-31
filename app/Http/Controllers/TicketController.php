<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class TicketController extends Controller
{
    public function index($project_id)
    {
        $userId = Auth::id();
        $tickets = Ticket::where('project_id', $project_id)->get();

        return response()->json($tickets);
    }

    // Créer un nouveau ticket
    public function store(Request $request, $project_id)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'status' => 'required|in:Actif,Inactif,Archivé',
            // 'project_id' => 'nullable',
            // 'owner_id' =>'nullable',
            'estimate_date' => 'required|date|after:today',
            'team_assigned' => 'string',

        ]);

        $validatedData['owner_id'] = Auth::id();
        $validatedData['project_id'] = $idProject;

        $ticket = Ticket::create($validatedData);

        return response()->json(['message' => 'Ticket created successfully'], 201);
    }

    public function updateTicket(Request $request, $id){
        $ticket = Ticket::find($id);

        if(!$ticket){
            return response()->json(['error'=>'Ticket not found'],404);
        }

        if(Auth::id()!==$ticket->owner_id){
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validatedData = $request->validate([
            'title' => 'string|max:255',
            'description' => 'string|max:1000',
            'status' => 'in:Actif,Inactif,Archivé',
            // 'project_id' => 'nullable',
            // 'owner_id' =>'nullable',
            'estimate_date' => 'date|after:today',
            'team_assigned' => 'string',

        ]);

        $ticket->update($validatedData);
        return response()->json(['message' => 'Ticket updated successfully', 'ticket' => $ticket]);
    }

    public function destroyTicket($id){
        $ticket = Ticket::find($id);
        $project = Project::where('_id', $ticket->project_id)->first();


        if(!$ticket){
            return response()->json(['error'=>'Ticket not found'],404);
        }

        \Log::info('Project data', ['$project'=>Auth::id()]);
        if(Auth::id() == $ticket->owner_id && in_array(Auth::id(), $project->administrators));{
            $ticket->delete();
            return response()->json(['message' => 'Project deleted successfully']);
        }
        return response()->json(['error' => 'Unauthorized'], 403);
    }
}
