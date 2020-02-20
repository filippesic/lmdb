<?php

namespace App\Http\Controllers;

use App\User;
use App\Video;
use Dotenv\Validator;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::with('rated')->paginate('20');

        return response($users);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(),[
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
        //$rates = DB::table('rates')->select('rate')->where('video_id', 5)->avg('rate');

        return response($user);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\User  $user
     * @return // \Illuminate\Http\Response
     */
    public function show()
    {
        //$user = auth()->user()->with('rated')->get()->pluck('rated.*.rated')->flatten();
        return response(auth()->user());
       //return response(auth()->user());

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

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(),[
            'name' => 'required|min:3',
            'surname' => 'nullable',
            'email' => 'required',
            'password' => 'required|min:3',
        ])->validate();

        $user->name = \request('name');
        $user->surname = \request('surname');
        $user->email = \request('email');
        $user->password = \request('password');
        $user->save();

        if (auth()->user()->role->id === 2) {
            return response(['message' => 'Profile edited by administrator user mate']);
        }

        return response(['message' => 'Successfully edited a profile mate!' , $user]);
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

        if(auth()->user()->role->id === 2) {
            return response(['message' => 'Profile delete by administrator user m8!']);
        }
        return response(['message' =>'Successfully deleted your profile']);
    }

    public function rate(Video $video)
    {
        \request()->validate([
           'video_id' => 'required|integer',
           'rate' => 'required|integer'
        ]);
        //dd('here or not?');

        try {
//            if(!auth()->user()->rated()->attach(\request('video_id'), ['rate' => \request('rate')])) {
//
//                auth()->user()->rated()->detach(\request('video_id'));
//                auth()->user()->rated()->attach(\request('video_id'), ['rate' => \request('rate')]);
//            }

            auth()->user()->rated()->attach(\request('video_id'), ['rate' => \request('rate')]);

        } catch (QueryException $e) {
            if ($e->errorInfo[1] == 1062) {
                return response(['message' => 'You already rated the damn movie mate!']);
            }
        }

        if (\request('rate') <= 5) {
            return response(['message' => 'Why are you such a douche mate, c\'mon?']);
        }

        return response(['message' => 'You rated a movie!']);
    }

    public function unrate()
    {
        \request()->validate([
           'video_id' => 'required|integer',
           //'rate' => 'required|integer'
        ]);
        //dd('here or not?');

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
        //dd(auth()->user()->all());
       // $user->with('rated')->get()->pluck('rated.*.rated')->flatten();

        $rates = auth()->user()->rated->pluck('rated');
        return response($rates);
    }

    public function rates2()
    {
        //dd(auth()->user()->all());
       // $user->with('rated')->get()->pluck('rated.*.rated')->flatten();

//        \request()->validate([
//            'video_id' => 'required|integer'
//        ]);

        $video_id = \request('video_id');

       if (auth()->user()->rated->pluck('rated')->contains('video_id', $video_id)) {
           return response(auth()->user()->rated->pluck('rated')->where('video_id', $video_id)->flatten());
       } else {
           return response(['message' => 'No videos found mate!']);
       }

//        $rates = auth()->user()->rated->pluck('rated');
//        return response($rates);
    }

    public function updateRate()
    {

    }

}
