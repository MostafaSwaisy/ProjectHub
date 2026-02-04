<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Constructor to set up authorization middleware
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Will be implemented in T013
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Will be implemented in T040
    }

    /**
     * Archive a project
     */
    public function archive(Project $project)
    {
        // Will be implemented in T048
    }

    /**
     * Unarchive a project
     */
    public function unarchive(Project $project)
    {
        // Will be implemented in T049
    }

    /**
     * Duplicate a project
     */
    public function duplicate(Request $request, Project $project)
    {
        // Will be implemented in T084
    }

    /**
     * List project members
     */
    public function members(Project $project)
    {
        // Will be implemented in T072
    }

    /**
     * Add a member to the project
     */
    public function addMember(Request $request, Project $project)
    {
        // Will be implemented in T073
    }

    /**
     * Update a member's role
     */
    public function updateMember(Request $request, Project $project, $userId)
    {
        // Will be implemented in T074
    }

    /**
     * Remove a member from the project
     */
    public function removeMember(Project $project, $userId)
    {
        // Will be implemented in T075
    }
}
