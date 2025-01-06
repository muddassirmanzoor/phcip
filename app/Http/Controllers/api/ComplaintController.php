<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\ComplaintAttachment;
use App\Models\Complaints;
use App\Models\ComplaintTrack;
use App\Models\Department;
use App\Models\ImageMetaData;
use App\Models\Images;
use App\Models\LocationData;
use App\Models\Schools;
use App\Models\SISUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ComplaintController extends Controller
{

    /**
     *
     * @param \Illuminate\Http\Request $request
     */
    public function addComplaint(Request $request)
    {
        try {

            // Define validation rules
            $validator = Validator::make($request->all(), [
                'complaint_category_id_fk' => 'required|integer|max:50',
                'complaint_type_id_fk' => 'required|max:20',
                'complaint_details' => 'required|string',
                'district_id_fk' => 'required|max:50',
                'tehsil_id_fk' => 'required|max:50',
                'markaz_id' => 'sometimes|nullable',
                'complaint_source_id' => 'required',
            ]);

            // Check if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'data' => $validator->errors()
                ], 422);
            }

            $last_c = Complaints::latest()->first();

            $year = date('y');

            // Create new complaint
            $complaint = Complaints::create([
                'complaint_category_id_fk' => $request->complaint_category_id_fk,
                'complaint_type_id_fk' => $request->complaint_type_id_fk,
                'complaint_details' => $request->complaint_details,
                'district_id_fk' => $request->district_id_fk,
                'tehsil_id_fk' => $request->tehsil_id_fk,
                'markaz_id' => $request->markaz_id,
                'school_name' => $request->school_name,
                'complaint_source_id' => $request->complaint_source_id,
                'user_id' => Auth::user()->id,
                'status_id' => 1,
                'assigned_to_user_id' => 0,
                'lat' => $request->lat,
                'lng' => $request->lng,
            ]);

            //Set Complaint ID
            $complaint_id = $request->district_id_fk.$request->tehsil_id_fk.$year.$complaint->id;
            $complaint->complaint_id = $complaint_id;
            $complaint->save();

            // Handle attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('attachments/' . $request->complaint_type_id_fk . '/user_id/' . Auth::user()->id
                        , 'public');
                    ComplaintAttachment::create([
                        'complaint_id' => $complaint->id,
                        'attachment_path' => $path,
                    ]);
                }
            }

            // Return response
            return response()->json([
                'success' => true,
                'message' => 'Complaint added successfully',
                'complaint' => $complaint
            ], 201);
        } catch (\Exception $ex) {
            $response = [
                'status' => 'false',
                'message' => $ex->getMessage(),
            ];
            return response()->json($response, 400);
        }
    }

    public function complaintList(Request $request)
    {

        $query = Complaints::with('complaint_type');

        // Check Date
        if ($request->has('complaint_creation_date')) {
            $query->whereDate('created_at', $request->complaint_creation_date);
        }

        // Check Status
        if ($request->has('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        // Check District
        if ($request->has('district_id')) {
            $query->where('district_id_fk', $request->district_id);
        }

        // Check Tehsil
        if ($request->has('tehsil_id')) {
            $query->where('tehsil_id_fk', $request->tehsil_id);
        }

        // Check Markaz
        if ($request->has('markaz_id')) {
            $query->where('markaz_id', $request->markaz_id);
        }

        $data = [];

        // Retrieve complaints based on user type
        if (Auth::user()->user_type_id == 3) { // Complaint user

            $complaints = $query->where('user_id', Auth::user()->id)->get();
            $data['submitted'] = $this->mapComplaintTypeName($complaints);

        } elseif (Auth::user()->user_type_id == 1) { //Admin

            $complaints = $query->get();
            $data = $this->mapComplaintTypeName($complaints);

        } elseif (Auth::user()->user_type_id == 2) {// Department User

            $complaints = $query->where('assigned_to_user_id', Auth::user()->id)->get();
            $data['assigned'] = $this->mapComplaintTypeName($complaints);
        }



        return response()->json([
            'success' => true,
            'message' => 'Complaint List successfully',
            'complaints' => $data
        ], 200);
    }

    public function complaintsCount(Request $request)
    {

        $query = Complaints::with('complaint_type');

        // Check Date
        if ($request->has('complaint_creation_date')) {
            $query->whereDate('created_at', $request->complaint_creation_date);
        }

        // Check Status
        if ($request->has('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        $data = [];
            // Admin
            $statuses = [
                'received' => 1,
                'assigned' => 2,
                'processing' => 3,
                'resolved' => 4,
                'declined' => 5,
                'sent_back' => 6
            ];

            foreach ($statuses as $key => $statusId) {
                $statusQuery = clone $query; // Clone the query for each status to avoid modifying the original query
                $complaints = $statusQuery->where('status_id', $statusId)
                    ->when($request->filled('district_id'), function ($query) use ($request) {
                        return $query->where('district_id_fk', $request->district_id);
                    })
                    ->when($request->filled('tehsil_id'), function ($query) use ($request) {
                        return $query->where('tehsil_id_fk', $request->tehsil_id);
                    })
                    ->when($request->filled('markaz_id'), function ($query) use ($request) {
                        return $query->where('markaz_id', $request->markaz_id);
                    })
                    ->count();

                $data[$key] = $complaints;
            }

        return response()->json([
            'success' => true,
            'message' => 'Complaints Count successfully',
            'complaints' => $data
        ], 200);
    }

    /**
     * @param $complaints
     * @return mixed
     */
    private function mapComplaintTypeName($complaints)
    {
        return $complaints->map(function ($complaint) {
            if ($complaint->relationLoaded('complaint_type') && $complaint->complaint_type) {
                $complaint->complaint_type_name = $complaint->complaint_type->complaint_type;
            } else {
                $complaint->complaint_type_name = null; // or some default value
            }
            // Hide the complaint_type relationship
            $complaint->makeHidden('complaint_type');
            return $complaint;
        });
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function departmentUserList(Request $request)
    {
        $users = User::where('department_id', $request->department_id)->get();

        return response()->json([
            'success' => true,
            'message' => 'User List successfully',
            'users' => $users
        ], 200);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function complaintAssign(Request $request)
    {
        $complaint = Complaints::where('id', $request->complaint_id)->first();
        $complaint->status_id = $request->status_id;
        $complaint->assigned_to_user_id = $request->assigned_to_user_id;
        $complaint->updated_by = Auth::user()->id;
        $complaint->updated_at = Carbon::now();
        $complaint->save();

        $track = ComplaintTrack::create([
            'status_id' => $request->status_id,
            'assigned_by' => Auth::user()->id,
            'assigned_to' => $request->assigned_to_user_id,
            'complaint_id' => $complaint->id,
            'note' => $request->note,
            'action' => $request->action,
            'complaint_action_date' => date('Y-m-d H:i:s'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Complaint Assigned Successfully',
            'track' => $track,
        ], 200);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function complaintUpdate(Request $request)
    {
        $complaint = Complaints::where('id', $request->complaint_id)->first();
        $complaint->status_id = $request->status_id;
        $complaint->updated_by = Auth::user()->id;
        $complaint->updated_at = Carbon::now();
        // Sent Back Case
        if($request->status_id == 6){
            $complaint->assigned_to_user_id = 0;
        }
        $complaint->save();

        $complaintTrack = ComplaintTrack::where('id', $request->complaint_id)->first();
        $assigned_to = $complaintTrack->assigned_to;

        // Sent Back Case
        if($request->status_id == 6){
            $assigned_to = $complaintTrack->assigned_by;
        }

        ComplaintTrack::create([
            'status_id' => $request->status_id,
            'assigned_by' => $complaintTrack->assigned_by,
            'assigned_to' => $assigned_to,
            'complaint_id' => $complaint->id,
            'note' => $request->note,
            'action' => $request->action,
            'complaint_action_date' => date('Y-m-d H:i:s'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Complaint Status Updated Successfully',
            'complaint' => $complaint,
        ], 200);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function complaintDetail(Request $request)
    {
        $complaint = \DB::table('complaints')
            ->leftJoin('users', 'complaints.user_id', '=', 'users.id')
            ->leftJoin('status_types as complaint_status', 'complaints.status_id', '=', 'complaint_status.id')
            ->leftJoin('complaint_types', 'complaints.complaint_type_id_fk', '=', 'complaint_types.id')
            ->leftJoin('complaint_categories', 'complaints.complaint_category_id_fk', '=', 'complaint_categories.id')
            ->leftJoin('complaint_sources', 'complaints.complaint_source_id', '=', 'complaint_sources.id')
            ->leftJoin('districts', 'complaints.district_id_fk', '=', 'districts.d_id')
            ->leftJoin('tehsils', 'complaints.tehsil_id_fk', '=', 'tehsils.t_id')
            ->leftJoin('markazs', 'complaints.markaz_id', '=', 'markazs.m_id')
            ->where('complaints.id', $request->complaint_id)
            ->select(
                'complaints.*',
                'users.name as complainant_name',
                'complaint_status.status_name',
                'complaint_types.complaint_type',
                'complaint_categories.complaint_category',
                'complaint_sources.complaint_source',
                'districts.d_name as district_name',
                'tehsils.t_name as tehsil_name',
                'markazs.m_name as markaz_name',
            )
            ->first();

        $complaintTracks = \DB::table('complaint_tracks')
            ->leftJoin('users', 'complaint_tracks.assigned_to', '=', 'users.id')
            ->leftJoin('departments', 'users.department_id', '=', 'departments.id')
            ->leftJoin('status_types as track_status', 'complaint_tracks.status_id', '=', 'track_status.id')
            ->where('complaint_tracks.complaint_id', $request->complaint_id)
            ->select(
                'complaint_tracks.*',
                'users.name as assigned_user',
                'departments.department_name as assigned_department',
                'track_status.status_name as track_status'
            )
            ->get();

//        $user = User::where('id', Auth::user()->id)->first();
//        if($user->user_type_id == 3 && $complaint->status_id == 1){
//            $complaint->status_name = 'Submitted';
//        }

        return response()->json([
            'success' => true,
            'message' => 'Complaint Detail Successfully',
            'complaint' => $complaint,
            'complaint_track' => $complaintTracks,
        ], 200);
    }
}
