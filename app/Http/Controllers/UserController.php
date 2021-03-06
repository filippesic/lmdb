<?php

namespace App\Http\Controllers;

use App\User;
use App\Video;
use Dotenv\Validator;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'required|min:3',
            'surname' => 'nullable',
            'email' => 'required',
            'password' => 'required|min:4',
        ])->validate();

        $user = User::create([
            'name' => $validator['name'],
            'surname' => $validator['surname'],
            'email' => $validator['email'],
            'password' => Hash::make($validator['password'])
        ]);

        return response($user);

    }

    /**
     * Display the specified resource.
     *
     * @param \App\User $user
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show()
    {
        //$this->authorize('view', $user);

        $query = auth()->user()->watchlist->pluck('id');
        $videos = Video::with('type', 'artists', 'director', 'genres', 'seasons')
            ->leftJoin('rates', 'videos.id', '=', 'rates.video_id')
            ->select('videos.*', DB::raw('AVG(rate) as rating_avg'))->whereIn('videos.id', $query)
            ->groupBy('videos.id')->orderBy(request('sort') ?? 'created_at', request('dir') ?? 'desc')->get();

        $user = \auth()->user();
        $user->watchlist = $videos;

        return response($user);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\User $user
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'required|string|min:3',
            'surname' => 'sometimes|string',
            'email' => 'required|email',
            'currentPassword' => 'required_with:newPassword|min:2|password:api',
            'newPassword' => 'required_with:currentPassword|min:3|confirmed'
//            'newPassword' => Rule::requiredIf(function () use ($request) {
//                if(!empty($request->currentPassword) && !is_null($request->currentPassword)) {
//                    return true;
//                } else {
//                    return response(['message' => 'New password is finally required mate, no open doors anymore hesuuus']);
//                }
//            }),
        ])->validate();

        //dd((Hash::check(request('currentPassword'), auth()->user()->password) ?: 'nope'));

        $user->name = request('name');
        $user->surname = request('surname');
        $user->email = request('email');

        if (\request('newPassword')) {
            $user->password = Hash::make(\request('newPassword'));
        }

        $user->save();

        if (auth()->user()->role->id === 2) {
            return response(['message' => 'Profile edited by administrator mate', 'user' => $user]);
        }

        return response(['message' => 'Successfully edited a profile mate!', 'user' => $user]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\User $user
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        $user->tokens->each(function ($token, $key) {
            $token->delete();
        });

        $user->delete();

        if (auth()->user()->role->id === 2) {
            return response(['message' => 'Profile deleted by administrator user m8!']);
        }
        return response(['message' => 'Successfully deleted your profile']);
    }

    public function rate()
    {
        \request()->validate([
            'video_id' => 'required|integer|nullable',
            'rate' => 'required|integer|nullable'
        ]);
        if (auth()->user()->rated->pluck('rated')->contains('video_id', request('video_id'))) {
            //dd('exists');
            auth()->user()->rated()->updateExistingPivot(\request('video_id'), ['rate' => request('rate')]);
            return response(['message' => 'You updated a movie']);
        } else {
            auth()->user()->rated()->attach(\request('video_id'), ['rate' => \request('rate')]);
            return response(['message' => 'You rated a movie']);
        }
    }

    public function unrate()
    {
        \request()->validate([
            'video_id' => 'required|integer',
        ]);

        try {
            auth()->user()->rated()->detach(\request('video_id'));
        } catch (QueryException $e) {
            if ($e->errorInfo[1] == 1062) {
                return response(['message' => 'You already rated the damn movie mate!']);
            }
        }

        return response(['message' => 'You removed the rating from this movie mate!']);
    }

    public function rates()
    {
        $rates = auth()->user()->rated->pluck('rated');
        return response($rates);
    }

    public function rates2()
    {
        \request()->validate([
            'video_id' => 'required|integer'
        ]);

        if (auth()->user()->rated->pluck('rated')->contains('video_id', \request('video_id'))) {
            return response(auth()->user()->rated->pluck('rated')->where('video_id', \request('video_id'))->flatten());
        } else {
            return response(['message' => 'No rates found for that movie mate!'], 404);
        }
    }

    public function addToList()
    {
        \request()->validate([
            'video_id' => 'required|integer|exists:videos,id'
        ]);

        $message = auth()->user()->watchlist()->toggle(\request('video_id'));

        if ($message['attached'] == null) {
            return response(['message' => 'You deleted a movie from watchlist mate'], 200);
        } else {
            return response(['message' => 'You added a movie to your watchlist mate'], 201);
        }

    }

    public function watchlistId()
    {
//        return response(auth()->user()->load('watchlist'));
        return response()->json([
            //'user' => auth()->user(),
            'watchlist' => auth()->user()->watchlist->pluck('id')
        ]);
    }

    public function list2()
    {
        $ids = auth()->user()->watchlist->pluck('id');
        //dd(auth()->user()->watchlist);
        //$this->authorize('viewAny', $video);
        $query = Video::with('type', 'artists', 'director', 'genres', 'seasons')
            ->leftJoin('rates', 'videos.id', '=', 'rates.video_id')
            ->select('videos.*', DB::raw('ROUND(AVG(rate), 1) as rating_avg'))->whereIn('videos.id', $ids)
            ->groupBy('videos.id')->orderBy('rating_avg', 'desc')->get();

        return response($query);
    }

}
