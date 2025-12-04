<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    public function checkUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'value' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid'], 400);
        }
        try {
            $check = User::where('UserID', $request->value)->exists();
            return response()->json(['status' => $check], 200);
        } catch (\Exception $exception) {
            return response()->json(['message' => 'Oops! Something went wrong'], 400);
        }
    }

    public function allUsers()
    {
        $user = JWTAuth::parseToken()->authenticate();
        // if ($user->UserType == 'admin') {
        //     return User::select('ID', 'Name', 'Designation')->get();
        // } else {
        //     return User::where('UserType', '!=', 'admin')
        //         ->select('ID', 'Name', 'Designation')
        //         ->get();
        // }
        return User::select('UserID', 'UserName', 'Designation')->get();
    }

    public function index(Request $request)
    {
        $take = $request->take;
        $search = $request->search;
        $authUser = JWTAuth::parseToken()->authenticate();
        return User::where('UserName', 'like', '%' . $search . '%')
            ->where('UserType', '!=', 'Admin')
            ->select('UserID', 'UserName as Name', 'Mobile', 'Designation', 'UserType', 'Status')
            ->paginate($take);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'designation' => 'required',
            'mobile' => 'required',
            'UserID' => 'required|unique:UserManager',
            'password' => 'required|string|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid'], 400);
        }

        try {
            $auth = JWTAuth::parseToken()->authenticate();
            $user = new User();
            $user->UserID = $request->UserID;
            $user->UserName = $request->name;
            $user->Mobile = $request->mobile;
            $user->UserType = 'O';
            $user->Designation = $request->designation;
            $user->Password = bcrypt($request->password);
            $user->Status = 'Y';
            $user->CreatedBy = $auth->UserID;
            $user->Avatar = 'default.png';
            $user->save();
            return response()->json(['message' => "User added successfully"]);

        } catch (\Exception $exception) {

            return $exception->getMessage();
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'designation' => 'required',
            'mobile' => 'required',
            'UserID' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid'], 400);
        }

        try {

            $auth = JWTAuth::parseToken()->authenticate();
            $user = User::where('UserID', $request->UserID)->first();
            $user->UserName = $request->name;
            $user->Mobile = $request->mobile;
            $user->Designation = $request->designation;
            if ($request->password) $user->Password = bcrypt($request->password);
            $user->UpdatedBy = $auth->UserID;
            $user->save();
            return response()->json(['message' => "User updated successfully"]);

        } catch (\Exception $exception) {

            return $exception->getMessage();
        }
    }

    public function delete($id)
    {
        if (false) {
            return response()->json(['message' => "User is already used!"], 500);
        } else {
            User::where('id', $id)->delete();
            return response()->json(['message' => "User deleted successfully"]);
        }
    }
}
