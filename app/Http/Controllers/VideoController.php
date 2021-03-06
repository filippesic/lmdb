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
use function foo\func;

class VideoController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api')->except(['index', 'show', 'top', 'search', 'ratingList', 'asdf']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // return response(\request()->all());
        request()->validate([
            'offset' => 'sometimes|integer',
            'limit' => 'sometimes|integer',
            'artist' => 'sometimes|string|min:3', //Rule::in('name', 'rating_avg', 'release_date', 'country', 'created_at')],
            'genre' => 'sometimes|string|min:3', //Rule::in('name', 'rating_avg', 'release_date', 'country', 'created_at')],
            'ord' => ['sometimes', 'string', Rule::in('name', 'rating_avg', 'release_date', 'country', 'created_at')],
            'dir' => ['sometimes', 'string', Rule::in('asc', 'desc')],
        ]);

        $total = Video::all()->count();

        $queryGenre = Video::whereHas('genres', function($q){
            $q->where('genres.name', 'like', '%'. \request('genre') . '%')->orderBy('genres.name', 'desc');
        })->with('genres');

        $totalGenre = $queryGenre->count();

        //dd($queryGenre->count());

        $queryGenreLimit = $queryGenre->leftJoin('rates', 'videos.id', '=', 'rates.video_id')
            ->select(DB::raw('videos.*, AVG(rate) as rating_avg'))
            ->groupBy('videos.id')
            ->orderBy(\request('ord') ?? 'videos.created_at', \request('dir') ?? 'asc')
            ->skip(request('offset') ?? 0)
            ->take(request('limit') ?? 5)
            ->get();
       // dd($queryGenreLimit);

        $queryArtists = Video::whereHas('artists', function($q){
            $q->where('artists.name', 'like', '%'. \request('artist'). '%');
        })->with('artists');

        $totalArtists = $queryArtists->count();

        $queryArtistsLimit = $queryArtists->leftJoin('rates', 'videos.id', '=', 'rates.video_id')
            ->select(DB::raw('videos.*, AVG(rate) as rating_avg'))
            ->groupBy('videos.id')->orderBy(\request('ord') ?? 'videos.created_at', \request('dir') ?? 'asc')
            ->skip(request('offset') ?? 0)
            ->take(request('limit') ?? 5)
            ->get();
        //dd($queryArtistsLimit);

        if (request('genre')) {
            return response(['videos' => $queryGenreLimit, 'total' => $totalGenre]);
        } elseif (request('artist')) {
            return response(['videos' => $queryArtistsLimit, 'total' => $totalArtists]);
        } else {
            $query = Video::with('type', 'artists', 'director', 'genres', 'seasons')
                ->leftJoin('rates', 'videos.id', '=', 'rates.video_id')
                ->select('videos.*', DB::raw('AVG(rate) as rating_avg'))
                ->groupBy('videos.id')->orderBy(request('ord') ?? 'created_at', request('dir') ?? 'asc')
                ->skip(request('offset') ?? 0)
                ->take(request('limit') ?? 5)
                ->get();

            return response(['videos' => $query, 'total' => $total]);
        }

//        $queryGenre = Video::with('type', 'artists', 'director', 'genres', 'seasons')
//            ->join('genre_video', 'videos.id', '=', 'genre_video.video_id')
//            ->join('genres', 'genres.id', '=', 'genre_video.genre_id')
//            ->select('*')->where('genres.name', 'LIKE', '%Drama%')->orderBy('videos.created_at', 'desc')->get();

//        $queryArtists = Video::with('type', 'artists', 'director', 'genres', 'seasons')
//            ->join('artist_video', 'videos.id', '=', 'artist_video.video_id')
//            ->join('artists', 'artists.id', '=', 'artist_video.artist_id')
//            ->select('*')->orderBy('artists.name', 'desc')->get();

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param Video $video
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
     * @param \App\Video $video
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
        }
//        } else {
//            return response(['message' => 'File not deleted. It doesn\'t exist']);
//        }
        
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
            ->groupBy('videos.id')->orderBy('rating_avg', 'desc')->take(100)->get();

        return response($query);
    }

    public function search()
    {
        $validator = request()->validate([
            'query' => 'required|string|min:2',
            'offset' => 'required|numeric',
            'limit' => 'required|numeric'
        ]);

        $query = $validator['query'];
        $raw = DB::table('videos')->where('name', 'LIKE', '%' . $query . '%');
        $total = $raw->count();
        $videos = $raw->skip($validator['offset'])->take($validator['limit'])->get();

        return response(['videos' => $videos, 'total' => $total]);


    }

    public function ratingList()
    {
        //$this->authorize('viewAny', $video);

        $query = Video::leftJoin('rates', 'videos.id', '=', 'rates.video_id')
            ->select('videos.id', DB::raw('ROUND(AVG(rate), 1) as rating_avg'))
            ->groupBy('videos.id')->orderBy('rating_avg', 'desc')->get();

        return response($query);
    }

    // IGNORE, CODILITY PRACTISE

    public function asdf()
    {
        //dd(request()->server('HTTP_HOST'));
        function binaryGap($n)
        {
            $array = [];
            $dec = decbin($n);
            $counter = 0;

            for ($i = 0; $i < strlen($dec); $i++) {
                $array[] = $dec[$i];
            }
            echo 'decbin: ' . $dec;
            echo "<hr>";

            echo 'array from decbin: ';
            foreach ($array as $letter) {
                echo $letter;
            }
            echo "<hr>";

            for ($i = 0; $i < count($array); $i++) {
                //echo $array[$i] . "<br>";
                if ($array[$i] == 1) {
                    if (next($array) == 0) {
                        $counter++;
                    }
                }
            }

            echo $counter;
        }

//       binaryGap(1041);

        function binaryGap2(int $n)
        {
            $binaryString = decbin($n);
            $gap = 0;

            for ($i = 0; $i < strlen($binaryString); $i++) {
                switch ($binaryString[$i]) {
                    case 1:
                        $gap++;
                        break;
                    case 0:
                        break;
                }
            }
            echo 'binaryString: ' . $binaryString . "<br>";;
            echo 'gap: ' . $gap . "<br>";
        }

        //binaryGap2(1041);

        function binaryGap3(int $n)
        {
            echo "<div style=text-align:center;>";

            $binaryString = decbin($n);
            echo 'Binary string: ' . $binaryString . "<br>";

            $i = 0;
            $maxGap = 0;

            while ($binaryString[$i] != 1) {
                $i++;
            }

            echo '$i after first zeros loop check: ' . $i . "<br>";

            while ($i < strlen($binaryString)) {

                while ($binaryString[$i] == 1) {
                    $i++;
                }
                echo '$i after finding first one: ' . $i . "<br>";

                $tempGap = 0;

                while (strlen($binaryString) > $i && $binaryString[$i] == 0) {
                    $tempGap++;
                    $i++;
                }
                echo '$i after counting all the zeroes: ' . $i . "<br>";

                if (strlen($binaryString) > $i && $binaryString[$i] == 1 && $tempGap > $maxGap) {
                    $maxGap = $tempGap;
                }

                echo '$i after if check: ' . $i . "<br>";
                return $maxGap;
            }
            echo "</div>";
        }

//        $res = binaryGap3(650000);
//
//        echo "<br>" . "Result: " . $res;

        function arrayRotate(array $arr, int $rot)
        {

            $rotated = [];

            for ($i = 0; $i < $rot; $i++) {
                $lastElem = array_pop($arr);
                $newArray = array_unshift($arr, $lastElem);
            }

            return $arr;

        }

        $arrayExample = [1, 2, 3, 4];

        $result = arrayRotate($arrayExample, 4);
        //dd($result);

        ///////////////////////////////////////////////////////////////////////////////

        function oddOccurrencesInArray($A)
        {
            rsort($A);
            $count = array_count_values($A);
            $theOne = 0;
            $min = 0;

            foreach ($count as $value => $occurrence) {
                if ($occurrence == 1) {
                    $theOne = $value;
                }
            }

            return $theOne;

        }

        $ex = [9, 3, 9, 3, 9, 7, 9];
        $rez = oddOccurrencesInArray($ex);
        // dd($rez);


        function frogJmp(int $x, int $y, int $d)
        {
            $distance = $y - $x;
            $steps = (int)ceil($distance / $d);

            if ($x >= $y) {
                return false;
            } else {
                return $steps;
            }
        }

        $res = frogJmp(10, 85, 30);
        //dd($res);


        function permMissingElem(array $a)
        {
            sort($a);
            $goodArray = [];
            if (empty($a) || in_array(null, $a)) {
                return 0;
            } elseif (count($a) == 1) {
                return array_pop($a);
            } elseif (count($a) == 2 && $a[0] == $a[1]) {
                return array_pop($a);
            } else {
                for ($i = 1; $i <= count($a); $i++) {
                    $goodArray[] = $i;
                }
            }
            $arr = array_diff($goodArray, $a);
            return array_pop($arr);
        }

        $exampl = [2, 3, 5, 1];
        $res = permMissingElem($exampl);
        //dd($res);


        function frogRiverOne(int $x, array $a)
        {
            $keysTime = array_keys($a);
            $lowestTime = 0;

            if (!in_array($x, $keysTime) || count(array_unique($a)) == 1) {
                return -1;
            } else {
                foreach ($a as $time => $position) {
                    if ($position == $x) {
                        $lowestTime = $time;
                    }
                }
            }
            return $lowestTime;

        }

        $example = [4];

        $res2 = frogRiverOne(5, $example);
        dd($res2);

    }


}
