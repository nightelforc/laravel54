<?php
/**
 * Created by PhpStorm.
 * User: nightelf
 * Date: 2019/7/18
 * Time: 17:58
 */

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Http\Model\YearModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class YearController extends Controller
{
    /**
     * @return \Illuminate\Http\Response
     */
    public function lists()
    {
        $yearModel = new YearModel();
        $this->data = $yearModel->lists();
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function info(Request $request)
    {
        $rules = [
            'id' => 'required|integer',
        ];
        $message = [
            'id.required' => '获取工程年度参数失败',
            'id.integer' => '工程年度参数类型错误',
        ];
        $input = $request->only('id');
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $yearModel = new YearModel();
            $this->data = $yearModel->info($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 220101;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 220102;
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
    public function add(Request $request)
    {
        $rules = [
            'name' => 'required',
            'startTime' => 'required|date_format:Y-m-d',
            'endTime' => 'required|date_format:Y-m-d|after:startTime',
        ];
        $message = [
            'name.required' => '请输入工程年度名称',
            'startTime.required' => '请输入工程年度起始时间',
            'startTime.date_format' => '工程年度起始时间',
            'endTime.required' => '请输入工程年度截止时间',
            'endTime.date_format' => '工程年度截止时间',
            'endTime.after' => '工程年度截止时间必须大于工程年度起始时间',
        ];
        $input = $request->all();
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $yearModel = new YearModel();
            $checkYear = $yearModel->checkYear($input['startTime']);
            if ($checkYear == 0) {
                $result = $yearModel->insert($input);
                if (!$result) {
                    $this->code = 220208;
                    $this->msg = '保存失败，请稍后重试';
                }
            } else {
                $this->code = 220207;
                $this->msg = '新年度起始时间不能小于已有年度的截止时间';
            }
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'name') {
                if (key($failed['name']) == 'Required') {
                    $this->code = 220201;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'startTime') {
                if (key($failed['startTime']) == 'Required') {
                    $this->code = 220202;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['startTime']) == 'DateFormat') {
                    $this->code = 220203;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'endTime') {
                if (key($failed['endTime']) == 'Required') {
                    $this->code = 220204;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['endTime']) == 'DateFormat') {
                    $this->code = 220205;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['endTime']) == 'After') {
                    $this->code = 220206;
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
    public function edit(Request $request)
    {
        $rules = [
            'id' => 'required|integer',
            'name' => 'required',
            'startTime' => 'required|date_format:Y-m-d',
            'endTime' => 'required|date_format:Y-m-d|after:startTime',
        ];
        $message = [
            'id.required' => '获取工程年度参数失败',
            'id.integer' => '工程年度参数类型错误',
            'name.required' => '请输入工程年度名称',
            'startTime.required' => '请输入工程年度起始时间',
            'startTime.date_format' => '工程年度起始时间',
            'endTime.required' => '请输入工程年度截止时间',
            'endTime.date_format' => '工程年度截止时间',
            'endTime.after' => '工程年度截止时间必须大于工程年度起始时间',
        ];
        $input = $request->all();
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $yearModel = new YearModel();
            $yearModel->update($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 220301;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 220302;
                    $this->msg = $validator->errors()->first();
                }
            }
            if (key($failed) == 'name') {
                if (key($failed['name']) == 'Required') {
                    $this->code = 220303;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'startTime') {
                if (key($failed['startTime']) == 'Required') {
                    $this->code = 220304;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['startTime']) == 'DateFormat') {
                    $this->code = 220305;
                    $this->msg = $validator->errors()->first();
                }
            } elseif (key($failed) == 'endTime') {
                if (key($failed['endTime']) == 'Required') {
                    $this->code = 220306;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['endTime']) == 'DateFormat') {
                    $this->code = 220307;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['endTime']) == 'After') {
                    $this->code = 220308;
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
    public function delete(Request $request){
        $rules = [
            'id' => 'required|integer',
        ];
        $message = [
            'id.required' => '获取工程年度参数失败',
            'id.integer' => '工程年度参数类型错误',
        ];
        $input = $request->only('id');
        $validator = Validator::make($input, $rules, $message);
        if ($validator->passes()) {
            $yearModel = new YearModel();
            $yearModel->delete($input);
        } else {
            $failed = $validator->failed();
            if (key($failed) == 'id') {
                if (key($failed['id']) == 'Required') {
                    $this->code = 220401;
                    $this->msg = $validator->errors()->first();
                }
                if (key($failed['id']) == 'Integer') {
                    $this->code = 220402;
                    $this->msg = $validator->errors()->first();
                }
            }
        }
        return $this->ajaxResult($this->code, $this->msg, $this->data);
    }
}