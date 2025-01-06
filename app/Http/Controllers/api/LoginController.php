<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\ComplaintCategory;
use App\Models\ComplaintType;
use App\Models\Department;
use App\Models\District;
use App\Models\MonitoringUser;
use App\Models\Schools;
use App\Models\SISUser;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class LoginController extends Controller
{

    /**
     * Authenticate the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'cnic' => 'required|string',
            'mobile' => 'required|string',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => 'Validation Error!',
                'data' => $validator->errors(),
            ], 403);
        }

        // Create new user
        $user = User::create([
            'name' => $request->name,
            'cnic' => $request->cnic,
            'mobile' => $request->mobile,
            'email' => $request->email,
            'password' => bcrypt($request->password), // Ensure password is hashed
            'designation' => $request->designation,
            'department_id' => $request->department_id,
            'user_type_id' => $request->user_type_id,
        ]);

        // Return response
        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'user' => $user
        ], 201);
    }

    /**
     * Authenticate the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function mtLogin(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'cnic' => 'required',
            'password' => 'required'
        ]);

        if($validate->fails()){
            return response()->json([
                'success' => true,
                'message' => 'Validation Error!',
                'data' => $validate->errors(),
            ], 403);
        }

        $cnic = str_replace('-', '', $request->cnic);

        if ($request->password !== '12345678') {
            return response()->json([
                'success' => true,
                'message' => 'Wrong Password'
            ], 401);
        }

        // Check user exist
        $user = MonitoringUser::where('mt_cnic_number', $cnic)
            ->first();

        // Check password
        if(!$user) {
            return response()->json([
                'success' => true,
                'message' => 'Invalid CNIC'
            ], 401);
        }

        $data['token'] = $user->createToken($request->cnic)->plainTextToken;
        $data['user'] = $user;

        $response = [
            'success' => true,
            'message' => 'User is logged in successfully.',
            'data' => $data,
        ];

        return response()->json($response, 200);
    }

    /**
     * Display a form of the login.
     */
    public function checkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // Check if the email exists in the database
        $user = SISUser::where('u_email', $request->email)->first();

        if (!$user) {
            return response()->json(['status' => 'false','message' => 'Email not found'], 404);
        }

        // Generate a random OTP
        $otp = mt_rand(100000, 999999);

        // Store the OTP in the user's record (you may have an otp column in your users table)
        $user->otp = Hash::make($otp);
        $user->save();

        // Send the OTP to the user's email
        Mail::raw("Your OTP is: $otp", function ($message) use ($user) {
            $message->to($user->u_email)->subject('One Time Password (OTP)');
        });
        $response = [
            'status' => 'success',
            'message' => 'OTP sent successfully on Email.',
        ];

        return response()->json($response, 200);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|numeric', // Assuming OTP is numeric
        ]);

        // Find the user by email
        $user = SISUser::where('u_email', $request->email)->first();

        if (!$user) {
            return response()->json(['status' => 'false','message' => 'User not found'], 404);
        }

        // Check if the provided OTP matches the stored OTP
        if (!Hash::check($request->otp, $user->otp)) {
            return response()->json(['status' => 'false','message' => 'Invalid OTP'], 400);
        }

        return response()->json(['status' => 'success', 'message' => 'OTP verified successfully'], 200);
    }
    public function updatePassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required', // Assuming minimum password length is 8 characters
            'otp' => 'required|numeric', // Assuming OTP is numeric
        ]);

        // Find the user by email
        $user = SISUser::where('u_email', $request->email)->first();

        if (!$user) {
            return response()->json(['status' => 'false','message' => 'User not found'], 404);
        }

        // Check if the provided OTP matches the stored OTP
        if (!Hash::check($request->otp, $user->otp)) {
            return response()->json(['status' => 'false','message' => 'Invalid OTP'], 400);
        }

        // Clear the OTP from the user's record (optional)
        $user->otp = null;
        $user->save();

        // Update the user's password
        $user->u_password = Hash::make($request->password);
        $user->save();

        return response()->json(['status' => 'success', 'message' => 'Password updated successfully'], 200);
    }

    /**
     * Display a form of the login.
     */
    public function logout()
    {
        Session::forget('email');

        return redirect()->intended('/'); // Change to your desired redirect route
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function departmentList()
    {
        $departments = Department::select('id', 'department_name')->get();

        return response()->json([
            'success' => true,
            'message' => 'Department List successfully',
            'departments' => $departments
        ], 200);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function complaintTypes()
    {
        $complaint_types = ComplaintType::select('id', 'complaint_type')->get();

        return response()->json([
            'success' => true,
            'message' => 'Complaint Types List successfully',
            'data' => $complaint_types
        ], 200);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function complaintCategories()
    {
        $complaint_types = ComplaintCategory::select('id', 'complaint_category')->get();

        return response()->json([
            'success' => true,
            'message' => 'Complaint Category List successfully',
            'data' => $complaint_types
        ], 200);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function districts()
    {
        $districts = District::get();

        return response()->json([
            'success' => true,
            'message' => 'District List successfully',
            'data' => $districts
        ], 200);
    }
}
