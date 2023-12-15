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

                $Employees = Employee::select(
                    'employee.id as emp_id',
                    'employee.employeeid as emp_employeeid',
                    'employee.first_name as emp_first_name',
                    'employee.last_name as emp_last_name',
                    'employee.email as emp_email',
                    'employee.location as emp_location',
                    'employee.department as emp_department',
                    'employee.type as emp_type',
                    'employee.is_active as emp_is_active',
                    'employee.is_deleted as emp_is_deleted',
                    'employee.created_at as emp_created_at',
                    'approver.id as approver_id',
                    'approver.employeeid as approver_employeeid',
                    'approver.first_name as approver_first_name',
                    'approver.last_name as approver_last_name',
                    'approver.email as approver_email',
                    'approver.location as approver_location',
                    'approver.department as approver_department',
                    'approver.type as approver_type',
                    'approver.is_active as approver_is_active',
                    'approver.is_deleted as approver_is_deleted',
                    'approver.created_at as approver_created_at',
                    'leave_bank_emp.leave_bank as total'
                )
                ->leftJoin('leave_bank AS leave_bank_emp', 'employee.employeeid', '=', 'leave_bank_emp.employeeid')
                ->leftJoin('employee AS approver', 'leave_bank_emp.approverid', '=', 'approver.employeeid')
                ->where('employee.employeeid', '=', $userId)
                ->first();

    if ($userId == true ){

        $leaveData = LeaveType::leftJoin('leave_assign', 'leave_type.id', '=', 'leave_assign.leave_type')
        ->leftJoin('apply_leave', 'leave_assign.id', '=', 'apply_leave.assign_id')
        ->groupBy('leave_type.id', 'leave_type.type', 'leave_type.color')
        ->where('leave_assign.employeeid', $userId)
        ->select(
            'leave_assign.leave_count as count',
            'leave_type.color',
            'leave_assign.id as lid',
            'leave_type.id as value',
            'leave_type.id as btid',
            'leave_type.type AS label',
            DB::raw('SUM((apply_leave.status = "Approved") * apply_leave.count) as lcount')
        )->get();


        $dataItem = [];
        $cartItem = [];

        foreach ($leaveData as $leaveID) {

             $dataItem[] = [
                'count' => $leaveID->count,
                'color' => $leaveID->color,
                'lid' => $leaveID->lid,
                'lcount' => (float) $leaveID->lcount,
                'value' => $leaveID->value,
                'btid' => $leaveID->btid,
                'label' => $leaveID->label,
             ];

             $cartItem[] = [
                'value' => (float) $leaveID->lcount,
                'count' => $leaveID->count,
                'btid' => $leaveID->btid,
                'lid' => $leaveID->lid,
                'lcount' => (float) $leaveID->lcount,
                'color' => $leaveID->color,
                'title' => $leaveID->label,
             ];


         }


            if ($Employees) {
                $employeeData = [
                    'id' => $Employees->emp_id,
                    'employeeid' => $Employees->emp_employeeid,
                    'first_name' => $Employees->emp_first_name,
                    'last_name' => $Employees->emp_last_name,
                    'email' => $Employees->emp_email,
                    'location' => $Employees->emp_location,
                    'department' => $Employees->emp_department,
                    'type' => $Employees->emp_type,
                    'is_active' => $Employees->emp_is_active,
                    'is_deleted' => $Employees->emp_is_deleted,
                    'created_at' => $Employees->emp_created_at,
                ];

                $approverData = [
                    'id' => $Employees->approver_id,
                    'employeeid' => $Employees->approver_employeeid,
                    'first_name' => $Employees->approver_first_name,
                    'last_name' => $Employees->approver_last_name,
                    'email' => $Employees->approver_email,
                    'location' => $Employees->approver_location,
                    'department' => $Employees->approver_department,
                    'type' => $Employees->approver_type,
                    'is_active' => $Employees->approver_is_active,
                    'is_deleted' => $Employees->approver_is_deleted,
                    'created_at' => $Employees->approver_created_at,
                    'total'=>$Employees->total,
                ];
            }

         if (!empty($leaveData)) {
            return response()->json([
                'data' => $dataItem,
                'chart' => $cartItem,
                'message' => 'Empty Form',

                'emp' => [
                    'total' => $Employees->total,
                    'Employee' => $employeeData,
                    'approver' => $approverData,
            ],
            ]);
        } else {
            return response()->json([
                'data' => "Data not found",
                'chart' => 'Empty cart',
                'message' => 'Empty Form',
                'emp' => [],
            ]);
        }

    }

     else
            {
                $leaveData = LeaveType::join('leave_assign', 'leave_type.id', '=', 'leave_assign.leave_type')
                ->groupBy('leave_type.id', 'leave_type.type', 'leave_type.color')
                ->select(
                    'leave_type.id as id',
                    'leave_type.type',
                    'leave_type.color',
                    DB::raw('SUM(leave_assign.leave_count) as count')
                )->get();

            $leaveTypes = LeaveType::leftJoin('leave_assign', 'leave_type.id', '=', 'leave_assign.leave_type')
                ->leftJoin('apply_leave', 'leave_assign.id', '=', 'apply_leave.assign_id')
                ->groupBy('leave_type.id', 'leave_type.type', 'leave_type.color')
                ->select(
                    'leave_type.id',
                    'leave_type.type',
                    'leave_type.color',
                    DB::raw('SUM(leave_assign.leave_count) as count'),
                    DB::raw('SUM(CASE WHEN apply_leave.status = "Approved" THEN apply_leave.count ELSE 0 END) as lcount')
                )->get();


            $dataItem = [];
            $cartItem = [];

            foreach ($leaveTypes as $leaveType) {
               $leaveTypes =  $leaveData->where('id', $leaveType->id)->first();
                $dataItem[] = [
                    'count' => $leaveTypes->count ,
                    'color' => $leaveType->color,
                    'lcount' => (float) $leaveType->lcount,
                    'value' => (float) $leaveType->lcount,
                    'btid' => $leaveType->id,
                    'label' => $leaveType->type,
                ];

                $cartItem[] = [
                    'value' => (float) $leaveType->lcount,
                    'count' => $leaveTypes->count ,
                    'btid' => $leaveType->id,
                    'lcount' => (float) $leaveType->lcount,
                    'color' => $leaveType->color,
                    'title' => $leaveType->type,
                ];
            }

            if (!empty($dataItem)) {
                return response()->json([
                    'data' => $dataItem,
                    'chart' => $cartItem,
                    'message' => 'Empty Form',
                    'emp' => [],
                ]);
            } else {
                return response()->json([
                    'data' => "Data not found",
                    'chart' => 'Empty cart',
                    'message' => 'Empty Form',
                    'emp' => [],
                ]);
            }

            }
        }

}


