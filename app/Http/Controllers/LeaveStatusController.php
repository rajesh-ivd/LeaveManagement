<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\ApplyLeave;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\Location;
use App\Models\LeaveAssign;

class LeaveStatusController extends Controller
{
    public function Leaves_alldata(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'empid' => 'required_without_all:employee_id',
            'employee_id' => 'required_without_all:empid',
            'com' => 'string',
            'type' => 'string',
            'perpage' => 'integer',
            'page' => 'integer',
            // 'search' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }

        $employeeId = $request->employee_id;
        $empId = $request->empid;
        $com = $request->com;
        $type = $request->type;
        $perpage = $request->perpage;
        $page = $request->page;
        $search = $request->search;
        $firstName = $request->first_name;
        $pageOffset = ($perpage * $page) - $perpage;

        $query = ApplyLeave::select(
            'apply_leave.*',
            'employee.*',
            'leave_type.*',
            'leave_assign.leave_count',
            'location.locationName'
        )
            ->join('employee', 'apply_leave.employeeid', '=', 'employee.employeeid')
            ->join('leave_assign', 'apply_leave.assign_id', '=', 'leave_assign.id')
            ->join('leave_type', 'leave_assign.leave_type', '=', 'leave_type.id')
            ->join('location', 'employee.location', '=', 'location.location_id')
            ->where('apply_leave.employeeid', $empId)
            ->where(function ($q) use ($search) {
                $q->where('employee.first_name', 'like', "%$search%")
                    ->orWhere('employee.last_name', 'like', "%$search%")
                    ->orWhere('leave_type.type', 'like', "%$search%")
                    ->orWhere('location.locationName', 'like', "%$search%")
                    ->orWhere('employee.employeeid', 'like', "%$search%");
            })
            ->orderBy('apply_leave.created_at', 'desc')
            ->paginate($perpage, ['*'], 'page', $page);

        if ($empId === '0000') {
            $employeeData = ApplyLeave::select(
                'apply_leave.*',
                'employee.*',
                'leave_type.*',
                'leave_assign.leave_count',
                'location.locationName'
            )
                ->join('employee', 'apply_leave.employeeid', '=', 'employee.employeeid')
                ->join('leave_assign', 'apply_leave.assign_id', '=', 'leave_assign.id')
                ->join('leave_type', 'leave_assign.leave_type', '=', 'leave_type.id')
                ->join('location', 'employee.location', '=', 'location.location_id')
                ->get();

            $paginationUserData = [
                'totalCount' => $employeeData->count(),
                'totalPage' => 1,
            ];
            return response()->json([
                'id' => $empId,
                'message' => 'Successfully Found',
                'status' => 'success',
                'response' => [
                    'apply_leave' => $employeeData->toArray(),
                ],
                'pagination' => $paginationUserData,
            ]);
        }

        if ($employeeId === '0000' && $firstName === 'admin') {
            $adminData = Employee::where('employeeid', '0000')->first();

            if ($adminData) {
                return response()->json([
                    'id' => $employeeId,
                    'message' => 'Successfully Found',
                    'status' => 'success',
                    'response' => $adminData->toArray(),
                ]);
            } else {
                return response()->json([
                    'id' => $empId,
                    'message' => 'Admin data not found',
                    'status' => 'unsuccess',
                    'response' => [],
                ]);
            }
        }



        if ($empId) {
            $employeeData1 = ApplyLeave::select(
                'apply_leave.*',
                'employee.*',
                'leave_type.*',
                'leave_assign.leave_count',
                'location.locationName'
            )
                ->where('apply_leave.approver', $empId)
                ->whereIn('apply_leave.status', ['Approved', 'Rejected'])
                ->join('employee', 'apply_leave.employeeid', '=', 'employee.employeeid')
                ->join('leave_assign', 'apply_leave.assign_id', '=', 'leave_assign.id')
                ->join('leave_type', 'leave_assign.leave_type', '=', 'leave_type.id')
                ->join('location', 'employee.location', '=', 'location.location_id')
                ->paginate($perpage);

            $paginationUserData1 = [
                'totalCount' => $employeeData1->total(),
                'totalPage' => $employeeData1->lastPage(),
            ];
            return response()->json([
                'id' => $empId,
                'message' => 'Successfully Found',
                'status' => 'success',
                'response' => [
                    'apply_leave' => $employeeData1->items(),
                ],
                'pagination' => $paginationUserData1,
            ]);
        }



        $isValidEmployeeId = Employee::where('employeeid', $empId)->first();

        if (!$isValidEmployeeId) {
            return response()->json([
                'id' => 'Invalid Employee ID',
                'message' => 'The provided employee ID is not valid.',
                'status' => 'unsuccess',
                'response' => [],
            ]);
        }

        $employeeData = Employee::where('employeeid', $empId)->first();
        if ($employeeData) {
            return response()->json([
                'id' => $empId,
                'message' => 'Successfully Found.',
                'status' => 'success',
                'response' => $employeeData,
            ]);
        } else {
            return response()->json([
                'id' => $empId,
                'message' => 'Unsuccessfully Found.',
                'status' => 'unsuccess',
                'response' => [],
            ]);
        }
    }
}
