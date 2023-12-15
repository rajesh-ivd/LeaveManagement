 // $empData = Employee::select(
                //     'employee.id',
                //     'employee.employeeid',
                //     'employee.first_name',
                //     'employee.last_name',
                //     'employee.email',
                //     'employee.location',
                //     'employee.department',
                //     'employee.type',
                //     'employee.is_active',
                //     'employee.is_deleted',
                //     'employee.created_at'
                // )
                //     ->where('employee.employeeid', '=', $userId)
                //     ->first();

                //     $approverData = Employee::select(
                //         'approver.id',
                //         'approver.employeeid',
                //         'approver.first_name',
                //         'approver.last_name',
                //         'approver.email',
                //         'approver.location',
                //         'approver.department',
                //         'approver.type',
                //         'approver.is_active',
                //         'approver.is_deleted',
                //         'approver.created_at',
                //     )
                //     ->leftJoin('leave_bank AS leave_bank_emp', 'employee.employeeid', '=', 'leave_bank_emp.employeeid')
                //     ->leftJoin('employee AS approver', 'leave_bank_emp.approverid', '=', 'approver.employeeid')
                //     ->where('employee.employeeid', '=', $userId)
                //     ->first();

                //     $total = Employee::select(
                //         'leave_bank_emp.leave_bank as total',
                //     )
                //     ->leftJoin('leave_bank AS leave_bank_emp', 'employee.employeeid', '=', 'leave_bank_emp.employeeid')
                //     ->leftJoin('employee AS approver', 'leave_bank_emp.approverid', '=', 'approver.employeeid')
                //     ->where('employee.employeeid', '=', $userId)
                //     ->get();//marge both query at ones  beacouse i don't want to alalak alak query
