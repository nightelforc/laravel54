<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group([
    'namespace'=>'Auth',
    'prefix'=>'auth',
],function(){
    Route::post('/project/login', 'LoginController@loginProject');
    Route::post('/company/login', 'LoginController@loginCompany');
    Route::post('/logout', 'LoginController@logout');
    Route::post('/forgetPwd', 'LoginController@forgetPwd');
    Route::post('/resetPwd', 'LoginController@resetPwd');

});

Route::group([
    'middleware'=>'apiAuth',
],function(){
    //登录、审批、工作流等
    Route::group([
        'namespace'=>'Auth',
        'prefix'=>'auth',
    ],function(){
        Route::post('/auth/changePwd', 'LoginController@changePwd');
        //-------------------管理员管理-------------------//
        Route::post('/admin/lists', 'AdminController@lists');
        Route::post('/admin/selectLists', 'AdminController@selectLists');
        Route::post('/admin/add', 'AdminController@add');
        Route::post('/admin/info', 'AdminController@info');
        Route::post('/admin/edit', 'AdminController@edit');
        Route::post('/admin/editStatus', 'AdminController@editStatus');
        Route::post('/admin/setRole', 'AdminController@setRole');
        Route::post('/admin/setPermission', 'AdminController@setPermission');
        //-------------------角色管理-------------------//
        Route::post('/role/lists', 'RoleController@lists');
        Route::post('/role/selectLists', 'RoleController@selectLists');
        Route::post('/role/add', 'RoleController@add');
        Route::post('/role/info', 'RoleController@info');
        Route::post('/role/edit', 'RoleController@edit');
        Route::post('/role/editStatus', 'RoleController@editStatus');
        Route::post('/role/delete', 'RoleController@delete');
        Route::post('/role/setPermission', 'RoleController@setPermission');
        Route::post('/role/getPermission', 'RoleController@getPermission');
        //-------------------权限管理-------------------//
        Route::post('/permission/lists', 'PermissionController@lists');
        //-------------------审批流程管理-------------------//
        Route::post('/workflow/lists', 'WorkflowController@lists');
        Route::post('/workflow/info', 'WorkflowController@info');
        //不开放审批流程的添加
//        Route::post('/workflow/add', 'WorkflowController@add');
        Route::post('/workflow/edit', 'WorkflowController@edit');
        Route::post('/workflow/editStatus', 'WorkflowController@editStatus');

        Route::post('/workflowNode/lists', 'WorkflowController@nodeLists');
        Route::post('/workflowNode/add', 'WorkflowController@nodeAdd');
        Route::post('/workflowNode/del', 'WorkflowController@nodeDel');

        Route::post('/approval/lists', 'ApprovalController@lists');
        Route::post('/approval/myApprovalLists', 'ApprovalController@myApprovalLists');
        Route::post('/approval/accept', 'ApprovalController@accept');
        Route::post('/approval/reject', 'ApprovalController@reject');
    });

    //公司主要业务
    Route::group([
        'namespace'=>'Company',
        'prefix'=>'company',
    ],function(){
        //-------------------工人管理-------------------//
        Route::post('/employee/lists', 'EmployeeController@lists');
        Route::post('/employee/add', 'EmployeeController@add');
        Route::post('/employee/edit', 'EmployeeController@edit');
        Route::post('/employee/dayValueLists', 'EmployeeController@dayValueLists');
        Route::post('/employee/editDayValue', 'EmployeeController@editDayValue');

        Route::post('/project/lists', 'ProjectController@lists');
        Route::post('/project/add', 'ProjectController@add');
        Route::post('/project/info', 'ProjectController@info');
        Route::post('/project/edit', 'ProjectController@edit');
        Route::post('/project/editStatus', 'ProjectController@editStatus');
        Route::post('/project/delete', 'ProjectController@delete');

        Route::post('/project/editBudget', 'ProjectController@editBudget');
    });

    //项目主要业务
    Route::group([
        'namespace'=>'Project',
        'prefix'=>'project',
    ],function(){
        //-------------------工人管理-------------------//
        Route::post('/employee/search', 'EmployeeController@search');

        Route::post('/employee/lists', 'EmployeeController@lists');
        Route::post('/employee/info', 'EmployeeController@info');
        Route::post('/employee/batchChangeStatus', 'EmployeeController@batchChangeStatus');
        Route::post('/employee/batchChangeProject', 'EmployeeController@batchChangeProject');
        Route::post('/employee/addLoan', 'EmployeeController@addLoan');
        Route::post('/employee/addLiving', 'EmployeeController@addLiving');
        //考勤
        Route::post('/employee/attendanceList', 'EmployeeController@attendanceList');
        Route::post('/employee/supplement', 'EmployeeController@supplement');
        Route::post('/employee/addAttendance', 'EmployeeController@addAttendance');
        //请假
        Route::post('/employee/leaveLists', 'EmployeeController@leaveLists');
        Route::post('/employee/back', 'EmployeeController@back');
        Route::post('/employee/addLeave', 'EmployeeController@addLeave');

        //-------------------项目管理-------------------//
        Route::post('/project/selectLists', 'ProjectController@selectLists');
        //楼栋
        Route::post('/project/areaLists', 'ProjectController@areaLists');
        Route::post('/project/areaSelectLists', 'ProjectController@areaSelectLists');
        Route::post('/project/addArea', 'ProjectController@addArea');
        Route::post('/project/batchAddArea', 'ProjectController@batchAddArea');
        Route::post('/project/areaInfo', 'ProjectController@areaInfo');
        Route::post('/project/editArea', 'ProjectController@editArea');
        Route::post('/project/delArea', 'ProjectController@delArea');
        //楼层
        Route::post('/project/sectionLists', 'ProjectController@sectionLists');
        Route::post('/project/sectionSelectLists', 'ProjectController@sectionSelectLists');
        Route::post('/project/addSection', 'ProjectController@addSection');
        Route::post('/project/batchAddSection', 'ProjectController@batchAddSection');
        Route::post('/project/sectionInfo', 'ProjectController@sectionInfo');
        Route::post('/project/editSection', 'ProjectController@editSection');
        Route::post('/project/delSection', 'ProjectController@delSection');
        //预算
        Route::post('/project/budgetLists', 'ProjectController@budgetLists');
        Route::post('/project/costLists', 'ProjectController@costLists');
        //分账
        Route::post('/projectSeparate/sectionListsWithGroup', 'ProjectController@sectionListsWithGroup');
        Route::post('/projectSeparate/separateLog', 'ProjectController@separateLog');
        Route::post('/projectSeparate/separateLogInfo', 'ProjectController@separateLogInfo');
        Route::post('/projectSeparate/addAssignment', 'ProjectController@addAssignment');
        Route::post('/projectSeparate/delAssignment', 'ProjectController@delAssignment');
        Route::post('/projectSeparate/addSeparate', 'ProjectController@addSeparate');
        Route::post('/projectSeparate/delSeparate', 'ProjectController@delSeparate');
        //杂工分账
        Route::post('/projectSeparate/otherSeparateLists', 'ProjectController@otherSeparateLists');
        Route::post('/projectSeparate/addOtherSeparate', 'ProjectController@addOtherSeparate');
        Route::post('/projectSeparate/delOtherSeparate', 'ProjectController@delOtherSeparate');

        //班组
        Route::post('/projectGroup/lists', 'ProjectGroupController@lists');
        Route::post('/projectGroup/add', 'ProjectGroupController@add');
        Route::post('/projectGroup/info', 'ProjectGroupController@info');
        Route::post('/projectGroup/edit', 'ProjectGroupController@edit');
        Route::post('/projectGroup/editStatus', 'ProjectGroupController@editStatus');
        Route::post('/projectGroup/memberLists', 'ProjectGroupController@memberLists');
        Route::post('/projectGroup/addMember', 'ProjectGroupController@addMember');
        Route::post('/projectGroup/delMember', 'ProjectGroupController@delMember');
        Route::post('/projectGroup/setLeader', 'ProjectGroupController@setLeader');

        //-------------------供应商管理-------------------//
        Route::post('/supplier/lists', 'SupplierController@lists');
        Route::post('/supplier/selectLists', 'SupplierController@selectLists');
        Route::post('/supplier/add', 'SupplierController@add');
        Route::post('/supplier/info', 'SupplierController@info');
        Route::post('/supplier/edit', 'SupplierController@edit');
        Route::post('/supplier/delete', 'SupplierController@delete');
        Route::post('/supplier/orderLists', 'SupplierController@orderLists');
        Route::post('/supplier/orderInfo', 'SupplierController@orderInfo');
        Route::post('/supplier/addOrder', 'SupplierController@addOrder');
        Route::post('/supplier/batchRepay', 'SupplierController@batchRepay');
        Route::post('/supplier/repaymentLists', 'SupplierController@repaymentLists');

        //-------------------材料管理-------------------//
        Route::post('/material/search', 'MaterialController@search');
        Route::post('/material/lists', 'MaterialController@lists');
        Route::post('/material/add', 'MaterialController@add');
        Route::post('/material/info', 'MaterialController@info');
        Route::post('/material/edit', 'MaterialController@edit');
        Route::post('/material/editStatus', 'MaterialController@editStatus');

        Route::post('/material/specLists', 'MaterialController@specLists');
        Route::post('/material/specSelectLists', 'MaterialController@specSelectLists');
        Route::post('/material/addSpec', 'MaterialController@addSpec');
        Route::post('/material/editSpec', 'MaterialController@editSpec');
        Route::post('/material/specInfo', 'MaterialController@specInfo');
        Route::post('/material/delSpec', 'MaterialController@delSpec');

        //-------------------仓库管理-------------------//
        Route::post('/warehouse/search', 'WarehouseController@search');
        Route::post('/warehouse/lists', 'WarehouseController@lists');
        Route::post('/warehouse/info', 'WarehouseController@info');
        Route::post('/warehouse/getRate', 'WarehouseController@getRate');
        Route::post('/warehouse/setRate', 'WarehouseController@setRate');
        Route::post('/warehouse/setSalePrice', 'WarehouseController@setSalePrice');
        Route::post('/warehouse/consume', 'WarehouseController@consume');
        Route::post('/warehouse/expend', 'WarehouseController@expend');
        Route::post('/warehouse/breakdown', 'WarehouseController@breakdown');
        Route::post('/warehouse/allot', 'WarehouseController@allot');
        Route::post('/warehouse/purchase', 'WarehouseController@purchase');
        Route::post('/warehouse/receipt', 'WarehouseController@receipt');

        Route::post('/warehouse/logLists', 'WarehouseController@logLists');
        Route::post('/warehouse/breakdownLists', 'WarehouseController@breakdownLists');
        Route::post('/warehouse/consumeLists', 'WarehouseController@consumeLists');

        //-------------------财务管理-------------------//
        Route::post('/finance/wageLists', 'FinanceController@wageLists');
        Route::post('/finance/supplierOrder', 'FinanceController@supplierOrder');
        Route::post('/finance/loanLists', 'FinanceController@loanLists');
        Route::post('/finance/livingLists', 'FinanceController@livingLists');
    });

    //系统设置
    Route::group([
        'namespace'=>'Setting',
        'prefix'=>'setting',
    ],function(){
        Route::post('/year/lists', 'YearController@lists');
        Route::post('/year/info', 'YearController@info');
        Route::post('/year/add', 'YearController@add');
        Route::post('/year/edit', 'YearController@edit');
        Route::post('/year/delete', 'YearController@delete');

        Route::post('/unit/lists', 'UnitController@lists');
        Route::post('/unit/info', 'UnitController@info');
        Route::post('/unit/add', 'UnitController@add');
        Route::post('/unit/edit', 'UnitController@edit');
        Route::post('/unit/editStatus', 'UnitController@editStatus');

        Route::post('/profession/lists', 'ProfessionController@lists');
        Route::post('/profession/info', 'ProfessionController@info');
        Route::post('/profession/add', 'ProfessionController@add');
        Route::post('/profession/edit', 'ProfessionController@edit');
        Route::post('/profession/editStatus', 'ProfessionController@editStatus');

        Route::post('/assignment/lists', 'AssignmentController@lists');
        Route::post('/assignment/info', 'AssignmentController@info');
        Route::post('/assignment/add', 'AssignmentController@add');
        Route::post('/assignment/edit', 'AssignmentController@edit');
        Route::post('/assignment/editStatus', 'AssignmentController@editStatus');
    });

    //系统设置
    Route::group([
        'namespace'=>'Excel',
        'prefix'=>'excel',
    ],function(){
        Route::post('/import/iEmployeeLists', 'ImportController@iEmployeeLists');

        Route::get('/download', 'ExportController@download');
    });
});
