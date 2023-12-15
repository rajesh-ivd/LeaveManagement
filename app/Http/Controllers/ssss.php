<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\ApplyLeave;
use App\Models\LeaveAssign;
use App\Models\LeaveType;

use Illuminate\Support\Facades\Auth; 

class DashbordController extends Controller
{
    public function Dashbord_alldata(Request $request)
    {
        $userId = $request->userid;

    
        $leaveData =LeaveAssign::select(
                        'leave_type.id as btid',
                        'leave_type.type as label',
                        'leave_type.color',
                        DB::raw('SUM(leave_assign.leave_count) as count'),

                        DB::raw('(SELECT SUM(apply_leave.count) FROM apply_leave WHERE apply_leave.status = "Approved" AND apply_leave.employeeid =leave_assign.leave_type ) as lcount')
                    )
                    ->join('leave_type', 'leave_assign.leave_type', '=', 'leave_type.id')
                    // ->where('leave_assign.employeeid', '=', $userId)
                    ->groupBy('leave_type.id', 'leave_type.type', 'leave_type.color')
                    ->get();


$arrnew = [];
        for ($i = 0; $i < count($leave_approved); $i++) {
            $arrnew[] = array_merge(["count" => $count_leave[$i]], $leave_approved[$i]);
        }

//                    $leaveDataCount = ApplyLeave::where('leave_type', 'leave_count', 'is_active')
//     ->join('apply_leave', 'apply_leave.assign_id')
//     ->get();

// // Assuming you want to print the result
// foreach ($leaveDataCount as $data) {
//     print_r($data->toArray());
// }

// die(); 



        //     if ($userId ) {
        //     $employeeData = LeaveAssign::select(
        //                 'leave_type.id as btid',
        //                 'leave_type.type as label',
        //                 'leave_type.color',
        //                 DB::raw('sum(leave_assign.leave_count) as count'),

        //                 DB::raw('(SELECT SUM(apply_leave.count) FROM apply_leave WHERE apply_leave.status = "Approved" AND apply_leave.assign_id =leave_assign.leave_type ) as lcount')
        //             )
        //             ->join('leave_type', 'leave_assign.leave_type', '=', 'leave_type.id')
        //             // ->where('leave_assign.employeeid', '=', $userId)
        //             ->groupBy('leave_type.id', 'leave_type.type', 'leave_type.color')
        //             ->get();

            
        //     return response()->json([
        //         'id' => $userId,
        //         'message' => 'Successfully Found',
        //         'status' => 'success',
        //         'data' => [
        //              $employeeData->toArray(),
        //         ],
        //     ]);
        // }


        $responseData = [
            'data' => $leaveData->map(function ($leave) {
                return [
                    'count' => (float) $leave->count,
                    'color' => $leave->color,
                    'btid' => (string) $leave->btid,
                    'lcount' => $leave->status === 'Approved' ? (float) $leave->lcount : 0,
                    'value' => (float) $leave->lcount,
                    'label' => $leave->label,
                ];
            }),
            'chart' => $leaveData->map(function ($leave) {
                return [
                    'value' => $leave->status === 'Approved' ? (float) $leave->lcount : 0,
                    'count' => (float) $leave->count,
                    'btid' => (string) $leave->btid,
                    'lcount' => $leave->status === 'Approved' ? (float) $leave->lcount : 0,
                    'color' => $leave->color,
                    'title' => $leave->label,
                ];
            }),
            'message' => $leaveData->isEmpty() ? 'Empty Form' : 'Success',
            'emp' => [],  // Add logic to fetch employee data if needed
            'userid' => $userId,
        ];

        return response()->json($responseData);
    }
}
