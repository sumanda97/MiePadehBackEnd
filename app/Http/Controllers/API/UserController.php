<?php

namespace App\Http\Controllers\API;

use App\Actions\Fortify\PasswordValidationRules;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    public function login(Request $request)
    {
        try{
            //Validasi Input
            $request->validate(
                ['email' => 'email|required',
                'password'=>'required']
            );
            //Check Crediantls
            $credentials = request(['email','password']);
            if(!Auth::attempt($credentials)){
                return ResponseFormatter::error([
                    'message' => 'Unauthorized'
                ], 'Authetication Failed',500);
            }
            // Jika Hash Tidak sesuai Maka Error
            $user = User::where('email',$request->email)->first();
            if(!Hash::check($request->password,$user->password,[])){
                throw new \Exception('Invalid Credentials');
            }
            //Jika Berhasil maka Login
            $tokenResult = $user->createToken('authToken')->plainTextToken;
            return ResponseFormatter::success([
                'access_token'=>$tokenResult,
                'token_type' =>'Bearer',
                'user'=>$user
            ], 'Authenticated');
        } catch(Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error
            ], 'Authentication Failed',500);
        }
    }
    
    public function register(Request $request)
    {
         try{
            $request->validate(
               [ 'name' => ["required", "string" ,"max:255"],
                'email' => ["required", "string" ,"email" ,"max:255" ,"unique:users"],
                'password' => $this-> passwordRules()

                ]);

                User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'address' => $request->address,
                    'houseNumber' => $request->houseNumber,
                    'phoneNumber' => $request->phoneNumber,
                    'city' => $request->city,
                    'password' => Hash::make($request->password),
                ]);

                $user = User::where('email', $request->email)->first();

                $tokenResult = $user->createToken('authToken')->plainTextToken;

                return ResponseFormatter::success([
                    'access_token' => $tokenResult,
                    'token_type' => 'Bearer',
                    'user' => $user 
                ]);

         } catch (Exception $error) {
              return  ResponseFormatter::error([
                  'message' => 'Something Went Wrong',
                  'error' =>$error
              ],'Authentication Failed',500);
         }
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken()->delete();
        return ResponseFormatter::success($token, 'Token Revoked');
    }

    public function updateProfile(Request $request)
    {
        $data = $request->all();

        $user = Auth::user();
        $user->update($data);

        return ResponseFormatter::success($user,'Profile Updated'); 
    }

    public function fetch(Request $request)
    {
        return ResponseFormatter::success($request->user(),'Data Berhasil Diambil');
    }

    public function updatePhoto(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'file' => 'required|image|max:2048'
        ]);
        if($validator-> fails()){
            return ResponseFormatter::error(
                ['error' => $validator->errors()],'Update Fails',401
            );
        }
        if($request->file('file')){
            $file = $request->file->store('assets/user','public');

            // Save Photo to Database (URL)
            $user = Auth::user();
            $user->profile_photo_path=$file;
            $user->update();

            return ResponseFormatter::success([$file],'File Success Upload');
        }
    }

    use PasswordValidationRules;


}

