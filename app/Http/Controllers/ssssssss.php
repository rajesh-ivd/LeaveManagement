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
        // $validator = Validator::make($request->all(), [
        //     'userid' => 'required|integer',
        // ]);

        // if ($validator->fails()) {
        //     return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        // }
$userId = $request->userid;


$leaveData =LeaveAssign::select(
    'leave_type.id as btid',
    'leave_type.type as label',
    'leave_type.color',
    DB::raw('SUM(leave_assign.leave_count) as count'),

    DB::raw('(SELECT SUM(apply_leave.count) FROM apply_leave WHERE apply_leave.status = "Approved" AND apply_leave.assign_id =leave_assign.leave_type ) as lcount')
)
->join('leave_type', 'leave_assign.leave_type', '=', 'leave_type.id')
// ->where('leave_assign.employeeid', '=', $userId)
->groupBy('leave_type.id', 'leave_type.type', 'leave_type.color')
->get();

    $leaveCounts = LeaveAssign::get();

foreach ($leaveCounts as $lcount) {

    
    printf($lcount);
}
die();


$responseData = [];

// $subQueryResult = DB::connection('mysql_second')
//     ->table('apply_leave')
//     ->select(DB::raw('SUM(apply_leave.count) as lcount'))
//     ->where('apply_leave.status', 'Approved')
//     ->where('apply_leave.assign_id', $userId)
//     ->get();

foreach ($leaveData as $leave) {
            $responseData = [
            'data' => $leaveData->map(function ($leave) {
                return [
                    'count' => (int)$leave->count, 
                    'color' => $leave->color,
                    'btid' => (string)$leave->btid, 
                    'lcount' => (int)$leave->lcount,
                    'value' => (int)$leave->lcount,
                    'label' => $leave->label,
                ];
            }),
            'chart' => $leaveData->map(function ($leave) {
                return [
                    'value' => (int)$leave->lcount, 
                    'count' => (int)$leave->count,
                    'btid' => (string)$leave->btid, 
                    'lcount' => (int)$leave->lcount,
                    'color' => $leave->color,
                    'title' => $leave->label,
                ];
            }),
            'message' => $leaveData->isEmpty() ? 'Empty Form' : 'Success',
            'emp' => [],
            'userid' => $userId, 
        ];

}

return response()->json($responseData);
    }
}
