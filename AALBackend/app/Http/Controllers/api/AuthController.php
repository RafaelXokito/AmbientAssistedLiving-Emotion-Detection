<?php
namespace App\Http\Controllers\api;
use App\Http\Resources\Administrator\AdministratorResource;
use App\Http\Resources\Auth\AuthResource;
use App\Http\Resources\Client\ClientResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseController
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth:api', ['except' => ['login', 'activateClient']]);
    }
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        request()->request->add([
            'grant_type' => 'password',
            'client_id' => $request->type == "RaspberryPi" ? env('PASSPORT_CLIENT_ID_PI') : env('PASSPORT_CLIENT_ID_WEB'),
            'client_secret' => $request->type == "RaspberryPi" ? env('PASSPORT_CLIENT_SECRET_PI') : env('PASSPORT_CLIENT_SECRET_WEB'),
            'username' => $request->email,
            'password' => $request->password,
            'scope'         => '',
        ]);

        $request = Request::create(env('PASSPORT_SERVER_URL') . '/oauth/token', 'POST');
        $response = Route::dispatch($request);
        $errorCode = $response->getStatusCode();

        if ($errorCode == '200') {
            $user = User::where('email',$validator->validated()["email"])->first();
            if (str_contains($user->userable_type, "Client") && ! $user->userable->is_active)
                return response()->json(['error' => 'Client not activated!'], 403);
            return json_decode((string) $response->content(), true);
        } else {
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised'], 403);
        }
    }

    public function toggleNotifiable(){
        Auth::user()->notifiable = !Auth::user()->notifiable;
        Auth::user()->save();
        return new AuthResource(Auth::user());
    }

    /***
     * Activate the client
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function activateClient(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        if (! auth()->attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $user = User::where('email',$validator->validated()["email"])->first();
        if (str_contains($user->userable_type, "Client") && ! $user->userable->is_active) {
            $user->userable->is_active = true;
            $user->userable->save();
            return response()->json(['success' => 'Client activated!'], 200);
        }
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    /***
     * Update current logged user
     * @param Request $request
     * @return AdministratorResource|ClientResource|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'email|unique:App\Models\User,email',
            'name' => 'string',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        try {
            DB::beginTransaction();

            $user = Auth::user();
            if ($request->has("name"))
                $user->name = $request["name"];
            if ($request->has("email"))
                $user->email = $request["email"];
            $user->save();

            DB::commit();

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(array(
                'code'      =>  400,
                'message'   =>  $th->getMessage()
            ), 400);
        }

        if (str_contains(Auth::user()->userable_type, 'Client')){
            $validatorClient = Validator::make($request->all(), [
                'contact' => [function ($attribute, $value, $fail) {
                    if (!preg_match("/^([9][1236])[0-9]*?$/", $value)) {
                        $fail('The phone number need to follow the portuguese number.');
                    }
                }],
                'birthdate' => ['bail', 'date', 'before:today'],
            ]);
            if ($validatorClient->fails()) {
                return response()->json($validator->errors(), 422);
            }

            try {
                DB::beginTransaction();

                $client = Auth::user()->userable;
                if ($request->has("contact"))
                    $client->contact = $request["contact"];
                if ($request->has("birthdate"))
                    $client->birthdate = $request["birthdate"];

                $client->save();

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                return response()->json(array(
                    'code'      =>  400,
                    'message'   =>  $th->getMessage()
                ), 400);
            }

            return new ClientResource($client);
        }

        return new AdministratorResource($user->userable);
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|confirmed|min:6',
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }
        $user = User::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)]
        ));
        return response()->json([
            'message' => 'User successfully registered',
            'user' => $user
        ], 201);
    }
     */


    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request) {
        $accessToken = $request->user()->token();
        $token = $request->user()->tokens->find($accessToken);
        $token->revoke();
        $token->delete();
        return response()->json(['message' => 'User successfully signed out']);
    }
    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh(Request $request) {
        request()->request->add([
            'refresh_token' => $request->refresh_token,
            'grant_type' => 'refresh_token',
            'client_id' => $request->type == "RaspberryPi" ? env('PASSPORT_CLIENT_ID_PI') : env('PASSPORT_CLIENT_ID_WEB'),
            'client_secret' => $request->type == "RaspberryPi" ? env('PASSPORT_CLIENT_SECRET_PI') : env('PASSPORT_CLIENT_SECRET_WEB'),
            'scope'         => '',
        ]);

        $request = Request::create(env('PASSPORT_SERVER_URL') . '/oauth/token', 'POST');

        $response = Route::dispatch($request);

        $errorCode = $response->getStatusCode();

        if ($errorCode == '200') {
            return json_decode((string) $response->content(), true);
        } else {
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised'], 403);
        }
    }
    /**
     * Get the authenticated User.
     *
     * @return AuthResource
     */
    public function userProfile() {
        return new AuthResource(auth()->user());
    }
    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token){
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => new AuthResource(auth()->user())
        ]);
    }
}
