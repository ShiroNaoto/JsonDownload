<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use Illuminate\Http\Request;
use App\Http\Resources\V1\ProjectResource;
use App\Http\Resources\V1\ProjectCollection;

use App\Models\Project;

class ProjectController extends Controller
{
    public function index(){
        $baseQuery = Project::query();
        return new ProjectCollection($baseQuery->latest()->get());
    }

    public function show(Project $project){
        return new ProjectResource($project);
    }

    public function store(){
        
    }

    public function update(){

    }

    public function destroy(){

    }
}
