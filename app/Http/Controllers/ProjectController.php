<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ProjectController extends Controller
{

    use ApiResponseTrait;
    /**
    * Display a listing of the resource.
    *
    * @OA\Get(
    *     path="/api/project",
    *     summary="Liste tous les projets",
    *     operationId="getprojects",
    *     tags={"Projects"},
    *     @OA\Response(
    *         response=200,
    *         description="Liste des projects",
    *         @OA\Schema(
    *             type="array",
    *             @OA\Items(ref="#/definitions/Item")
    *         ),
    *     ),
    * )
     */
    public function index()
    {
//----------------------------------------------------------------------------------------------
        //Recuperer tous les projets au dela d'aujourd'hui
        // $projects = Project::whereDate('start_date', '=', '2023-11-11')->get();

        //Recuperer tous les projets inferieur a la date d'aujourd'hui
        // $projects = Project::whereDate('end_date', '=', '2023-11-11')->get();

        //Recuperer tous les projets compris entre la date ... et ...
        // $projects = Project::whereBetween('start_date', ['2023-11-11', '2023-12-11'])
        //                     ->orWhereBetween('end_date', ['2023-11-11', '2023-12-11'])->get();
//-------------------------------------------------------------------------------------------------

        //Les projets les plus recents
        // $projects = Project::orderBy('name', 'ASC')->orderBy('rate', 'DESC')->get();

        //Pagination
        // $projects = Project::paginate(3);

        //En fonction de l'utilisateurs actuellement connecte

        // return ProjectResource::collection(Project::where('user_id', Auth::user()->id)->get());
        return response()->json(Project::all());
    }

    /**
     * Store a newly created resource in storage.
     * @OA\Post(
     *     path="/api/store",
     *     summary="Enregistrer un nouveau projet",
     *
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Le nom du projet",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="description",
     *         in="query",
     *         description="La description du projet",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="La date de debut du projet",
     *         required=false,
     *         @OA\Schema(type="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="La date de fin du projet",
     *         required=false,
     *         @OA\Schema(type="date")
     *     ),
     *     @OA\Parameter(
     *         name="rate",
     *         in="query",
     *         description="La note du projet",
     *         required=false,
     *         @OA\Schema(type="date")
     *     ),
     *     @OA\Response(response="201", description="Projet enregistre avec succes"),
     *     @OA\Response(response="422", description="Validation errors")
     * )
     */
    public function store(StoreProjectRequest $request)
    {
        $request->validated($request->all());

        $image = $request->image;

        if($image != null && !$image->getError()){
            $image = $request->image->store('asset', 'public');
        }

        $project = Project::create([
            'name' => $request->name,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'rate' => $request->rate,
            'user_id' => Auth::user()->id,
            'image' => $image
        ]);

        return $this->successResponse($project, 'Projet Cree avec succes');

    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        // if(!Gate::allows('access', $project)){
        //     return $this->unauthorizedResponse("Vous n'etes pas autorise a acceder");
        // }

        if(Auth::user()->cannot('view', $project)){
            return $this->unauthorizedResponse("Vous n'etes pas autorise a acceder");
        }

        return new ProjectResource($project);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        if(!Gate::allows('access', $project)){
            return $this->unauthorizedResponse("Vous n'etes pas autorise a acceder");
        }

        $request->validated($request->all());

        $project->update($request->all());

        return $this->successResponse($project, 'Projet modifie avec succes');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {

        if(!Gate::allows('access', $project)){
            return $this->unauthorizedResponse("Vous n'etes pas autorise a acceder");
        }

        $project->delete();

        return response()->json([
            'success' => true,
            'message' => 'Project supprime avec succes'
        ]);
    }

    public function search(Request $request){
        $keyword = $request->input('keyword');

        $projects = Project::where('name', 'like', "%$keyword%")->get();

        return response()->json($projects);

    }
    
}
