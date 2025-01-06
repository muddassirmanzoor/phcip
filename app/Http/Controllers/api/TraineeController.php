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

class TraineeController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function questionsList()
    {
        $questions = \DB::table('leadership_trainee_form_questions')->get();

        return response()->json([
            'success' => true,
            'message' => 'Question List successfully',
            'data' => $questions
        ], 200);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function answerList()
    {
        $questions = \DB::table('leadership_trainee_form_answers_type')->get();

        return response()->json([
            'success' => true,
            'message' => 'Answer List successfully',
            'data' => $questions
        ], 200);
    }
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function districtList()
    {
        $districts = \DB::table('districts')->get();

        return response()->json([
            'success' => true,
            'message' => 'District List successfully',
            'data' => $districts
        ], 200);
    }
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function tehsilList(Request $request)
    {
        $tehsil = \DB::table('tehsil');
            if ($request->has('district_id')) {
                $tehsil->where('district_idfk', $request->district_id);
            }
        $tehsil = $tehsil->get();

        return response()->json([
            'success' => true,
            'message' => 'Tehsil List successfully',
            'data' => $tehsil
        ], 200);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function schoolDetail(Request $request)
    {
        $batch = \DB::table('batches_info')
            ->whereDate('batch_start_date', '<=', date('Y-m-d'))
            ->whereDate('batch_end_date', '>=', date('Y-m-d'))
            ->first();

        $school = \DB::table('leadership_mt')
            ->where('school_emis_code', $request->school_emis_code)
            ->where('mt_batch', $batch->batch_nmae)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'School Data successfully',
            'data' => $school
        ], 200);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function participantInfoAnswers(Request $request)
    {

        $info = \DB::table('leadership_participant_trainee_info')
            ->where('android_id', $request->android_id)
            ->first();
        if($info){
            return response()->json([
                'success' => true,
                'message' => 'Response already sent',
            ], 200);
        }else{
            // Create new user
            $user_info = \DB::table('leadership_participant_trainee_info')->
            insertGetId([
                'android_id' => $request->android_id,
                'participant_name' => $request->participant_name,
                'designation' => $request->designation,
                'contact_number' => $request->contact_number,
                'date' => $request->date,
                'training_day' => $request->training_day,
                'school_emis_code' => $request->school_emis_code,
                'school_name' => $request->school_name,
                'class_room_number' => $request->class_room_number,
                'training_category' => $request->training_category,
                'mt_name' => $request->mt_name,
                'leadership_mt_id' => $request->leadership_mt_id,
                'particpant_remarks' => $request->particpant_remarks,
                'district_name' => $request->district_name,
                'tehsil_name' => $request->tehsil_name,
                'batch' => $request->batch,
                'remarks' => $request->remarks,
            ]);

            // Save user answers
            $answers = $request->answers; // This should be an array like [2,3,2,4,3,5,6]
            foreach ($answers as $index => $answer_id) {
                \DB::table('leadership_trainee_form_answer')->insert([
                    'leadership_participant_info_id' => $user_info,
                    'question_id' => $index + 1, // Assuming question_id starts from 1 and follows the array index
                    'answer_id' => $answer_id,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Response saved successfully',
            ], 200);
        }

    }
}
