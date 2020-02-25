<?php

namespace App\Http\Controllers;

use App\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GenreController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api')->only(['store', 'update', 'destroy']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $genresWithRel = Genre::with('videos')->get();

        return response($genresWithRel);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param Genre $genre
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Request $request, Genre $genre)
    {
        $this->authorize('create', $genre);

        $validated = array_unique($request->validate([
           'name' => 'required|array|unique:genres,name'
        ]));

        foreach (array_unique($validated['name']) as $genre) {
            Genre::create(['name' => $genre]);
        }

        return response(['message' => 'Genre successfully created mate']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Genre  $genre
     * @return \Illuminate\Http\Response
     */
    public function show(Genre $genre)
    {
        $genreWithRel = Genre::with('videos')->find($genre->id);

        return response($genreWithRel);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Genre $genre
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, Genre $genre)
    {
        $this->authorize('update', $genre);

        $request->validate([
            'name' => 'required|string'
        ]);

        $genre->name = $request->get('name');
        $genre->save();

        return  response(['message' => 'You edited the genre successfully mate!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Genre $genre
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(Genre $genre)
    {
        $this->authorize('delete', $genre);
        
        $genre->delete();

        return response(['message' => 'Genre successfully deleted mate']);
    }
}
