<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\APIServices\Users\Login;
use App\APIServices\Users\Logout;
use App\APIServices\Users\Me;
use App\APIServices\Users\Register;
use App\APIServices\Users\UpdateProfile;
use App\APIServices\Users\DeleteUser;

class UserController extends Controller
{
    public function login(Request $request)
    {
        return Login::execute($request);
    }

    public function logout(Request $request)
    {
        return Logout::execute($request);
    }    

    public function me(Request $request)
    {
        return Me::execute($request);
    }

    public function register(Request $request)
    {
       return Register::execute($request);
    }

    public function editProfile(Request $request)
    {
        return UpdateProfile::execute($request);
    }

    public function deleteUser(Request $request)
    {
        return DeleteUser::execute($request);
    }
}
