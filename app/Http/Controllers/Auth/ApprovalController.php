<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/22
 * Time: 9:18
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Model\WorkflowItemModel;
use App\Http\Model\WorkflowItemProcessModel;
use App\Http\Model\WorkflowModel;
use App\Http\Model\WorkflowNodeModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApprovalController extends Controller
{
    /**
     * @param $code
     * @param $data
     * @return array
     */
    public static function approval($code, $data)
    {
        $approval = [
            'status' => true,
            'result' => false,
            'approvalId' => ''
        ];
        if (isset($data['id'])){
            $pk = $data['id'];
            unset($data['id']);
        }else{
            $pk = '';
        }

        $workflowModel = new WorkflowModel();
        $info = $workflowModel->info(['code' => $code]);
        $callBackClass = $info['className'];
        $callBackMethod = $info['methodName'];
        if (!$info['status']) {
            $approval['status'] = false;
            self::afterApproval($callBackClass, $callBackMethod, $pk, $data,1);
            return $approval;
        }
        $session = session(parent::pasn);
        $workflowNodeModel = new WorkflowNodeModel();
        //加载审批流程当前全流程节点
        $processList = $workflowNodeModel->handlerLists(['workflowId' => $info['id'], 'projectId' => $session['projectId']]);

        $approvalData = [
            'workflowId' => $info['id'],
            'projectId' => $session['projectId'],
            'adminId' => $session['id'],
            'joinTime' => date('Y-m-d H:i:s'),
            'process' => json_encode($processList),
            'callBackClass' => $callBackClass,
            'callBackMethod' => $callBackMethod,
            'pk' => $pk,
            'data' => json_encode($data)
        ];

        $workflowItemModel = new WorkflowItemModel();
        $itemId = $workflowItemModel->insert($approvalData);
        $itemProcess = [];
        foreach ($processList as $p) {
            $itemProcess[] = ['itemId' => $itemId, 'adminId' => $p, 'createTime' => date('Y-m-d H:i:s')];
        }

        $workflowItemProcessModel = new WorkflowItemProcessModel();
        $result = $workflowItemProcessModel->insertArray($itemProcess);
        if ($result) {
            $approval['result'] = true;
            $approval['approvalId'] = $itemId;
        }

        return $approval;
    }

    public static function afterApproval($callBackClass, $callBackMethod, $pk, $data,$approvalResult)
    {
        $className ='\App\Http\Model\\'.$callBackClass;
        $newClass = new $className;
        call_user_func([$newClass,$callBackMethod],$pk,$data,$approvalResult);
    }
}