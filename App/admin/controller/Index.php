<?php
namespace app\admin\controller;
use think\Db;
use think\Request;


class   Index extends Base
{
	private $allUrl = "https://www.uh786s.top/home.htm";

    /*
    展示列表
     */
    public function index()
    {
        $where=[];
        if(input('title')) {
            $where['title']= ['like', '%'.input('title').'%'];
            $this->assign('title', input('title'));
        }
        if(input('cate_id')) {
            $where['cate_id']= input('cate_id');
        }
        $this->assign('cate_id', input('cate_id'));

        $page_size = input('page_size')=='' ? 10 : input('page_size');
        $this->assign('page_size', $page_size);

        global  $jingpinList;
        $jingpinList =  Db::table('article_shou')
            ->column('article_id');
        $list = Db::table('article')
            ->field('article.id,title,article.create_time,content,order,article_cate.cate_name')
            ->join('article_cate','article.cate_id=article_cate.id','left')
            ->where($where)
            ->order('`order` desc,sfjj desc,length(content) desc')
            ->paginate(input('page_size'), false, ['query' => Request::instance()->param()] )
            ->each(function($item,$key){
                global $jingpinList;

                if ( in_array($item['id'],$jingpinList) ){
                    $item['isjingpin'] = 1;
                }else{
                    $item['isjingpin'] = 0;
                }
                return $item;
            });

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
    /*添加*/
    public function addArticle(){
        if(Request::instance()->isPost()){
            $posrData= Request::instance()->post();

            $res = Db::table('article')->insert($posrData);
            if($res){
                $this->success("添加成功！",'/Admin/index/index');
            }else{
                $this->success("添加失败！",$_SERVER['HTTP_REFERER']);
            }
        }
        if(input('cate_id')) {
            $where['cate_id']= input('cate_id');
        }
        $this->assign('cate_id', input('cate_id'));

        $cateList = Db::table('article_cate')->select();
        return $this->fetch('article_add',[
            'cateList'=>$cateList
        ]);

    }

    /*
    展示列表
     */
    public function jingpin()
    {
        $jingpinList =  Db::table('article_shou')
            ->field('article_shou.id,article_id,user_id,title')
            ->join("article",'article.id=article_shou.article_id','left')
            ->select();

        $this->assign('jingpinList', $jingpinList);
        $this->assign('count', count($jingpinList));

        return $this->fetch('jingpin');
    }
    public function delArticle(){
    	if(Request::instance()->isPost()){
    		$posrData= Request::instance()->post();

			$res = Db::table('article')->where( ['id'=>['in',$posrData['id']] ] )->delete();

			return ['code'=>1];
    	}
    }
    public function deljingpinArticle(){
    	if(Request::instance()->isPost()){
    		$posrData= Request::instance()->post();

			$res = Db::table('article_shou')->where('id', $posrData['id'])->delete();

			return ['code'=>1];
    	}
    }

    public function jingpinArticle(){
        if(Request::instance()->isPost()){
            $posrData= Request::instance()->post();

            $insert['user_id'] = $_SESSION['think']['uid'];
            $insert['article_id'] = $posrData['id'];
            $insert['create_time'] = date("Y-m-d H:i:s");;
            $res = Db::table('article_shou')->insert($insert);

            if($res){
                return ['code'=>1];
            }else{
                return ['code'=>0];
            }
        }
    }
    public function editArticle(){
    	if(Request::instance()->isPost()){
    		$posrData= Request::instance()->post();

			$res = Db::table('article')->where('id', $posrData['id'])->update($posrData);

			if($res){
				$this->success("修改成功！",'/Admin/index/index');
	        }else{
				$this->success("修改失败！",$_SERVER['HTTP_REFERER']);
	        }
			die;
    	}
		$id= input('id');

    	$articleInfo = Db::table('article') 
    		->where('id',$id )
			->find();

    	$cateList = Db::table('article_cate')->select();
		return $this->fetch('article_edit',[
			'cateList'=>$cateList,
			'articleInfo'=>$articleInfo
			]);

    }
	/*
	展示列表
	 */
    public function bonus()
    {
    	if(Request::instance()->isPost()){
    		$posrData= Request::instance()->post();
    		foreach ($posrData as $key => $val) {
    			Db::table('basic_config')->where('k',$key)->update(['v'=>$val]);
    		}

    	}
        $bonusList = Db::table('basic_config')->where("type",'web')->select();
        $bonus= Db::table('basic_config')->where("k",'is_kai')->find();

		return $this->fetch('bonus',[
            'bonusList'=>$bonusList,
            'bonus'=>$bonus,
		]);
    }
    /*
	展示列表
	 */
    public function manage()
    {
    	$start_page = isset($_COOKIE["start_page"]) ? $_COOKIE['start_page'] : 0;
    	$page = isset($_COOKIE["page"]) ? $_COOKIE['page'] : 5;

    	$number_1 = Db::table('article')->count();
    	//$number_2 = Db::table('article')->where('content','not null')->count();

    	$number_2 = 0;
		return $this->fetch('manage',[
				'start_page'=>$start_page,
				'page'=>$page,
				'number_1'=>$number_1,
				'number_2'=>$number_2,
			]);
    }
	/*
	根据id查询文章
	 */
    public function getInfo()
    {
    	$id = input("id");
    	$info = Db::table('article') -> where("id",$id)->find();
        $info['content'] = str_replace('<p>　　 </p>','',$info['content']);

		return $this->fetch('info',[
			'info'=>$info
			]);
    }
	/*
	根据id查询详情
	 */
    public function getInfoUrl()
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
		return 1;
    }

    public function index_1(){
        try{
            //设置最大运行时间
            //ini_set('max_execution_time', 60);
            include(APP_PATH.'../QueryList.class.php');
            $list = Db::table('article')->field('id,href,title,length(content) as len')
                ->where("length(content) =65532")
                //->where("title",'like','%爱情公寓%')
                ->fetchSql(0)
                ->limit(5)
                ->select();

            $listCount = Db::table('article')
                ->where("length(content) =65532")
                //->where("title",'like','%爱情公寓%')
                ->fetchSql(0)
                ->count();
            $rslist=[];

            echo "还有".$listCount."个文章没有获取<br>";

            //print_r($list);die;
            if(!empty($list)){
                foreach($list as $key =>$val){
                        echo "id:".$val['id'].",title:".$val['title'].",url=".$val['href'].",文章长度".$val['len']."<br>";
                        //echo intval(substr($val['href'],12,10)) ."<br>";

                        //详情地址
                        //$urlInfo = 'https://www.966eefbcbe74323a.com/htm/novel6/'.intval(substr($val['href'],12,10)).'.htm';
                        $urlInfo = 'https://www.966eefbcbe74323a.com'.$val['href'];

                        //echo $urlInfo;die;
                        $patternInfo = array(
                            "title" => array(".news_details h1,.text-overflow", "html"),
                            "content" => array(".xs-details-content", "html")
                        );
                        $qyInfo = new \QueryList($urlInfo, $patternInfo, '', '', 'utf-8');
                        $rsInfo = $qyInfo->jsonArr;
                        $rsInfo[0]['id']=$val['id'];
                        $rslist[]=$rsInfo[0];
                }
                echo "<br>";
                foreach($rslist as $key =>$val){
                    //print_r($val['id']);die;
                    $updateRes = [];
                    if( isset ($val) ){
                        $updateRes['content'] = $val['content'];
                        //修改内容
                        Db::table('article')->where("id",$val['id'])->update($updateRes);
                        echo "title:".$val['title']."修改成功,文章长度:".strlen($val['content'])."<br>";
                    }
                }
                //$this->index_1(2);
                echo "<script language=JavaScript> location.replace(location.href);</script>";
                die;
            }else{
                echo "没有数据了";
            }
        }catch(RequestException $e){
            echo "Http Error \r\n";
            echo "出错了 刷新";
            //echo "<script language=JavaScript> location.replace(location.href);</script>";
        }
    }
	/*
	批量获取文章内容 默认20条
	 */
    public function getInfoAll()
    {
		include(APP_PATH.'../QueryList.class.php');
        //设置最大运行时间
		ini_set('max_execution_time', 3000);

        try {
            $list = Db::table('article')->field('id,href,title')->fetchSql(0)->where("content",'null')->select();


            $str = "";
            $i=0;
            foreach ($list as $key => $value) {
            	if($i<=50){
            		$i++;
            	}else{
            		break;
            	}
                $urlInfo = $this->allUrl.$value['href'];

                $patternInfo = array(
                    "content" => array(".xs-details-content", "html")
                );
                $qyInfo = new \QueryList($urlInfo, $patternInfo, '', '', 'utf-8');
                $rsInfo = $qyInfo->jsonArr;

                $updateRes = [];

                if( isset ($rsInfo[0]) ){
                    $updateRes['content'] = $rsInfo[0]['content'];;
                    //修改内容
                    Db::table('article')->where("id",$value['id'])->update($updateRes);
                    $str.="id:".$value['id'].";".$value["title"]."获取内容成功<br>";
                }

            }

            $number_1 = Db::table('article')->count();
	    	/*$number_2 = Db::table('article')->where('content','not null')->count();*/
	    	$number_2 = $number_1-count($list);

            return ['code'=>1,'msg'=>$str,'number_1'=>$number_1,'number_2'=>$number_2];

        }catch (\Exception $e) {

            return ['code'=>3,'msg'=>"$e",'number_1'=>0,'number_2'=>0];
        }

    }

	/*
	采集数据
	 */
    public function getALLByPage()
    {
        //设置最大运行时间
		ini_set('max_execution_time', 3000); 
		include(APP_PATH.'../QueryList.class.php');

		//筛选条件
		$pattern = array(
				"title" => array(".box-topic-list li a", "text"),
				"href" => array(".box-topic-list li a", "href")
			);

		//循环页数
		$start_page	= 	input('page_str');
		$page	=	input('page');

		setcookie('start_page', $start_page);
		setcookie('page', $page);

		/*查询所有数据，避免相同标题插入*/
    	$titleList = Db::table('article') ->column('title');

		$insertAll = [];
		$insertCount = 0;
		$code=0;
		$end_page=0;

		//循环采集数据
		for ($i= $start_page; $i <= $start_page+$page; $i++) { 
			# code...
			$url = $this->allUrl."/htm/novellist10/$i.htm";

			$qy = new \QueryList($url, $pattern, '', '', 'utf-8');
			$rs = $qy->jsonArr;

			if(empty( $rs)){
				$code=1;
				$end_page = $i;
				break;
			}
			//抓取详情页
			/*foreach ($rs as $key => $value) {
				$urlInfo = $this->allUrl.$value['href'];

				$patternInfo = array(
					"content" => array(".xs-details-content p", "text")
				);
				$qyInfo = new \QueryList($urlInfo, $patternInfo, '', '', 'utf-8');

				$rsInfo = $qyInfo->jsonArr;

				if (isset ($rsInfo[0] ) ) {
					//赋值内容
					$rs[$key]['content'] = $rsInfo[0]['content'];
				}
			}*/
			foreach ($rs as $key => &$value) {
				$value = implode(' ',$value)[0];
				//存在则销毁，避免重复title出现
				if(in_array($value['title'], $titleList)){
					unset($rs[$key]);
				}else{
					$value['create_time'] = date("Y-m-d H:i:s");
					$value['cate_id'] = 4;
				}
			}
			print_r($rs);die;
			$insertCount += count($rs);
	    	// table方法必须指定完整的数据表名
			Db::table('article')->insertAll($rs);

		}
		if($code==1){
    		return ['code'=>2,'msg'=>$end_page ];
		}else{
    		return ['code'=>1,'msg'=>$insertCount ];
		}

    }
}
