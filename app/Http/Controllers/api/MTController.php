<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\ComplaintCategory;
use App\Models\ComplaintSource;
use App\Models\ComplaintStatus;
use App\Models\ComplaintType;
use App\Models\Department;
use App\Models\District;
use App\Models\Markaz;
use App\Models\Schools;
use App\Models\SISUser;
use App\Models\Tehsil;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class MTController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function questionsList()
    {
        $questions = \DB::table('leadership_monitoring_questions')->get();

        return response()->json([
            'success' => true,
            'message' => 'Monitoring Question List successfully',
            'data' => $questions
        ], 200);
    }
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function observationQuestionsList()
    {
        $questions = \DB::table('leadership_monitoring_questions')->get();

        $qa_questions = \DB::table('leadership_training_observation_questions')->get();

        return response()->json([
            'success' => true,
            'message' => 'Observation Question List successfully',
            'data' => $questions,
            'qa_questions' => $qa_questions
        ], 200);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function mtInfoAnswers(Request $request)
    {
            // Create new user
            $user_info = \DB::table('leadership_monitoring_info')->
            insertGetId([
                'monitor_name' => $request->monitor_name,
                'designation' => $request->designation,
                'visit_date' => date('Y-m-d'),
                'visit_time' => date('H:i:s'),
                'school_emis_code' => $request->school_emis_code,
                'school_name' => $request->school_name,
                'class_room_number' => $request->class_room_number,
                'training_category' => $request->training_category,
                'leadership_mt_id' => $request->leadership_mt_id,
                'district_name' => $request->district_name,
                'tehsil_name' => $request->tehsil_name,
                'total_observation_time' => $request->total_observation_time,
                'present_participant' => $request->present_participant,
                'attendance_sheet_present' => $request->attendance_sheet_present,
                'batch' => $request->batch,
                'remarks' => $request->remarks,
                'monitoring_user_id' => Auth::user()->id,
            ]);

            // Save user answers
            $answers = $request->answers; // This should be an array like [2,3,2,4,3,5,6]
            foreach ($answers as $index => $answer_id) {
                \DB::table('leadership_monitoring_form_answers')->insert([
                    'leadership_monitoring_info_id' => $user_info,
                    'mt_question_id' => $index + 1, // Assuming question_id starts from 1 and follows the array index
                    'mt_answer' => $answer_id,
                ]);
            }

            if ($request->hasFile('attendance_pictures')) {
                foreach ($request->file('attendance_pictures') as $file) {
                    $path = $file->store('attendance_pictures/' . $user_info . '/user_id/' . Auth::user()->id
                        , 'public');
                    \DB::table('leadership_attendance_class_pictures')->insert([
                        'leadership_monitoring_info_id' => $user_info,
                        'picture_path' => $path,
                        'picture_type' => 'attendance',
                    ]);
                }
            }

            if ($request->hasFile('class_pictures')) {
                foreach ($request->file('class_pictures') as $file) {
                    $path = $file->store('class_pictures/' . $user_info . '/user_id/' . Auth::user()->id
                        , 'public');
                    \DB::table('leadership_attendance_class_pictures')->insert([
                        'leadership_monitoring_info_id' => $user_info,
                        'picture_path' => $path,
                        'picture_type' => 'class',
                    ]);
                }
            }


            return response()->json([
                'success' => true,
                'message' => 'Response saved successfully',
            ], 200);

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function qaInfoAnswers(Request $request)
    {
            // Create new user
            $user_info = \DB::table('leadership_monitoring_info')->
            insertGetId([
                'monitor_name' => $request->monitor_name,
                'designation' => $request->designation,
                'visit_date' => date('Y-m-d'),
                'visit_time' => date('H:i:s'),
                'school_emis_code' => $request->school_emis_code,
                'school_name' => $request->school_name,
                'class_room_number' => $request->class_room_number,
                'training_category' => $request->training_category,
                'leadership_mt_id' => $request->leadership_mt_id,
                'district_name' => $request->district_name,
                'tehsil_name' => $request->tehsil_name,
                'total_observation_time' => $request->total_observation_time,
                'present_participant' => $request->present_participant,
                'attendance_sheet_present' => $request->attendance_sheet_present,
                'batch' => $request->batch,
                'remarks' => $request->remarks,
                'monitoring_user_id' => Auth::user()->id,
            ]);

            // Save user answers
            $answers = $request->answers; // This should be an array like [2,3,2,4,3,5,6]
            foreach ($answers as $index => $answer_id) {
                \DB::table('leadership_monitoring_form_answers')->insert([
                    'leadership_monitoring_info_id' => $user_info,
                    'mt_question_id' => $index + 1, // Assuming question_id starts from 1 and follows the array index
                    'mt_answer' => $answer_id,
                ]);
            }

            // Save user answers
            $qa_answers = $request->qa_answers; // This should be an array like [2,3,2,4,3,5,6]
            foreach ($qa_answers as $index => $answer_id) {
                \DB::table('leadership_training_observation_answers')->insert([
                    'leadership_monitoring_info_id' => $user_info,
                    'leadership_training_observation_question_id' => $index + 1, // Assuming question_id starts from 1 and follows the array index
                    'leadership_trainee_form_answers_type_id' => $answer_id,
                ]);
            }

            if ($request->hasFile('attendance_pictures')) {
                foreach ($request->file('attendance_pictures') as $file) {
                    $path = $file->store('attendance_pictures/' . $user_info . '/user_id/' . Auth::user()->id
                        , 'public');
                    \DB::table('leadership_attendance_class_pictures')->insert([
                        'leadership_monitoring_info_id' => $user_info,
                        'picture_path' => $path,
                        'picture_type' => 'attendance',
                    ]);
                }
            }

            if ($request->hasFile('class_pictures')) {
                foreach ($request->file('class_pictures') as $file) {
                    $path = $file->store('class_pictures/' . $user_info . '/user_id/' . Auth::user()->id
                        , 'public');
                    \DB::table('leadership_attendance_class_pictures')->insert([
                        'leadership_monitoring_info_id' => $user_info,
                        'picture_path' => $path,
                        'picture_type' => 'class',
                    ]);
                }
            }


            return response()->json([
                'success' => true,
                'message' => 'Response saved successfully',
            ], 200);

    }
}
