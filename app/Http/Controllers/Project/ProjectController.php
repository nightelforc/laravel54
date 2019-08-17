<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/25
 * Time: 10:58
 */

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Auth\ApprovalController;
use App\Http\Controllers\Controller;
use App\Http\Model\ProjectAreaModel;
use App\Http\Model\ProjectBudgetModel;
use App\Http\Model\ProjectGroupAssignmentModel;
use App\Http\Model\ProjectGroupModel;
use App\Http\Model\ProjectGroupSeparateAccountsModel;
use App\Http\Model\ProjectModel;
use App\Http\Model\ProjectOtherSeparateAccountsModel;
use App\Http\Model\ProjectSectionModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function selectLists(){
        $projectModel = new ProjectModel();
        $this->data = $projectModel->lists();
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function areaLists(Request $request)
    {
        $rules = [
            'projectId' => 'required|integer',
            'draw'=> 'required|integer',
            'length' => 'required|integer|in:10,20,50',
            'start' => 'required|integer|min:0',
        ];
        $message = [
            'projectId.required' => '获取项目参数失败',
            'projectId.integer' => '项目参数类型错误',
            'length.required' => '获取每页记录数参数失败',
            'length.integer' => '每页记录数参数类型错误',
            'length.in' => '每页记录数参数值不正确',
            'start.required' => '获取起始记录参数失败',
            'start.integer' => '起始记录参数类型错误',
            'start.min' => '起始记录参数值不小于:min',
        ];
        $input = $request->only(['projectId', 'search', 'draw','length', 'start']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $projectAreaModel = new ProjectAreaModel();
            $lists = $projectAreaModel->lists($input);
            $this->data = [
                "draw"=>$input['draw'],
                "data"=>$lists,
                "recordsFiltered"=>count($lists),
                "recordsTotal"=>count($lists),
            ];
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 420201;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 420202;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'draw') {
                if (key($failed['draw']) == 'Required') {
                    $this->code = 420203;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['draw']) == 'Integer') {
                    $this->code = 420204;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'length') {
                if (key($failed['length']) == 'Required') {
                    $this->code = 420205;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['length']) == 'Integer') {
                    $this->code = 420206;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['length']) == 'In') {
                    $this->code = 420207;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'start') {
                if (key($failed['start']) == 'Required') {
                    $this->code = 420208;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['start']) == 'Integer') {
                    $this->code = 420209;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['start']) == 'Min') {
                    $this->code = 410210;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function addArea(Request $request)
    {
        $rules = [
            'projectId' => 'required|integer',
            'name' => 'required',
        ];
        $message = [
            'projectId.required' => '获取项目参数失败',
            'projectId.integer' => '项目参数类型错误',
            'name.required' => '请填写项目名称',
        ];
        $input = $request->only(['projectId', 'name', 'remark']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $projectAreaModel = new ProjectAreaModel();
            $projectAreaModel->insert($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 420301;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 420302;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'name') {
                if (key($failed['name']) == 'Required') {
                    $this->code = 420303;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function batchAddArea(Request $request)
    {
        $rules = [
            'projectId' => 'required|integer',
            'amount' => 'required|integer',
        ];
        $message = [
            'projectId.required' => '获取项目参数失败',
            'projectId.integer' => '项目参数类型错误',
            'amount.required' => '获取批量生成数量失败',
            'amount.integer' => '批量生成参数类型错误',
        ];
        $input = $request->only(['projectId', 'amount']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $projectAreaModel = new ProjectAreaModel();
            for ($i = 1; $i <= $input['amount']; $i++) {
                $projectAreaModel->insert(['projectId' => $input['projectId'], 'name' => $i . '号楼']);
            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 420401;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 420402;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'amount') {
                if (key($failed['amount']) == 'Required') {
                    $this->code = 420403;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['amount']) == 'Integer') {
                    $this->code = 420404;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function areaInfo(Request $request)
    {
        $rules = [
            'id' => 'required|integer',
        ];
        $message = [
            'id.required' => '获取施工区参数失败',
            'id.integer' => '施工区参数类型错误',
        ];
        $input = $request->only(['id']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $projectAreaModel = new ProjectAreaModel();
            $this->data = $projectAreaModel->info($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 420501;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 420502;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function editArea(Request $request)
    {
        $rules = [
            'id' => 'required',
            'name' => 'required',
        ];
        $message = [
            'id.required' => '获取施工区参数失败',
            'name.required' => '请填写项目名称',
        ];
        $input = $request->only(['id', 'name', 'remark']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $projectAreaModel = new ProjectAreaModel();
            $projectAreaModel->update($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 420601;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'name') {
                if (key($failed['name']) == 'Required') {
                    $this->code = 420602;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function delArea(Request $request)
    {
        $rules = [
            'id' => 'required|integer',
        ];
        $message = [
            'id.required' => '获取施工区参数失败',
            'id.integer' => '施工区参数类型错误',
        ];
        $input = $request->only(['id']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $projectSectionModel = new ProjectSectionModel();
            $result = $projectSectionModel->lists(['areaId' => $input['id']]);
            if (empty($result)) {
                $projectAreaModel = new ProjectAreaModel();
                $projectAreaModel->delete($input);
            } else {
                $this->code = 420703;
                $this->msg = '该楼栋下已建立楼层，不能删除';
            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 420701;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 420702;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function sectionLists(Request $request)
    {
        $rules = [
            'areaId' => 'required|integer',
            'draw' => 'required|integer',
            'length' => 'required|integer|in:10,20,50',
            'start' => 'required|integer|min:0',
        ];
        $message = [
            'areaId.required' => '获取施工区参数失败',
            'areaId.integer' => '施工区参数类型错误',
            'length.required' => '获取记录条数失败',
            'length.integer' => '记录条数参数类型错误',
            'length.in' => '记录条数参数值不正确',
            'start.required' => '获取起始记录位置失败',
            'start.integer' => '起始记录参数类型错误',
            'start.min' => '起始记录值不小于:min',
        ];
        $input = $request->only(['areaId', 'search', 'draw', 'length', 'start']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $projectSectionModel = new ProjectSectionModel();
            $lists = $projectSectionModel->lists($input);
            $this->data = [
                "draw"=>$input['draw'],
                "data"=>$lists,
                "recordsFiltered"=>count($lists),
                "recordsTotal"=>count($lists),
            ];
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'areaId') {
                if (key($failed['areaId']) == 'Required') {
                    $this->code = 420801;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['areaId']) == 'Integer') {
                    $this->code = 420802;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'draw') {
                if (key($failed['draw']) == 'Required') {
                    $this->code = 420803;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['draw']) == 'Integer') {
                    $this->code = 420804;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'length') {
                if (key($failed['length']) == 'Required') {
                    $this->code = 420805;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['length']) == 'Integer') {
                    $this->code = 420806;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['length']) == 'In') {
                    $this->code = 420807;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'start') {
                if (key($failed['start']) == 'Required') {
                    $this->code = 420808;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['start']) == 'Integer') {
                    $this->code = 420809;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['start']) == 'Min') {
                    $this->code = 420810;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function addSection(Request $request)
    {
        $rules = [
            'projectId' => 'required|integer',
            'areaId' => 'required|integer',
            'name' => 'required',
        ];
        $message = [
            'projectId.required' => '获取项目参数失败',
            'projectId.integer' => '项目参数类型错误',
            'areaId.required' => '获取施工区参数失败',
            'areaId.integer' => '施工区参数类型错误',
            'name.required' => '请填写项目名称',
        ];
        $input = $request->only(['projectId','areaId', 'name', 'remark']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $projectSectionModel = new ProjectSectionModel();
            $projectSectionModel->insert($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 420901;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 420902;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'areaId') {
                if (key($failed['areaId']) == 'Required') {
                    $this->code = 420903;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['areaId']) == 'Integer') {
                    $this->code = 420904;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'name') {
                if (key($failed['name']) == 'Required') {
                    $this->code = 420905;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function batchAddSection(Request $request)
    {
        $rules = [
            'projectId' => 'required|integer',
            'areaId' => 'required|integer',
            'amount' => 'required|integer',
        ];
        $message = [
            'projectId.required' => '获取项目参数失败',
            'projectId.integer' => '项目参数类型错误',
            'areaId.required' => '获取施工区参数失败',
            'areaId.integer' => '施工区参数类型错误',
            'amount.required' => '获取项目参数失败',
        ];
        $input = $request->only(['projectId','areaId', 'amount']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $projectSectionModel = new ProjectSectionModel();
            for ($i = 1; $i <= $input['amount']; $i++) {
                $projectSectionModel->insert(['areaId' => $input['areaId'], 'name' => $i . '层']);
            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 421001;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 421002;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'areaId') {
                if (key($failed['areaId']) == 'Required') {
                    $this->code = 421003;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['areaId']) == 'Integer') {
                    $this->code = 421004;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'amount') {
                if (key($failed['amount']) == 'Required') {
                    $this->code = 421005;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['amount']) == 'Integer') {
                    $this->code = 421006;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function sectionInfo(Request $request)
    {
        $rules = [
            'id' => 'required|integer',
        ];
        $message = [
            'id.required' => '获取施工段参数失败',
            'id.integer' => '施工段参数类型错误',
        ];
        $input = $request->only(['id']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $projectSectionModel = new ProjectSectionModel();
            $this->data = $projectSectionModel->info($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 421101;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 421102;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function editSection(Request $request)
    {
        $rules = [
            'id' => 'required',
            'name' => 'required',
        ];
        $message = [
            'id.required' => '获取施工区参数失败',
            'name.required' => '请填写项目名称',
        ];
        $input = $request->only(['id', 'name', 'remark']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $projectSectionModel = new ProjectSectionModel();
            $projectSectionModel->update($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 421201;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'name') {
                if (key($failed['name']) == 'Required') {
                    $this->code = 421202;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function delSection(Request $request)
    {
        $rules = [
            'id' => 'required|integer',
        ];
        $message = [
            'id.required' => '获取施工区参数失败',
            'id.integer' => '施工区参数类型错误',
        ];
        $input = $request->only(['id']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $projectGroupAssignmentModel = new ProjectGroupAssignmentModel();
            $result = $projectGroupAssignmentModel->isAssignment(['areaId' => $input['id']]);
            if ($result == 0) {
                $projectSectionModel = new ProjectSectionModel();
                $projectSectionModel->delete($input);
            } else {
                $this->code = 421303;
                $this->msg = '该楼层下已建立记录分账，不能删除';
            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 421301;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 421302;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function budgetLists(Request $request)
    {
        $rules = [
            'sectionId' => 'required|integer',
        ];
        $message = [
            'sectionId.required' => '获取施工段参数失败',
            'sectionId.integer' => '施工段参数类型错误',
        ];
        $input = $request->only(['sectionId']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $projectBudgetModel = new ProjectBudgetModel();
            $this->data = $projectBudgetModel->budgetLists($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 421401;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 421402;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function costLists(Request $request)
    {
        $rules = [
            'sectionId' => 'required|integer',
        ];
        $message = [
            'sectionId.required' => '获取施工段参数失败',
            'sectionId.integer' => '施工段参数类型错误',
        ];
        $input = $request->only(['sectionId']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $projectGroupAssignmentModel = new ProjectGroupAssignmentModel();
            $this->data = $projectGroupAssignmentModel->costLists($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 421501;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 421502;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function sectionListsWithGroup(Request $request)
    {
        $rules = [
            'areaId' => 'required|integer',
            'draw' => 'required|integer',
            'length' => 'required|integer|in:10,20,50',
            'start' => 'required|integer|min:0',
        ];
        $message = [
            'areaId.required' => '获取施工区参数失败',
            'areaId.integer' => '施工区参数类型错误',
            'length.required' => '获取每页记录参数失败',
            'length.integer' => '每页记录参数类型错误',
            'length.in' => '每页记录参数值不正确',
            'start.required' => '获取起始记录参数失败',
            'start.integer' => '起始记录参数类型错误',
            'start.min' => '起始记录参数值不小于:min',
        ];
        $input = $request->only(['areaId', 'search', 'draw', 'length', 'start']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $projectSectionModel = new ProjectSectionModel();
            $lists = $projectSectionModel->lists($input);
            $projectGroupAssignmentModel = new ProjectGroupAssignmentModel();
            foreach ($lists as $key => $r) {
                $lists[$key]->group = $projectGroupAssignmentModel->sectionLists(['sectionId' => $r->id]);
            }
            $this->data = [
                "draw"=>$input['draw'],
                "data"=>$lists,
                "recordsFiltered"=>count($lists),
                "recordsTotal"=>count($lists),
            ];
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'areaId') {
                if (key($failed['areaId']) == 'Required') {
                    $this->code = 421601;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['areaId']) == 'Integer') {
                    $this->code = 421602;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'draw') {
                if (key($failed['draw']) == 'Required') {
                    $this->code = 421603;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['draw']) == 'Integer') {
                    $this->code = 421604;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'limit') {
                if (key($failed['length']) == 'Required') {
                    $this->code = 421605;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['length']) == 'Integer') {
                    $this->code = 421605;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['length']) == 'In') {
                    $this->code = 421606;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'start') {
                if (key($failed['start']) == 'Required') {
                    $this->code = 421607;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['start']) == 'Integer') {
                    $this->code = 421607;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['start']) == 'Min') {
                    $this->code = 421608;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function separateLog(Request $request)
    {
        $rules = [
            'projectId' => 'required|integer',
            'sectionId' => 'required|integer',
            'professionId' => 'required|integer',
        ];
        $message = [
            'projectId.required' => '获取项目参数失败',
            'projectId.integer' => '项目参数类型错误',
            'sectionId.required' => '获取施工区参数失败',
            'sectionId.integer' => '施工区参数类型错误',
            'professionId.required' => '获取工种参数失败',
            'professionId.integer' => '工种参数类型错误',
        ];
        $input = $request->only(['projectId', 'sectionId', 'professionId']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $projectGroupModel = new ProjectGroupModel();
            $this->data = $projectGroupModel->lists(['projectId' => $input['projectId'], 'professionId' => $input['professionId']]);
            $projectGroupAssignmentModel = new ProjectGroupAssignmentModel();
            $projectGroupSeparateAccountsModel = new ProjectGroupSeparateAccountsModel();
            foreach ($this->data as $key => $d) {
                //施工项分账记录
                $data=[
                    'projectId'=>$input['projectId'],
                    'sectionId'=>$input['sectionId'],
                    'groupId'=>$d->id
                ];
                $this->data[$key]->assignment = $projectGroupAssignmentModel->lists($data);
                //班组成员分账记录
                $this->data[$key]->separate = $projectGroupSeparateAccountsModel->lists($data);
            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 421701;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 421702;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'sectionId') {
                if (key($failed['sectionId']) == 'Required') {
                    $this->code = 421703;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['sectionId']) == 'Integer') {
                    $this->code = 421704;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'professionId') {
                if (key($failed['professionId']) == 'Required') {
                    $this->code = 421705;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['professionId']) == 'Integer') {
                    $this->code = 421706;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function addAssignment(Request $request){
        $rules = [
            'projectId' => 'required|integer',
            'areaId' => 'required|integer',
            'sectionId' => 'required|integer',
            'professionId' => 'required|integer',
            'groupId' => 'required|integer',
            'data'=>'required|array'
        ];
        $message = [
            'projectId.required' => '获取项目参数失败',
            'projectId.integer' => '项目参数类型错误',
            'areaId.required' => '获取施工段参数失败',
            'areaId.integer' => '施工段参数类型错误',
            'sectionId.required' => '获取施工区参数失败',
            'sectionId.integer' => '施工区参数类型错误',
            'professionId.required' => '获取工种参数失败',
            'professionId.integer' => '工种参数类型错误',
            'groupId.required' => '获取班组参数失败',
            'groupId.integer' => '班组参数类型错误',
            'data.required' => '获取分账数据失败',
            'data.array' => '分账参数类型错误',
        ];
        $input = $request->only(['projectId', 'areaId', 'sectionId','professionId', 'groupId', 'data',self::token]);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $rules1 = [
                'assignmentId' => 'required|integer',
                'amount' => 'required|numeric',
                'price' => 'required|numeric',
                'totalPrice' => 'required|numeric',
            ];
            $message1 = [
                'assignmentId.required' => '获取施工项参数失败',
                'assignmentId.integer' => '施工项参数类型错误',
                'amount.required' => '获取施工量参数失败',
                'amount.numeric' => '施工量参数类型错误',
                'price.required' => '获取单价参数失败',
                'price.numeric' => '单价参数类型错误',
                'totalPrice.required' => '获取总价失败',
                'totalPrice.numeric' => '总价类型错误',
            ];
            $input1 = $input['data'];
            $continue = true;
            foreach ($input1 as $key => $i) {
                $validator1 = Validator::make($i, $rules1, $message1);
                if ($validator1->fails()) {
                    $continue = false;
                    $failed1 = $validator1->failed();
                    if (key($failed1) == 'assignmentId') {
                        if (key($failed1['assignmentId']) == 'Required') {
                            $this->code = 421813;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                        if (key($failed1['assignmentId']) == 'Integer') {
                            $this->code = 421814;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                    } elseif (key($failed1) == 'amount') {
                        if (key($failed1['amount']) == 'Required') {
                            $this->code = 421815;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                        if (key($failed1['amount']) == 'Numeric') {
                            $this->code = 421816;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                    } elseif (key($failed1) == 'price') {
                        if (key($failed1['price']) == 'Required') {
                            $this->code = 421817;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                        if (key($failed1['price']) == 'Numeric') {
                            $this->code = 421818;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                    } elseif (key($failed1) == 'totalPrice') {
                        if (key($failed1['totalPrice']) == 'Required') {
                            $this->code = 421819;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                        if (key($failed1['totalPrice']) == 'Numeric') {
                            $this->code = 421820;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                    }
                }
            }
            if ($continue) {
                $projectGroupAssignmentModel = new ProjectGroupAssignmentModel();
                $insertId = $projectGroupAssignmentModel->insert($input);
                $input['ids'] = $insertId;
                $approval = ApprovalController::approval('addAssignment', $input);
                if ($approval['status']) {
                    if ($approval['result']) {
                        $this->msg = '申请提交成功，请等待审批结果';
                    } else {
                        $this->code = 421821;
                        $this->msg = '保存失败，请稍后重试';
                    }
                }
            }
        }else{
            $failed = $validator->failed();
            if (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 421801;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 421802;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'areaId') {
                if (key($failed['areaId']) == 'Required') {
                    $this->code = 421803;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['areaId']) == 'Integer') {
                    $this->code = 421804;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'sectionId') {
                if (key($failed['sectionId']) == 'Required') {
                    $this->code = 421805;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['sectionId']) == 'Integer') {
                    $this->code = 421806;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'professionId') {
                if (key($failed['professionId']) == 'Required') {
                    $this->code = 421807;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['professionId']) == 'Integer') {
                    $this->code = 421808;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'groupId') {
                if (key($failed['groupId']) == 'Required') {
                    $this->code = 421809;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['groupId']) == 'Integer') {
                    $this->code = 421810;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'data') {
                if (key($failed['data']) == 'Required') {
                    $this->code = 421811;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['data']) == 'Integer') {
                    $this->code = 421812;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function addSeparate(Request $request){
        $rules = [
            'projectId' => 'required|integer',
            'areaId' => 'required|integer',
            'sectionId' => 'required|integer',
            'groupId' => 'required|integer',
            'data'=>'required|array'
        ];
        $message = [
            'projectId.required' => '获取项目参数失败',
            'projectId.integer' => '项目参数类型错误',
            'areaId.required' => '获取施工段参数失败',
            'areaId.integer' => '施工段参数类型错误',
            'sectionId.required' => '获取施工区参数失败',
            'sectionId.integer' => '施工区参数类型错误',
            'groupId.required' => '获取班组参数失败',
            'groupId.integer' => '班组参数类型错误',
            'data.required' => '获取分账数据失败',
            'data.array' => '分账参数类型错误',
        ];
        $input = $request->only(['projectId', 'areaId', 'sectionId', 'groupId', 'data',self::$token]);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $rules1 = [
                'memberId' => 'required|integer',
                'employeeId' => 'required|integer',
                'account' => 'required|numeric',
            ];
            $message1 = [
                'memberId.required' => '获取班组成员参数失败',
                'memberId.integer' => '班组成员参数类型错误',
                'employeeId.required' => '获取工人参数失败',
                'employeeId.integer' => '工人参数类型错误',
                'account.required' => '获取分账金额参数失败',
                'account.numeric' => '分账金额参数类型错误',
            ];
            $input1 = $input['data'];
            $continue = true;
            foreach ($input1 as $key => $i) {
                $validator1 = Validator::make($i, $rules1, $message1);
                if ($validator1->fails()) {
                    $continue = false;
                    $failed1 = $validator1->failed();
                    if (key($failed1) == 'memberId') {
                        if (key($failed1['memberId']) == 'Required') {
                            $this->code = 421911;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                        if (key($failed1['memberId']) == 'Integer') {
                            $this->code = 421912;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                    } elseif (key($failed1) == 'employeeId') {
                        if (key($failed1['employeeId']) == 'Required') {
                            $this->code = 421913;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                        if (key($failed1['employeeId']) == 'Integer') {
                            $this->code = 421814;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                    } elseif (key($failed1) == 'account') {
                        if (key($failed1['account']) == 'Required') {
                            $this->code = 421915;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                        if (key($failed1['account']) == 'Numeric') {
                            $this->code = 421916;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                    }
                }
            }
            if ($continue) {
                $projectGroupSeparateAccountsModel = new ProjectGroupSeparateAccountsModel();
                $insertId = $projectGroupSeparateAccountsModel->insert($input);
                $input['ids'] = $insertId;
                $approval = ApprovalController::approval('addSeparate', $input);
                if ($approval['status']) {
                    if ($approval['result']) {
                        $this->msg = '申请提交成功，请等待审批结果';
                    } else {
                        $this->code = 421917;
                        $this->msg = '保存失败，请稍后重试';
                    }
                }
            }
        }else{
            $failed = $validator->failed();
            if (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 421901;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 421902;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'areaId') {
                if (key($failed['areaId']) == 'Required') {
                    $this->code = 421903;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['areaId']) == 'Integer') {
                    $this->code = 421904;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'sectionId') {
                if (key($failed['sectionId']) == 'Required') {
                    $this->code = 421905;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['sectionId']) == 'Integer') {
                    $this->code = 421906;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'groupId') {
                if (key($failed['groupId']) == 'Required') {
                    $this->code = 421907;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['groupId']) == 'Integer') {
                    $this->code = 421908;
                    $this->msg = $validator->errors()->first();
                }
            }elseif (key($failed) == 'data') {
                if (key($failed['data']) == 'Required') {
                    $this->code = 421909;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['data']) == 'Integer') {
                    $this->code = 421910;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function otherSeparateLists(Request $request)
    {
        $rules = [
            'projectId' => 'required|integer',
            'draw' => 'required|integer',
            'length' => 'required|integer|in:10,20,50',
            'start' => 'required|integer|min:0',
        ];
        $message = [
            'projectId.required' => '获取项目参数失败',
            'projectId.integer' => '项目参数类型错误',
            'length.required' => '获取项目参数失败',
            'length.integer' => '记录条数参数类型错误',
            'length.in' => '记录条数参数值不正确',
            'start.required' => '获取起始记录参数失败',
            'start.integer' => '起始记录参数类型错误',
            'start.min' => '起始记录参数值不小于:min',
        ];
        $input = $request->only(['projectId', 'draw', 'length', 'start']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $projectOtherSeparateAccountsModel = new ProjectOtherSeparateAccountsModel();
            $lists = $projectOtherSeparateAccountsModel->lists($input);
            $this->data = [
                "draw"=>$input['draw'],
                "data"=>$lists,
                "recordsFiltered"=>count($lists),
                "recordsTotal"=>count($lists),
            ];
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 422001;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 422002;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'draw') {
                if (key($failed['draw']) == 'Required') {
                    $this->code = 422003;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['draw']) == 'Integer') {
                    $this->code = 422004;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'length') {
                if (key($failed['length']) == 'Required') {
                    $this->code = 422005;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['length']) == 'Integer') {
                    $this->code = 422006;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['length']) == 'In') {
                    $this->code = 422007;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'start') {
                if (key($failed['start']) == 'Required') {
                    $this->code = 422008;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['start']) == 'Integer') {
                    $this->code = 422009;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['start']) == 'Min') {
                    $this->code = 422010;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function addOtherSeparate(Request $request)
    {
        $rules = [
            'projectId' => 'required|integer',
            'employeeId' => 'required|integer',
            'account' => 'required|numeric',
            'separateTime' => 'required|date_format:Y-m-d',
            'data' => 'required|array',
        ];
        $message = [
            'projectId.required' => '获取项目参数失败',
            'projectId.integer' => '项目参数类型错误',
            'employeeId.required' => '请选择工人',
            'employeeId.integer' => '工人参数类型错误',
            'account.required' => '请填写分账金额',
            'account.numeric' => '分账金额类型错误',
            'separateTime.required' => '请选择分账时间',
            'separateTime.date_format' => '分账时间格式不正确',
            'data.required' => '请填写作业内容',
            'data.array' => '作业内容类型不正确',
        ];
        $input = $request->only(['projectId', 'employeeId', 'account', 'separateTime', 'data',self::$token]);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $rules1 = [
                'areaId' => 'required|integer',
                'sectionId' => 'required|integer',
                'professionId' => 'required|integer',
                'assignmentId' => 'required|integer',
            ];
            $message1 = [
                'areaId.required' => '请选择施工区',
                'areaId.integer' => '施工区参数类型错误',
                'sectionId.required' => '请选择楼层/施工段',
                'sectionId.integer' => '施工段参数类型错误',
                'professionId.required' => '请选择工种',
                'professionId.integer' => '工种参数类型错误',
                'assignmentId.required' => '请选择施工项',
                'assignmentId.integer' => '施工项参数类型错误',
            ];
            $input1 = $input['data'];
            $continue = true;
            foreach ($input1 as $key => $i) {
                $validator1 = Validator::make($i, $rules1, $message1);
                if ($validator1->fails()) {
                    $continue = false;
                    $failed1 = $validator1->failed();
                    if (key($failed1) == 'areaId') {
                        if (key($failed1['areaId']) == 'Required') {
                            $this->code = 422111;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                        if (key($failed1['areaId']) == 'Integer') {
                            $this->code = 422112;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                    } elseif (key($failed1) == 'sectionId') {
                        if (key($failed1['sectionId']) == 'Required') {
                            $this->code = 422113;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                        if (key($failed1['sectionId']) == 'Integer') {
                            $this->code = 422114;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                    } elseif (key($failed1) == 'professionId') {
                        if (key($failed1['professionId']) == 'Required') {
                            $this->code = 422115;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                        if (key($failed1['professionId']) == 'Integer') {
                            $this->code = 422116;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                    } elseif (key($failed1) == 'assignmentId') {
                        if (key($failed1['assignmentId']) == 'Required') {
                            $this->code = 422117;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                        if (key($failed1['assignmentId']) == 'Integer') {
                            $this->code = 422118;
                            $this->msg = $validator1->errors()->first();
                            break;
                        }
                    }
                }
            }
            if ($continue) {
                $projectOtherSeparateModel = new ProjectOtherSeparateAccountsModel();
                $insertId = $projectOtherSeparateModel->insert($input);
                $input['ids'] = $insertId;
                $approval = ApprovalController::approval('otherSeparate', $input);
                if ($approval['status']) {
                    if ($approval['result']) {
                        $this->msg = '申请提交成功，请等待审批结果';
                    } else {
                        $this->code = 422119;
                        $this->msg = '保存失败，请稍后重试';
                    }
                }
            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 422101;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 422102;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'employeeId') {
                if (key($failed['employeeId']) == 'Required') {
                    $this->code = 422103;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['employeeId']) == 'Integer') {
                    $this->code = 422104;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'account') {
                if (key($failed['account']) == 'Required') {
                    $this->code = 422105;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['account']) == 'Numeric') {
                    $this->code = 422106;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'separateTime') {
                if (key($failed['separateTime']) == 'Required') {
                    $this->code = 422107;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['separateTime']) == 'DateFormat') {
                    $this->code = 422108;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'data') {
                if (key($failed['data']) == 'Required') {
                    $this->code = 422109;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['data']) == 'Array') {
                    $this->code = 422110;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function delOtherSeparate(Request $request)
    {
        $rules = [
            'id' => 'required|integer',
        ];
        $message = [
            'id.required' => '获取施工区参数失败',
            'id.integer' => '施工区参数类型错误',
        ];
        $input = $request->only(['id']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $projectOtherSeparateAccountsModel = new ProjectOtherSeparateAccountsModel();
            $projectOtherSeparateAccountsModel->delete($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 422201;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 422202;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function areaSelectLists(Request $request){
        $rules = [
            'projectId' => 'required|integer',
        ];
        $message = [
            'projectId.required' => '获取项目参数失败',
            'projectId.integer' => '项目参数类型错误',
        ];
        $input = $request->only(['projectId']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $projectAreaModel = new ProjectAreaModel();
            $this->data = $projectAreaModel->selectLists($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 422301;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 422302;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function sectionSelectLists(Request $request){
        $rules = [
            'areaId' => 'required|integer',
        ];
        $message = [
            'areaId.required' => '获取施工区参数失败',
            'areaId.integer' => '施工区参数类型错误',
        ];
        $input = $request->only(['areaId']);
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $projectSectionModel = new ProjectSectionModel();
            $this->data = $projectSectionModel->selectLists($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'projectId') {
                if (key($failed['projectId']) == 'Required') {
                    $this->code = 422401;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['projectId']) == 'Integer') {
                    $this->code = 422402;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }
}