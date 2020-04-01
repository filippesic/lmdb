<?php

namespace App\Http\Controllers;

use App\Artist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ArtistController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api')->except(['index', 'show', 'actors', 'directors']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $artists = Artist::with('type', 'videos')->paginate(20);

        return response($artists);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param Artist $artist
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Request $request, Artist $artist)
    {
        $this->authorize('create', $artist);

        $validator = Validator::make($request->all(), [
            'artist_type_id' => 'required|numeric',
            'poster' => 'required|image',
            'name' => 'required|string|min:2',
            'surname' => 'required|string|min:2',
            'gender' => 'required|string',
            'birth_date' => 'required',
            'bio' => 'required|string',
            'country' => 'required|min:2'
        ])->validate();

        $request->file('poster')->storePubliclyAs('/public/artistsPosters', $validator['poster'] = Str::random(40) . '.' . $request->file('poster')->guessClientExtension());

        $artist = Artist::create($validator);

        return response($artist);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Artist  $artist
     * @return \Illuminate\Http\Response
     */
    public function show(Artist $artist)
    {
        $artistWithRel = Artist::with('videos', 'type')->findOrFail($artist->id);
        return response($artistWithRel);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Artist $artist
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, Artist $artist)
    {
        $this->authorize('update', $artist);

        //$input = $request->all();

        $validator = Validator::make(\request()->all(), [
            'artist_type_id' => 'required|numeric',
            'poster' => 'required|image',
            'name' => 'required|string|min:2',
            'surname' => 'required|string|min:2',
            'gender' => ['required', Rule::in(['m', 'f'])],
            'birth_date' => 'required|date',
            'country' => 'required|min:2',
        ])->validate();

        $request->file('poster')->storePubliclyAs('/public/artistsPosters', $validator['poster'] = Str::random(40) . '.' . $request->file('poster')->guessClientExtension());

        $artist->poster = $validator['poster'];
        $artist->artist_type_id = $validator['artist_type_id'];
        $artist->name = $validator['name'];
        $artist->surname = $validator['surname'];
        $artist->gender = $validator['gender'];
        $artist->birth_date = $validator['birth_date'];
        $artist->country = $validator['country'];
        $artist->save();

        return response($artist);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Artist $artist
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */

    public function destroy(Artist $artist)
    {
        $this->authorize('delete', $artist);

        $artist->delete();

        return response([
            'message' => 'Artist successfully deleted!'
        ]);
    }

    public function directors()
    {
        $directors = DB::table('artists')->select('id', 'name', 'surname')->where('artist_type_id', 2)->get();

        return response($directors);
    }

    public function actors()
    {
        $actors = DB::table('artists')->select('id', 'name', 'surname')->where('artist_type_id', 1)->get();

        return response($actors);
    }
}
