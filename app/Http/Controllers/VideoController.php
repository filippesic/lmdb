<?php

namespace App\Http\Controllers;

use App\Artist;
use App\Rate;
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
        $this->middleware('auth:api')->except(['index', 'show', 'top', 'search', 'ratingList']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = Video::with('type', 'artists', 'director', 'genres', 'seasons')
            ->leftJoin('rates', 'videos.id', '=', 'rates.video_id')
            ->select('videos.*', DB::raw('AVG(rate) as rating_avg'))
            ->groupBy('videos.id')->orderBy('rating_avg', 'desc')
            ->paginate(20);

        return response($query);
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

        $validator = Validator::make($request->all(), [
            'video_type_id' => 'required',
            'poster' => 'required|image',
            'name' => 'required|min:3',
            'genres' => 'required|exists:genres,id|max:3',
            'mpaa_rating' => ['required', Rule::in('PG', 'PG-13', 'A', 'R', 'NR', 'TV-MA', 'G')],
            'duration_in_minutes' => 'required|integer',
            'release_date' => 'required|date',
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
            'episodes' => 'array|required_if:video_type_id,2',

        ])->validate();

        $request->file('poster')->storePubliclyAs('/public/videoPosters', $validator['poster'] = Str::random(40) . '.' . $request->file('poster')->guessClientExtension());

        $video = Video::create($validator);

        if (request('episodes')) {
            foreach (request('episodes') as $episode) {
                $season = $video->seasons()->create(['episodes' => $episode]);
            }
        }

        $video->artists()->sync($request->get('artists'));
        $video->genres()->sync($request->get('genres'));

        return response($video);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Video  $video
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Video $video)
    {
       // dd($video->load('seasons'));
        $collection = collect($video->rates()->pluck('rate'));

        $videoWithRel = Video::with('type', 'rates', 'artists', 'director', 'genres', 'seasons')->findOrFail($video->id);
        return response()->json([
            'id' => $video->id,
            'video_type_id' => $video->video_type_id,
            'director_id' => $video->director_id,
            'name' => $video->name,
            'poster' => $video->poster,
            'trailer' => $video->trailer,
            'rating_avg' => round($collection->avg(), 1),
            'mpaa_rating' => $video->mpaa_rating,
            'duration_in_minutes' => $video->duration_in_minutes,
            'release_date' => $video->release_date->format('d.m.Y'),
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
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Video $video
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, Video $video)
    {
        $this->authorize('update', $video);

        $validator = Validator::make($request->all(), [
            'video_type_id' => 'required',
            'poster' => 'required|image',
            'name' => 'required|min:3',
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
            'episodes' => 'array|required_if:video_type_id,2',

        ])->validate();

        $request->file('poster')->storePubliclyAs('/public/videoPosters', $validator['poster'] = Str::random(40) . '.' . $request->file('poster')->guessClientExtension());

        $video->video_type_id = $validator['video_type_id'];
        $video->poster = $validator['poster'];
        $video->name = $validator['name'];
        $video->mpaa_rating = $validator['mpaa_rating'];
        $video->duration_in_minutes = $validator['duration_in_minutes'];
        $video->country = $validator['country'];
        $video->plot = $validator['plot'];
        $video->director_id = $validator['director_id'];
        $video->trailer = $validator['trailer'];
        $video->seasons()->delete();

        $video->save();
        //dd($validator['episodes']);

        if ($video->video_type_id == 1) {
            $video->seasons()->delete();
        } else {
            if (\request('episodes')) {
                foreach ($validator['episodes'] as $episode) {
                    $video->seasons()->create(['episodes' => $episode]);
                }
            }
        }

//        if (request('episodes') && !$video->seasons()->exists()) {
//            foreach (request('episodes') as $episode) {
//                $season = $video->seasons()->create(['episodes' => $episode]); // Needs to be changed to be able to edit per season, not just add new seasons indefinitely
//            }
//        } elseif(\request('episodes') && $video->seasons()->exists()) {
//            if(\request('season_id') && ) {
//
//            }
//        } else {
//            $video->seasons()->delete();
//        }

        $video->artists()->sync($request->get('artists'));
        $video->genres()->sync($request->get('genres'));

        return response($video->load('artists', 'genres', 'seasons'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Video $video
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(Video $video)
    {
        $this->authorize('delete', $video);
        //dd(Storage::disk('local')->delete('public/videoPosters/LYApdVF6zTaxQH0NNEs6w5fftZajIi29GXSjiuuS.jpeg'));

        //dd($video->getOriginal('poster'));
        //dd(public_path() . '/storage/videoPosters/' . $video->getOriginal('poster'));
        //Storage::delete(storage_path() . '\\app\\public\\videoPosters\\' . $video->getOriginal('poster'));
        //dd(is_file(storage_path() . '/app/public/videoPosters/' . $video->getOriginal('poster')));

        //dd(Storage::delete('/public/storage/videoPosters/' . $video->getOriginal('poster')));
        //Storage::delete(public_path() . '/storage/videoPosters/' . $video->getOriginal('poster'));
        //dd(Storage::allDirectories());

        if (is_file(storage_path() . '/app/public/videoPosters/' . $video->getOriginal('poster'))) {
            Storage::delete('public/videoPosters/' . $video->getOriginal('poster'));
        } else {
            return response(['message' => 'File not deleted. It doesn\'t exist']);
        }
        $video->seasons()->delete();
        $video->delete();

        return response([
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

    public function ratingList()
    {
        //$this->authorize('viewAny', $video);

        $query = Video::leftJoin('rates', 'videos.id', '=', 'rates.video_id')
            ->select('videos.id', DB::raw('ROUND(AVG(rate), 1) as rating_avg'))
            ->groupBy('videos.id')->orderBy('rating_avg', 'desc')->get();

        return response($query);
    }

//    public function asdf() {
//        dd(request()->server('HTTP_HOST'));
//    }


}
