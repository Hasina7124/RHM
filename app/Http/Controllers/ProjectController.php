<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Jenssegers\Mongodb\Eloquent\Model as Eloquent; // Assurez-vous que c'est cette ligne

class ProjectController extends Eloquent
{
    // Liste tous les projets accessibles à l’utilisateur
    public function index()
    {
        $userId = Auth::id();
        $projects = Project::where('owner_id', $userId)
            ->orWhere('administrators', $userId)
            ->orWhere('teams.members', $userId)
            ->get();

        return response()->json($projects);
    }

    // Créer un nouveau projet
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'status' => 'required|in:Actif,Inactif,Archivé',
            // 'owner_id' =>'nullable',
            'administrators' => 'nullable|array',
            'administrators.*' => 'string|exists:users,_id',
            'teams' => 'nullable|array',
            'teams.*.name' => 'required|string|max:255',
            'teams.*.members' => 'nullable|array',
            'teams.*.members.*' => 'string|exists:users,_id',
        ]);

        \Log::info('Owner ID:', ['owner_id' => $validatedData]);

        $validatedData['owner_id'] = Auth::id();

        $project = Project::create($validatedData);

        return response()->json(['message' => 'Project created successfully'], 201);
    }

    // Afficher un projet spécifique
    public function show($id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        if (!$this->userHasAccess($project)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($project);
    }

    // Modifier un projet existant
    public function updateProject(Request $request, $id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        if (Auth::id() !== $project->owner_id && !in_array(Auth::id(), $project->administrators)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:1000',
            'status' => 'sometimes|in:Actif,Inactif,Archivé',
            'administrators' => 'nullable|array',
            'administrators.*' => 'string|exists:users,_id',
            'teams' => 'nullable|array',
            'teams.*.name' => 'required|string|max:255',
            'teams.*.members' => 'nullable|array',
            'teams.*.members.*' => 'string|exists:users,_id',
        ]);

        $project->update($validatedData);

        return response()->json(['message' => 'Project updated successfully', 'project' => $project]);
    }

    // Supprimer un projet
    public function destroyProject($id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        if (Auth::id() !== $project->owner_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $project->delete();

        return response()->json(['message' => 'Project deleted successfully']);
    }

    // Vérifie si l'utilisateur a accès au projet
    private function userHasAccess($project)
    {
        $userId = Auth::id();
        return $userId === $project->owner_id || 
               in_array($userId, $project->administrators) ||
               collect($project->teams)->pluck('members')->flatten()->contains($userId);
    }
}
