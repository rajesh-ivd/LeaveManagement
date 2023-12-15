<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\ApplyLeave;
use App\Models\LeaveAssign;
use App\Models\LeaveType;
use App\Models\Employee;
use App\Models\Leavebank;

use Illuminate\Support\Facades\Auth;

class DashbordController extends Controller
{
    public function Dashbord_alldata(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userid' => 'sometimes|required|integer',
        ]);


        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }

        $userId = $request->userid;
            if ($userId) {
                $LeveDataId = LeaveAssign::select(
                    'leave_assign.leave_count as count',
                    'leave_type.color',
                    'leave_assign.id as lid',
                    'leave_type.id as value',
                    'leave_type.id as btid',
                    'leave_type.type AS label'
                )
                    ->join('leave_type', 'leave_assign.leave_type', '=', 'leave_type.id')
                    ->where('leave_assign.employeeid', '=', $userId)
                    ->get();

                    $leaveCount = LeaveAssign::join('leave_type', 'leave_assign.leave_type', '=', 'leave_type.id')
                                ->leftJoin('apply_leave', 'leave_assign.id', '=', 'apply_leave.assign_id')
                                ->groupBy('leave_type.id', 'leave_type.type', 'leave_type.color')
                                ->where('leave_assign.employeeid', $userId)
                                ->select(
                                    'leave_type.id',
                                    'leave_type.type',
                                    'leave_type.color',
                                    DB::raw('SUM((apply_leave.status = "Approved") * apply_leave.count) as lcount')
                                )->get();

                $empData = Employee::select(
                    'employee.id',
                    'employee.employeeid',
                    'employee.first_name',
                    'employee.last_name',
                    'employee.email',
                    'employee.location',
                    'employee.department',
                    'employee.type',
                    'employee.is_active',
                    'employee.is_deleted',
                    'employee.created_at'
                )
                    ->where('employee.employeeid', '=', $userId)
                    ->first();

                    $approverData = Employee::select(
                        'approver.id',
                        'approver.employeeid',
                        'approver.first_name',
                        'approver.last_name',
                        'approver.email',
                        'approver.location',
                        'approver.department',
                        'approver.type',
                        'approver.is_active',
                        'approver.is_deleted',
                        'approver.created_at',
                    )
                    ->leftJoin('leave_bank AS leave_bank_emp', 'employee.employeeid', '=', 'leave_bank_emp.employeeid')
                    ->leftJoin('employee AS approver', 'leave_bank_emp.approverid', '=', 'approver.employeeid')
                    ->where('employee.employeeid', '=', $userId)
                    ->first();

                    $total = Employee::select(
                        'leave_bank_emp.leave_bank as total',
                    )
                    ->leftJoin('leave_bank AS leave_bank_emp', 'employee.employeeid', '=', 'leave_bank_emp.employeeid')
                    ->leftJoin('employee AS approver', 'leave_bank_emp.approverid', '=', 'approver.employeeid')
                    ->where('employee.employeeid', '=', $userId)
                    ->get();

                $responseDataId = [
                    'data' => $LeveDataId->map(function ($leaveID) use ($leaveCount) {
                        $lcount = $leaveCount->where('id', $leaveID->btid)->value('lcount') ?? 0;
                        return [
                            'count' => $leaveID->count,
                            'color' => $leaveID->color,
                            'lid' => $leaveID->lid,
                            'lcount' => (float) $lcount,
                            'value' => $leaveID->value,
                            'btid' => $leaveID->btid,
                            'label' => $leaveID->label,
                        ];
                    }),
                    'chart' => $LeveDataId->map(function ($leaveID) use ($leaveCount) {
                        $lcount = $leaveCount->where('id', $leaveID->btid)->value('lcount') ?? 0;
                        return [
                            'value' => (float) $lcount,
                            'count' => $leaveID->count,
                            'btid' => $leaveID->btid,
                            'lid' => $leaveID->lid,
                            'lcount' => (float) $lcount,
                            'color' => $leaveID->color,
                            'title' => $leaveID->label,
                        ];
                    }),
                    'message' => 'Empty Form',
                    'emp' => [
                        'total' =>$total[0]->total,
                        'emp' => $empData,
                        'approver' => $approverData,
                    ],
                ];

                return response()->json($responseDataId);
            }


        $leaveData = LeaveType::join('leave_assign', 'leave_type.id', '=', 'leave_assign.leave_type')
            ->groupBy('leave_type.id', 'leave_type.type', 'leave_type.color')
            ->select(
                'leave_type.id as btid',
                'leave_type.type as label',
                'leave_type.color',
                DB::raw('SUM(leave_assign.leave_count) as count')
            )->get();

        $leaveCount = LeaveAssign::join('leave_type', 'leave_assign.leave_type', '=', 'leave_type.id')
            ->leftJoin('apply_leave', 'leave_assign.id', '=', 'apply_leave.assign_id')
            ->groupBy('leave_type.id', 'leave_type.type', 'leave_type.color')
            ->select(
                'leave_type.id',
                'leave_type.type',
                'leave_type.color',
                DB::raw('SUM((apply_leave.status = "Approved") * apply_leave.count) as lcount')
            )->get();

        $responseData = [
            'data' => $leaveData->map(function ($leave) use ($leaveCount) {
                $lcount = $leaveCount->where('id', $leave->btid)->value('lcount') ?? 0;

                return [
                    'count' => $leave->count,
                    'color' => $leave->color,
                    'btid' =>  $leave->btid,
                    'lcount' => (float) $lcount,
                    'value' => (float) $lcount,
                    'label' => $leave->label,
                ];
            }),
            'chart' => $leaveData->map(function ($leave) use ($leaveCount) {
                $lcount = $leaveCount->where('id', $leave->btid)->value('lcount') ?? 0;

                return [
                    'value' => (float) $lcount,
                    'count' => $leave->count,
                    'btid' =>  $leave->btid,
                    'lcount' => (float) $lcount,
                    'color' => $leave->color,
                    'title' => $leave->label,
                ];
            }),
            'message' => 'Empty Form',

            'emp' => [],
        ];

        return response()->json($responseData);
    }
}

