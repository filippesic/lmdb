<?php

namespace App\Http\Controllers;

use App\Artist;
use App\Rate;
use App\Rules\DirectorCheck;
use App\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use phpDocumentor\Reflection\Types\Collection;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class VideoController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api')->except(['index', 'show', 'top', 'search', 'asdf']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $dd = collect();

        $videos = Video::with('type', 'artists', 'director', 'genres', 'seasons')->get();

        $query = Video::with('type', 'artists', 'director', 'genres', 'seasons')
            ->leftJoin('rates', 'videos.id', '=', 'rates.video_id')
            ->select('videos.*', DB::raw('AVG(rate) as rating_avg'))
            ->groupBy('videos.id')->orderBy('rating_avg', 'desc')
            ->paginate(20);

//        foreach ($videos as $id => $video) {
//            $dd->push(DB::table('rates')->select('rate')->where('video_id', $video->id)->limit(20)->avg('rate'));
//            $video->rating_avg = $dd[$id];
//            //return $rates = collect($video->rates()->pluck('rate'))->all();
//        }

        //W$rates = Video::with('rates')->get()->pluck('rates.*.rates');
      //  DB::table('rates')->select('rate')->where('video_id', $video->id)->avg('rate');

        //return view('welcome', compact('videos'));
        return response($query);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Request $request, Video $video)
    {
        $this->authorize('create', $video);

        //$directors = Artist::where('artist_type_id', 2)->select('id')->get()->pluck('id');
        //return response(dd(public_path()));

        $validator = Validator::make($request->all(), [
            'video_type_id' => 'required',
            'poster' => 'required|image',
            'name' => 'required|min:3',
            //'rating' => 'required|integer', // Should be calculated in the background from the 'rates' table!
            'genres' => 'required|exists:genres,id|max:3',
            'mpaa_rating' => ['required', Rule::in('PG', 'PG-13', 'A', 'R', 'NR')],
            'duration_in_minutes' => 'required|integer',
            'release_date' => 'required',
            'country' => 'required|min:2',
            'plot' => 'required|string',
//            'director_id' => ['required', function ($attribute, $value, $fail) {
//                $directors = Artist::where('artist_type_id', 2)->select('id')->get()->pluck('id')->toArray();
//                    if (!in_array($value, $directors)) {
//                        $fail($attribute.' is not a valid director id.'); ---------- MUCH MORE DEMANDING ON THE SERVER
//                    }
//
//            },],

            'director_id' => 'required', Rule::exists('artists', 'id') // MORE OPTIMIZED VERSION
                ->where(function ($query) {
                    $query->where('artist_type_id', 2);
                }),

            'artists' => 'required|exists:artists,id|different:director_id',
            'trailer' => 'required|starts_with:https://www.youtube.com/embed/',

        ])->validate();

        //dd($request->file('poster')->guessClientExtension());
        //$posterName = request()->file('poster')->getClientOriginalName();
        //request()->poster->move(public_path('/storage/images/videoPosters'), $posterName);
        //$validator['poster'] = time() . '_' . Str::random(40) . $request->file('poster')->guessClientExtension();
        //$artists = explode(',', $request->get('artists'));
        //$validator['artists'] = $artists;
        // dd($request->file('poster'));
        //dd(public_path('storage\images\videoPosters'));

        $request->file('poster')->storePubliclyAs('/public/videoPosters', $validator['poster'] = Str::random(40) . '.' . $request->file('poster')->guessClientExtension());

        $video = Video::create($validator);

        $video->artists()->sync($request->get('artists'));
        $video->genres()->sync($request->get('genres'));

        return response($video);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Video  $video
     * @return \Illuminate\Http\Response
     */
    public function show(Video $video)
    {

        //dd(request()->server('SERVER_ADDR'));
        $collection = collect($video->rates()->pluck('rate'));

        $videoWithRel = Video::with('type', 'rates', 'artists', 'director', 'genres', 'seasons')->findOrFail($video->id);
        return response([
            'id' => $video->id,
            'video_type_id' => $video->video_type_id,
            'director_id JEL TI TREBA ZNACI NE?' => $video->director_id,
            'name' => $video->name,
            'poster' => $video->poster,
            'trailer' => $video->trailer,
            'rating_avg' => round($collection->avg(), 1),
            'mpaa_rating' => $video->mpaa_rating,
            'duration_in_minutes' => $video->duration_in_minutes,
            'release_date' => $video->release_date,
            'country' => $video->country,
            'plot' => $video->plot,
            'type' => $video->type()->get(),
            'artists' => $video->artists()->get(),
            'director' => $video->director()->get(),
            'genres' => $video->genres()->get(),
            'seasons' => $video->seasons()->get(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Video  $video
     * @return \Illuminate\Http\Response
     */
    public function edit(Video $video)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Video $video
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, Video $video)
    {
        $this->authorize('create', $video);
        //dd($request->all());
        $validator = Validator::make($request->all(), [
            'video_type_id' => 'required',
            'poster' => 'required|image',
            'name' => 'required|min:3',
            //'rating' => 'required|integer', // Should be calculated in the background from the 'rates' table!
            'genres' => 'required|exists:genres,id|max:3',
            'mpaa_rating' => ['required', Rule::in('PG', 'PG-13', 'A', 'R', 'NR')],
            'duration_in_minutes' => 'required|integer',
            'release_date' => 'required',
            'country' => 'required|min:2',
            'plot' => 'required|string',
//            'director_id' => ['required', function ($attribute, $value, $fail) {
//                $directors = Artist::where('artist_type_id', 2)->select('id')->get()->pluck('id')->toArray();
//                    if (!in_array($value, $directors)) {
//                        $fail($attribute.' is not a valid director id.'); ---------- MUCH MORE DEMANDING ON THE SERVER
//                    }
//
//            },],

            'director_id' => 'required', Rule::exists('artists', 'id') // MORE OPTIMIZED VERSION
                ->where(function ($query) {
                $query->where('artist_type_id', 2);
            }),

            'artists' => 'required|exists:artists,id|different:director_id',
            'trailer' => 'required',

        ])->validate();

        //dd('not here right?');

        $video->video_type_id = $validator['video_type_id'];
        $video->poster = $request->file('poster')->storePubliclyAs('/public/videoPosters', $validator['poster'] = Str::random(40) . '.' . $request->file('poster')->guessClientExtension());
        $video->name = $validator['name'];
        $video->mpaa_rating = $validator['mpaa_rating'];
        $video->duration_in_minutes = $validator['duration_in_minutes'];
        $video->country = $validator['country'];
        $video->plot = $validator['plot'];
        $video->director_id = $validator['director_id'];
//        if($request->get('rate') !== null) {
//            $video->rates()->attach(327, ['rate' => $request->get('rate')]);
//            $video->rating = DB::table('rates')->select('rate')->where('video_id', $video->id)->avg('rate');
//            $video->update(['rating' => avg()])
//            //dd('here mate?');
//        }

        $video->save();

        return response($video);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Video  $video
     * @return \Illuminate\Http\Response
     */
    public function destroy(Video $video)
    {
        $video->delete();

        return response([
            'status' => 'success',
            'message' => 'Video successfully deleted!'
        ]);
    }

    public function top()
    {
        $query = Video::with('type', 'artists', 'director', 'genres', 'seasons')
            ->leftJoin('rates', 'videos.id', '=', 'rates.video_id')
            ->select('videos.*', DB::raw('ROUND(AVG(rate), 1) as rating_avg'))
            ->groupBy('videos.id')->orderBy('rating_avg', 'desc')
            ->paginate(20);

        return response($query);
    }

    public function search(Request $request)
    {
        if ($request->get('query')) {
            $query = $request->get('query');

            $videos = Video::where('name', 'LIKE', '%' . $query . '%')->paginate(20);
            $videos->appends(['query' => $query]);

            return response($videos);
        } else {
            return response(['message' => 'No search query mate']);
        }
    }

    public function average(Video $video)
    {
        $ratesNew = [];
//        foreach ($video->rates as $rate) {
//            return response($rate);
//
//        }

        $collection = collect($video->rates()->pluck('rate'));
            return response([
          'average' => $collection->avg()
        ]);
    }

    public function averageAll() {
        //  DB::table('rates')->select('rate')->where('video_id', $video->id)->avg('rate');

        $rates = Video::with('rates')->get()->pluck('rates.*.rates')->flatten();        // video_id, user_id, rate
        $videos = Video::with('type', 'artists', 'director', 'genres', 'seasons', 'rates')->get();


        $dd = collect();
        foreach ($videos as $id => $video) {
            $dd->push(DB::table('rates')->select('rate')->where('video_id', $video->id)->avg('rate'));

            //return $rates = collect($video->rates()->pluck('rate'))->all();
        }
        return response()->json($dd);
        //return response($rates2);


        //return view('welcome', compact('videos'));
        //return response($rates[0]->video_id);
       // return response($videos);
    }

    public function asdf() {
        //dd(request()->server('HTTP_HOST'));
    }
}
