<?php
namespace app\index\controller;
use communal\Communal;
use think\Db;
use think\Request;
use app\index\model\Article;

class Index extends \think\Controller
{

    public function _initialize()
    {
        $bonus= Db::table('basic_config')->where("k",'is_kai')->find();
        if($bonus['v']=='off'){
            $uname = session('uname');
            if(empty($uname)){
                //网站被关闭
                die("网站被关闭");
            }
        }
        //检查登录
        //
    }
	private $allUrl = "http://www.r6d7.com";

	/*
	展示列表
	 */
    public function index()
    {
		$where=[];
		if(input('title')) {
		    setcookie("title",input('title'));
			$where['title']= ['like', '%'.input('title').'%'];
			$this->assign('title', input('title'));
		}else{
            $title = isset( $_COOKIE['title'] ) ? $_COOKIE['title'] : '';
            $this->assign('title',$title);
        }
		$cate_id = input('cate_id') == null ?  1 : input('cate_id');

        $cateInfo =  Db::table('article_cate')->where("id",$cate_id)->find();
        $where['cate_id']= $cate_id;
        $this->assign('cateInfo', $cateInfo);
		$this->assign('cate_id', $cate_id);

    	$list = Db::table('article') 
            ->field('id,title,create_time')
			->where($where)
			->paginate(15, false, ['query' => Request::instance()->param()] );
		$page = $list->render();
		$count=$list->total;


    	$cateList = Db::table('article_cate')->select();

		$this->assign('page', $page);

		$this->assign('list', $list);

		return $this->fetch('list',[
			'list'=>$list,
			'page'=>$page,
			'count'=>$count,
			'cateList'=>$cateList
			]);
    }
	/*
	根据id查询文章
	 */
    public function getInfo()
    {
    	$id = input("id");
    	$info = Db::table('article') -> where("id",$id)->find();
    	if($info['content']==null){
            $info = $this->getInfoUrl($id);
        }
        $info['content'] = str_replace('<br style="font-family: 微软雅黑, Helvetica, Arial, sans-serif; font-size: 22.4px; white-space: normal; background-color: rgb(255, 255, 255);">','<br>',$info['content']);
        $info['content'] = str_replace('<br><br><br>','<br>',$info['content']);
        $info['content'] = str_replace('<br><br>','<br>',$info['content']);
        //$info['content'] = str_replace('。','。<br>',$info['content']);


        $cateList = Db::table('article_cate')->select();

		return $this->fetch('info',[
            'info'=>$info,
			'cateList'=>$cateList,
			]);
    }
    public function aa(){
        //设置最大运行时间
        ini_set('max_execution_time', 3000);
        $InfoList = Db::table('article')->where(['title'=>['like','%　12%']])->limit(1000)->column("title",'id');

        echo count($InfoList);
        echo "<br>";
        $updateALl = array();

        foreach ($InfoList as $key => $value) {
            if(strpos($value,"　")){
                $keyALl [] = $key;
                $title = substr($value,0,strpos($value,"　"))."";
                echo $value;
                echo "<br>";
                echo $title;
                echo "<br>";
                Db::table('article')->where('id',$key)->update(['title'=>$title]);
            }
        }
    }

	/*
	根据id查询详情
	 */
    public function getInfoUrl($id)
    {
		include(APP_PATH.'../QueryList.class.php');
    	$id = input("id");
    	$InfoList = Db::table('article') -> where("id","in",$id)->select();

		$patternInfo = array(
			"content" => array(".xs-details-content", "html")
		);

    	foreach ($InfoList as $key => $value) {
			$urlInfo = $this->allUrl.$value['href'];
			$qyInfo = new \QueryList($urlInfo, $patternInfo, '', '', 'utf-8');
			$rsInfo = $qyInfo->jsonArr;

			$updateRes = [];
			if( isset ($rsInfo[0]) ){
				$updateRes['content'] = $rsInfo[0]['content'];;
				//修改内容
				Db::table('article')->where("id",$value['id'])->update($updateRes);
			}
    	}
        return  Db::table('article') -> where("id",$id)->find();
    }
}
