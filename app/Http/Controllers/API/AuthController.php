<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\api_m;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
// use Illuminate\Support\Facades\Request;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->guard = 'api';
        // $this->middleware('auth:api', ['except' => ['login']]);

    }
    public function login_response()
    {
        return response()->json([
            'Msg' => 'Token salah'
        ],200);
        // echo "salah";
    }

    public function loginGetCid(Request $request)
    {
        $no_hp = $request->no_hp;
        $data = DB::select(
        "SELECT
        company_id, nama_usaha
        FROM
            m_user_company
            INNER JOIN ( SELECT id FROM m_userx WHERE no_hp = '$no_hp' ) a ON m_user_company.kd_user = a.id");
        return response()->json($data, 200);
    }

    public function loginCompany(Request $request)
    {
        $cid = $request->company_id;
        $data = DB::select("SELECT company_id, nama_usaha, no_telepon, alamat FROM m_user_company WHERE company_id='$cid'");
        return response()->json($data[0], 200);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only('no_hp', 'passwd');
        $user = User::where($credentials)->first();
        // print_r($user);
        if (! $user )  {
            return response()->json(['error' => 'Unauthorized'] , 401);
        };
        if (!$token = auth($this->guard)->login($user)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return $this->respondWithToken($token, $user);




        // tidak dipakai
        // $user1 = DB::table('m_userx')->where('pass')
        // $user = DB::select('select * from users where id = ?')->first();
        // $user = DB::select('select * from m_userx where no_hp = ?', [$request->no_hp]);
        // print_r($credentials);
        // print_r($user);
        
        // $credentials = request(['email', 'password']);

        // if (! $token = auth()->attempt($credentials)) {
        //     return response()->json(['error' => 'Unauthorized'], 401);
        // }

        // return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token, $user)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'nama_user' => $user->nama,
            'email_user' => $user->email
        ]);
    }
}