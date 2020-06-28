<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class WasteController extends Controller
{

    /**
     * 公海库列表
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        return View::make('admin.waste.index');
    }

    public function data(Request $request)
    {
        $data = $request->all(['company_name','name','phone']);
        $res = Project::onlyTrashed()
            ->where('owner_user_id',-1)
            //公司名称
            ->when($data['company_name'],function ($query) use($data){
                return $query->where('company_name',$data['company_name']);
            })
            //姓名
            ->when($data['name'],function ($query) use($data){
                return $query->where('name',$data['name']);
            })
            //联系电话
            ->when($data['phone'],function ($query) use($data){
                return $query->where('phone',$data['phone']);
            })
            ->orderBy('deleted_at','desc')
            ->paginate($request->get('limit', 30));
        $data = [
            'code' => 0,
            'msg' => '正在请求中...',
            'count' => $res->total(),
            'data' => $res->items()
        ];
        return Response::json($data);

    }

    /**
     * 拾回
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function retrieve(Request $request)
    {
        $id = $request->get('id');
        $project = Project::onlyTrashed()
            ->where('id',$id)
            ->first();
        if (!$project){
            return Response::json(['code'=>1,'msg'=>'拾回异常：项目不存在']);
        }
        DB::beginTransaction();
        try{
            DB::table('project')->where('id',$id)->update([
                'deleted_user_id' => null,
                'deleted_at' => null,
                'owner_user_id' => $request->user()->id,
            ]);
            DB::commit();
            return Response::json(['code'=>0,'msg'=>'拾回成功']);
        }catch (\Exception $exception){
            DB::rollBack();
            Log::info('拾回异常：'.$exception->getMessage());
            return Response::json(['code'=>1,'msg'=>'拾回失败']);
        }

    }

    /**
     * 项目详情
     * @param $id
     * @return \Illuminate\Contracts\View\View
     */
    public function show($id)
    {
        $model = Project::onlyTrashed()->with('designs')->findOrFail($id);
        return View::make('admin.waste.show',compact('model'));
    }

}
