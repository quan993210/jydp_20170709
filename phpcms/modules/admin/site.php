<?php
defined('IN_PHPCMS') or exit('No permission resources.');
//模型原型存储路径
define('MODEL_PATH',PC_PATH.'modules'.DIRECTORY_SEPARATOR.'content'.DIRECTORY_SEPARATOR.'fields'.DIRECTORY_SEPARATOR);
//模型缓存路径
define('CACHE_MODEL_PATH',CACHE_PATH.'caches_model'.DIRECTORY_SEPARATOR.'caches_data'.DIRECTORY_SEPARATOR);
pc_base::load_app_class('admin', 'admin', 0);
class site extends admin {
	private $db;
	public function __construct() {
		$this->db = pc_base::load_model('site_model');
		parent::__construct();
	}
	
	public function init() {
		$total = $this->db->count();
		$page = isset($_GET['page']) && intval($_GET['page']) ? intval($_GET['page']) : 1;
		$pagesize = 20;
		$offset = ($page - 1) * $pagesize;
		$list = $this->db->select('', '*', $offset.','.$pagesize);
		$pages = pages($total, $page, $pagesize);
		$show_dialog = true;
		$big_menu = array('javascript:window.top.art.dialog({id:\'add\',iframe:\'?m=admin&c=site&a=add\', title:\''.L('add_site').'\', width:\'700\', height:\'500\', lock:true}, function(){var d = window.top.art.dialog({id:\'add\'}).data.iframe;var form = d.document.getElementById(\'dosubmit\');form.click();return false;}, function(){window.top.art.dialog({id:\'add\'}).close()});void(0);', L('add_site'));
		include $this->admin_tpl('site_list');
	}
	
	public function add() {
		header("Cache-control: private"); 
		if (isset($_GET['show_header'])) $show_header = 1;
		if (isset($_POST['dosubmit'])) {
			$name = isset($_POST['name']) && trim($_POST['name']) ? trim($_POST['name']) : showmessage(L('site_name').L('empty'));
			$dirname = isset($_POST['dirname']) && trim($_POST['dirname']) ? strtolower(trim($_POST['dirname'])) : showmessage(L('site_dirname').L('empty'));
			$domain = isset($_POST['domain']) && trim($_POST['domain']) ? trim($_POST['domain']) : '';
			$site_title = isset($_POST['site_title']) && trim($_POST['site_title']) ? trim($_POST['site_title']) : '';
			$keywords = isset($_POST['keywords']) && trim($_POST['keywords']) ? trim($_POST['keywords']) : '';
			$description = isset($_POST['description']) && trim($_POST['description']) ? trim($_POST['description']) : '';
			$release_point = isset($_POST['release_point']) ? $_POST['release_point'] : '';
			$template = isset($_POST['template']) && !empty($_POST['template']) ? $_POST['template'] : showmessage(L('please_select_a_style'));
			$default_style = isset($_POST['default_style']) && !empty($_POST['default_style']) ? $_POST['default_style'] : showmessage(L('please_choose_the_default_style'));			   
			if ($this->db->get_one(array('name'=>$name), 'siteid')) {
				showmessage(L('site_name').L('exists'));
			} 
			if (!preg_match('/^\\w+$/i', $dirname)) {
				showmessage(L('site_dirname').L('site_dirname_err_msg'));
			}
			if ($this->db->get_one(array('dirname'=>$dirname), 'siteid')) {
				showmessage(L('site_dirname').L('exists'));
			}
			if (!empty($domain) && !preg_match('/http:\/\/(.+)\/$/i', $domain)) {
				showmessage(L('site_domain').L('site_domain_ex2'));
			}
			if (!empty($domain) && $this->db->get_one(array('domain'=>$domain), 'siteid')) {
				showmessage(L('site_domain').L('exists'));
			}
			if (!empty($release_point) && is_array($release_point)) {
				if (count($release_point) > 4) {
					showmessage(L('release_point_configuration').L('most_choose_four'));
				}
				$s = '';
				foreach ($release_point as $key=>$val) {
					if($val) $s.= $s ? ",$val" : $val;
				}
				$release_point = $s;
				unset($s);
			} else {
				$release_point = '';
			}
			if (!empty($template) && is_array($template)) {
				$template = implode(',', $template);
			} else {
				$template = '';
			}
			$_POST['setting']['watermark_img'] = IMG_PATH.'water/'.$_POST['setting']['watermark_img'];
			$setting = trim(array2string($_POST['setting']));
			if ($new_siteid = $this->db->insert(array('name'=>$name,'dirname'=>$dirname, 'domain'=>$domain, 'site_title'=>$site_title, 'keywords'=>$keywords, 'description'=>$description, 'release_point'=>$release_point, 'template'=>$template,'setting'=>$setting, 'default_style'=>$default_style),true)) {
				$class_site = pc_base::load_app_class('sites');
				$class_site->set_cache();
				if($_POST['is_copy'] ==1){
					$this->copy_master_station($new_siteid);
				}

				showmessage(L('operation_success'), '?m=admin&c=site&a=init', '', 'add');
			} else {
				showmessage(L('operation_failure'));
			}
		} else {
			$release_point_db = pc_base::load_model('release_point_model');
			$release_point_list = $release_point_db->select('', 'id, name');
			$show_validator = $show_scroll = $show_header = true;
			$template_list = template_list();
			include $this->admin_tpl('site_add');
		}
	}
	
	public function del() {
		$siteid = isset($_GET['siteid']) && intval($_GET['siteid']) ? intval($_GET['siteid']) : showmessage(L('illegal_parameters'), HTTP_REFERER);
		if($siteid==1) showmessage(L('operation_failure'), HTTP_REFERER);
		if ($this->db->get_one(array('siteid'=>$siteid))) {
			if ($this->db->delete(array('siteid'=>$siteid))) {
				$class_site = pc_base::load_app_class('sites');
				$class_site->set_cache();
				showmessage(L('operation_success'), HTTP_REFERER);
			} else {
				showmessage(L('operation_failure'), HTTP_REFERER);
			}
		} else {
			showmessage(L('notfound'), HTTP_REFERER);
		}
	}
	
	public function edit() {
		$siteid = isset($_GET['siteid']) && intval($_GET['siteid']) ? intval($_GET['siteid']) : showmessage(L('illegal_parameters'), HTTP_REFERER);
		if ($data = $this->db->get_one(array('siteid'=>$siteid))) {
			if (isset($_POST['dosubmit'])) {
				$name = isset($_POST['name']) && trim($_POST['name']) ? trim($_POST['name']) : showmessage(L('site_name').L('empty'));
				$dirname = isset($_POST['dirname']) && trim($_POST['dirname']) ? strtolower(trim($_POST['dirname'])) : ($siteid == 1 ? '' :showmessage(L('site_dirname').L('empty')));
				$domain = isset($_POST['domain']) && trim($_POST['domain']) ? trim($_POST['domain']) : '';
				$site_title = isset($_POST['site_title']) && trim($_POST['site_title']) ? trim($_POST['site_title']) : '';
				$keywords = isset($_POST['keywords']) && trim($_POST['keywords']) ? trim($_POST['keywords']) : '';
				$description = isset($_POST['description']) && trim($_POST['description']) ? trim($_POST['description']) : '';
				$release_point = isset($_POST['release_point']) ? $_POST['release_point'] : '';
				$template = isset($_POST['template']) && !empty($_POST['template']) ? $_POST['template'] : showmessage(L('please_select_a_style'));
				$default_style = isset($_POST['default_style']) && !empty($_POST['default_style']) ? $_POST['default_style'] : showmessage(L('please_choose_the_default_style'));	
				if ($data['name'] != $name && $this->db->get_one(array('name'=>$name), 'siteid')) {
					showmessage(L('site_name').L('exists'));
				}
				if ($siteid != 1) {
					if (!preg_match('/^\\w+$/i', $dirname)) {
						showmessage(L('site_dirname').L('site_dirname_err_msg'));
					}
					if ($data['dirname'] != $dirname && $this->db->get_one(array('dirname'=>$dirname), 'siteid')) {
						showmessage(L('site_dirname').L('exists'));
					}
				} 
				
				if (!empty($domain) && !preg_match('/http:\/\/(.+)\/$/i', $domain)) {
					showmessage(L('site_domain').L('site_domain_ex2'));
				}
				if (!empty($domain) && $data['domain'] != $domain && $this->db->get_one(array('domain'=>$domain), 'siteid')) {
					showmessage(L('site_domain').L('exists'));
				}
				if (!empty($release_point) && is_array($release_point)) {
					if (count($release_point) > 4) {
						showmessage(L('release_point_configuration').L('most_choose_four'));
					}
					$s = '';
					foreach ($release_point as $key=>$val) {
						if($val) $s.= $s ? ",$val" : $val;
					}
					$release_point = $s;
					unset($s);
				} else {
					$release_point = '';
				}
				if (!empty($template) && is_array($template)) {
					$template = implode(',', $template);
				} else {
					$template = '';
				}
				$_POST['setting']['watermark_img'] = 'statics/images/water/'.$_POST['setting']['watermark_img'];
				$setting = trim(array2string($_POST['setting']));
				$sql = array('name'=>$name,'dirname'=>$dirname, 'domain'=>$domain, 'site_title'=>$site_title, 'keywords'=>$keywords, 'description'=>$description, 'release_point'=>$release_point, 'template'=>$template, 'setting'=>$setting, 'default_style'=>$default_style);
				if ($siteid == 1) unset($sql['dirname']);
				if ($this->db->update($sql, array('siteid'=>$siteid))) {
					$class_site = pc_base::load_app_class('sites');
					$class_site->set_cache();
					showmessage(L('operation_success'), '', '', 'edit');
				} else {
					showmessage(L('operation_failure'));
				}
			} else {
				$show_validator = true;
				$show_header = true;
				$show_scroll = true;
				$template_list = template_list();
				$setting = string2array($data['setting']);
				$setting['watermark_img'] = str_replace('statics/images/water/','',$setting['watermark_img']);
				$release_point_db = pc_base::load_model('release_point_model');
				$release_point_list = $release_point_db->select('', 'id, name');
				include $this->admin_tpl('site_edit');
			}
		} else {
			showmessage(L('notfound'), HTTP_REFERER);
		}
	}
	
	public function public_name() {
		$name = isset($_GET['name']) && trim($_GET['name']) ? (pc_base::load_config('system', 'charset') == 'gbk' ? iconv('utf-8', 'gbk', trim($_GET['name'])) : trim($_GET['name'])) : exit('0');
		$siteid = isset($_GET['siteid']) && intval($_GET['siteid']) ? intval($_GET['siteid']) : '';
 		$data = array();
		if ($siteid) {
			
			$data = $this->db->get_one(array('siteid'=>$siteid), 'name');
			if (!empty($data) && $data['name'] == $name) {
				exit('1');
			}
		}
		if ($this->db->get_one(array('name'=>$name), 'siteid')) {
			exit('0');
		} else {
			exit('1');
		}
	}
	
	public function public_dirname() {
		$dirname = isset($_GET['dirname']) && trim($_GET['dirname']) ? (pc_base::load_config('system', 'charset') == 'gbk' ? iconv('utf-8', 'gbk', trim($_GET['dirname'])) : trim($_GET['dirname'])) : exit('0');
		$siteid = isset($_GET['siteid']) && intval($_GET['siteid']) ? intval($_GET['siteid']) : '';
		$data = array();
		if ($siteid) {
			$data = $this->db->get_one(array('siteid'=>$siteid), 'dirname');
			if (!empty($data) && $data['dirname'] == $dirname) {
				exit('1');
			}
		}
		if ($this->db->get_one(array('dirname'=>$dirname), 'siteid')) {
			exit('0');
		} else {
			exit('1');
		}
	}

	private function check_gd() {
		if(!function_exists('imagepng') && !function_exists('imagejpeg') && !function_exists('imagegif')) {
			$gd = L('gd_unsupport');
		} else {
			$gd = L('gd_support');
		}
		return $gd;
	}


	/**
	 * 添加分站复制主站
	 * @param integer $siteid 分站站点ID
	 */
	public function copy_master_station($siteid){
		if(!$siteid){
			return false;
		}
		//复制模型start
		$this->copy_model($siteid);
		//复制栏目start
		$this->copy_category($siteid);
		//复制广告版位start
		$this->copy_space($siteid);
		//更新缓存start
		$this->update_cache();
		return true;

	}

	//复制模型
	private function copy_model($siteid){
		$sitemodel_db = pc_base::load_model('sitemodel_model');
		$copy_record_db = pc_base::load_model('copy_record_model');
		$model = get_sitemodel();
		foreach($model as $key=>$val){
			$old_modelid = $val['id'];
			$sitemodel = $sitemodel_db->get_one(array('siteid'=>1,'modelid'=>$val['id']));
			$info = array();
			$info['name'] = $val['modelname'];
			//主表表名
			$basic_table = $info['tablename'] = $val['tablename'].'_'.$siteid;
			//从表表名
			$table_data = $basic_table.'_data';
			$info['description'] = "";
			$info['type'] = 0;
			$info['siteid'] = $siteid;
			$info['default_style'] = $sitemodel['default_style'];
			$info['category_template'] = $sitemodel['category_template'];
			$info['list_template'] = $sitemodel['list_template'];
			$info['show_template'] = $sitemodel['show_template'];
			//主站模型文件
			$model_import = @file_get_contents('model/'.$val['tablename'].'.model');
			if(!empty($model_import)) {
				$model_import_data = string2array($model_import);
			}
			$is_exists = $sitemodel_db->table_exists($basic_table);
			if($is_exists){
				return "该站点已经添加".$val['modelname']."模型";
			}
			$modelid = $sitemodel_db->insert($info, 1);
			if($modelid){
				$tablepre = $sitemodel_db->db_tablepre;
				//建立数据表
				$model_sql = file_get_contents(MODEL_PATH.'model.sql');
				$model_sql = str_replace('$basic_table', $tablepre.$basic_table, $model_sql);
				$model_sql = str_replace('$table_data',$tablepre.$table_data, $model_sql);
				$model_sql = str_replace('$table_model_field',$tablepre.'model_field', $model_sql);
				$model_sql = str_replace('$modelid',$modelid,$model_sql);
				$model_sql = str_replace('$siteid',$siteid,$model_sql);
				$sitemodel_db->sql_execute($model_sql);

				if(!empty($model_import_data)) {
					$sitemodel_field_db = pc_base::load_model('sitemodel_field_model');
					$system_field = array('title','style','catid','url','listorder','status','userid','username','inputtime','updatetime','pages','readpoint','template','groupids_view','posids','content','keywords','description','thumb','typeid','relation','islink','allow_comment');
					foreach($model_import_data as $v) {
						$field = $v['field'];
						if(in_array($field,$system_field)) {
							$v['siteid'] = $siteid;
							unset($v['fieldid'],$v['modelid'],$v['field']);
							$v = new_addslashes($v);
							$v['setting'] = array2string($v['setting']);

							$sitemodel_field_db->update($v,array('modelid'=>$modelid,'field'=>$field));
						} else {
							$tablename = $v['issystem'] ? $tablepre.$basic_table : $tablepre.$table_data;
							//重组模型表字段属性

							$minlength = $v['minlength'] ? $v['minlength'] : 0;
							$maxlength = $v['maxlength'] ? $v['maxlength'] : 0;
							$field_type = $v['formtype'];
							require MODEL_PATH.$field_type.DIRECTORY_SEPARATOR.'config.inc.php';
							if(isset($v['setting']['fieldtype'])) {
								$field_type = $v['setting']['fieldtype'];
							}
							require MODEL_PATH.'add.sql.php';
							$v['tips'] = addslashes($v['tips']);
							$v['formattribute'] = addslashes($v['formattribute']);

							$v['setting'] = array2string($v['setting']);
							$v['modelid'] = $modelid;
							$v['siteid'] = $siteid;
							unset($v['fieldid']);

							$sitemodel_field_db->insert($v);
						}
					}
				}
				//添加模型复制记录
				$copy_record_db->insert(array('parentid'=>$old_modelid,'copyid'=>$modelid,'siteid'=>$siteid,'type'=>1));
			}
		}
		$this->public_model_cache();

	}

	//复制栏目
	private function copy_category($siteid){
		$copy_record_db = pc_base::load_model('copy_record_model');
		$category_db = pc_base::load_model('category_model');
		$where ="siteid=1 and modelid > 0";
		$category = $category_db->select($where,'*','','catid asc');
		$setting = get_setting();

		foreach($category as $key=>$val){
			$old_catid = $val['catid'];
			$info = $val;
			$copy_model_record = $copy_record_db->get_one(array('parentid'=>$val['modelid'],'siteid'=>$siteid,'type'=>1));
			pc_base::load_sys_func('iconv');
			unset($info['catid']);
			$info['siteid'] = $siteid;

			$info['modelid'] = $copy_model_record['copyid'];
			if($val['parentid'] != 0){
				$copy_category_record = $copy_record_db->get_one(array('parentid'=>$val['parentid'],'siteid'=>$siteid,'type'=>2));
				$info['parentid'] = $copy_category_record['copyid'];
			}

			$info['arrparentid'] = "";
			$info['child'] = "";
			$info['arrchildid'] = "";
			$info['setting'] = array2string($setting);
			$info['sethtml'] = "0";
			$info['listorder'] = "";
			$info['items'] = "";
			$info['url'] = '';

			$catid = $category_db->insert($info, true);
			//添加栏目复制记录
			$copy_record_db->insert(array('parentid'=>$old_catid,'copyid'=>$catid,'siteid'=>$siteid,'type'=>2));

			//栏目复制成功后添加栏目api_site配置id
			$data = array();
			$data['siteid'] = $siteid;
			$data['model'] = 1;
			$data['api_site_id'] = $catid;
			if($old_catid == 9){ //主站精彩栏目id
				$data['type'] = 1;
				if(!insertApiSiteid($data)){
					showmessage('添加配置表错误');
				}
			}elseif($old_catid == 10){//主站精选栏目id
				$data['type'] = 2;
				if(!insertApiSiteid($data)){
					showmessage('添加配置表错误');
				}
			}elseif($old_catid == 11){//主站潮派栏目id
				$data['type'] = 3;
				if(!insertApiSiteid($data)){
					showmessage('添加配置表错误');
				}
			}
		}
		$this->public_category_cache($siteid);
		return true;

	}

	//复制广告版位
	private function copy_space($siteid){
		$db_poster_space = pc_base::load_model('poster_space_model');
		$poster_space = $db_poster_space->select("spaceid in(11,12,13)");
		foreach($poster_space as $sp=>$spv){
			$old_spaceid = $spv['spaceid'];
			unset($spv['spaceid']);
			$spv['siteid'] = $siteid;
			$spaceid = $db_poster_space->insert($spv,1);
			$data = array();
			$data['siteid'] = $siteid;
			$data['model'] = 2;
			$data['api_site_id'] = $spaceid;
			//广告版位复制成功后添加广告api_site配置id
			if($old_spaceid == 11){ //主站精彩广告id
				$data['type'] = 1;
				if(!insertApiSiteid($data)){
					showmessage('添加配置表错误');
				}
			}elseif($old_spaceid == 12){//主站潮派广告id
				$data['type'] = 3;
				if(!insertApiSiteid($data)){
					showmessage('添加配置表错误');
				}
			}elseif($old_spaceid == 13){//主站精选广告id
				$data['type'] = 2;
				if(!insertApiSiteid($data)){
					showmessage('添加配置表错误');
				}
			}
		}
		return true;
	}

	//复制完成更新缓存
	private function update_cache(){
		$modules = array(
				array('name' => L('module'), 'function' => 'module'),
				array('name' => L('sites'), 'mod' => 'admin', 'file' => 'sites', 'function' => 'set_cache'),
				array('name' => L('category'), 'function' => 'category'),
				//array('name' => L('downservers'), 'function' => 'downservers'),
				//array('name' => L('badword_name'), 'function' => 'badword'),
				//array('name' => L('ipbanned'), 'function' => 'ipbanned'),
				//array('name' => L('keylink'), 'function' => 'keylink'),
				//array('name' => L('linkage'), 'function' => 'linkage'),
				array('name' => L('position'), 'function' => 'position'),
				//array('name' => L('admin_role'), 'function' => 'admin_role'),
				//array('name' => L('urlrule'), 'function' => 'urlrule'),
				array('name' => L('sitemodel'), 'function' => 'sitemodel'),
				array('name' => L('type'), 'function' => 'type', 'param' => 'content'),
				//array('name' => L('workflow'), 'function' => 'workflow'),
				//array('name' => L('dbsource'), 'function' => 'dbsource'),
				//array('name' => L('member_setting'), 'function' => 'member_setting'),
				//array('name' => L('member_group'), 'function' => 'member_group'),
				//array('name' => L('membermodel'), 'function' => 'membermodel'),
				//array('name' => L('member_model_field'), 'function' => 'member_model_field'),
				//array('name' => L('search_type'), 'function' => 'type', 'param' => 'search'),
				//array('name' => L('search_setting'), 'function' => 'search_setting'),
				//array('name' => L('update_vote_setting'), 'function' => 'vote_setting'),
				//array('name' => L('update_link_setting'), 'function' => 'link_setting'),
				//array('name' => L('special'), 'function' => 'special'),
				array('name' => L('setting'), 'function' => 'setting'),
				array('name' => L('database'), 'function' => 'database'),
				//array('name' => L('update_formguide_model'), 'mod' => 'formguide', 'file' => 'formguide', 'function' => 'public_cache'),
				array('name' => L('cache_file'), 'function' => 'copy_cache2database'),
				//array('name' => L('cache_copyfrom'), 'function' => 'copyfrom'),
				//array('name' => L('clear_files'), 'function' => 'del_file'),
				//array('name' => L('video_category_tb'), 'function' => 'video_category_tb'),
		);
		$num = count($modules);
		$this->cache_api = pc_base::load_app_class('cache_api', 'admin');
		for($i=0;$i<$num;$i++){
			$m = $modules[$i];
			if ($m['mod'] && $m['function']) {
				if ($m['file'] == '') $m['file'] = $m['function'];
				$M = getcache('modules', 'commons');
				if (in_array($m['mod'], array_keys($M))) {
					$cache = pc_base::load_app_class($m['file'], $m['mod']);
					$cache->{$m['function']}();
				}
			}else {
				$this->cache_api->cache($m['function'], $m['param']);
			}
		}
		return true;
	}


	/**
	 * 更新模型缓存
	 */
	public function public_model_cache() {
		$sitemodel_db = pc_base::load_model('sitemodel_model');
		require MODEL_PATH.'fields.inc.php';
		//更新内容模型类：表单生成、入库、更新、输出
		$classtypes = array('form','input','update','output');
		foreach($classtypes as $classtype) {
			$cache_data = file_get_contents(MODEL_PATH.'content_'.$classtype.'.class.php');
			$cache_data = str_replace('}?>','',$cache_data);
			foreach($fields as $field=>$fieldvalue) {
				if(file_exists(MODEL_PATH.$field.DIRECTORY_SEPARATOR.$classtype.'.inc.php')) {
					$cache_data .= file_get_contents(MODEL_PATH.$field.DIRECTORY_SEPARATOR.$classtype.'.inc.php');
				}
			}
			$cache_data .= "\r\n } \r\n?>";
			file_put_contents(CACHE_MODEL_PATH.'content_'.$classtype.'.class.php',$cache_data);
			@chmod(CACHE_MODEL_PATH.'content_'.$classtype.'.class.php',0777);
		}
		//更新模型数据缓存
		$model_array = array();
		$datas = $sitemodel_db->select(array('type'=>0));
		foreach ($datas as $r) {
			if(!$r['disabled']) $model_array[$r['modelid']] = $r;
		}
		setcache('model', $model_array, 'commons');
		return true;
	}


	/**
	 * 更新缓存并修复栏目
	 */
	public function public_category_cache($siteid) {
		$this->repair($siteid);
		$this->cache($siteid);
		return true;
	}
	/**
	 * 修复栏目数据
	 */
	private function repair($siteid) {
		$category_db = pc_base::load_model('category_model');
		pc_base::load_sys_func('iconv');
		@set_time_limit(600);
		$html_root = pc_base::load_config('system','html_root');
		$this->categorys = $categorys = array();
		$this->categorys = $categorys = $category_db->select(array('siteid'=>$siteid,'module'=>'content'), '*', '', 'listorder ASC, catid ASC', '', 'catid');

		$this->get_categorys($categorys);
		if(is_array($this->categorys)) {
			foreach($this->categorys as $catid => $cat) {
				if($cat['type'] == 2) continue;
				$arrparentid = $this->get_arrparentid($catid);
				$setting = string2array($cat['setting']);
				$arrchildid = $this->get_arrchildid($catid);
				$child = is_numeric($arrchildid) ? 0 : 1;
				if($categorys[$catid]['arrparentid']!=$arrparentid || $categorys[$catid]['arrchildid']!=$arrchildid || $categorys[$catid]['child']!=$child) $category_db->update(array('arrparentid'=>$arrparentid,'arrchildid'=>$arrchildid,'child'=>$child),array('catid'=>$catid));

				$parentdir = $this->get_parentdir($catid);
				$catname = $cat['catname'];
				$letters = gbk_to_pinyin($catname);
				$letter = strtolower(implode('', $letters));
				$listorder = $cat['listorder'] ? $cat['listorder'] : $catid;

				$this->sethtml = $setting['create_to_html_root'];
				//检查是否生成到根目录
				$this->get_sethtml($catid);
				$sethtml = $this->sethtml ? 1 : 0;

				if($setting['ishtml']) {
					//生成静态时
					$url = $this->update_url($catid);
					if(!preg_match('/^(http|https):\/\//i', $url)) {
						$url = $sethtml ? '/'.$url : $html_root.'/'.$url;
					}
				} else {
					//不生成静态时
					$url = $this->update_url($catid);
					$url = APP_PATH.$url;
				}
				if($cat['url']!=$url) $category_db->update(array('url'=>$url), array('catid'=>$catid));



				if($categorys[$catid]['parentdir']!=$parentdir || $categorys[$catid]['sethtml']!=$sethtml || $categorys[$catid]['letter']!=$letter || $categorys[$catid]['listorder']!=$listorder) $category_db->update(array('parentdir'=>$parentdir,'sethtml'=>$sethtml,'letter'=>$letter,'listorder'=>$listorder), array('catid'=>$catid));
			}
		}

		//删除在非正常显示的栏目
		foreach($this->categorys as $catid => $cat) {
			if($cat['parentid'] != 0 && !isset($this->categorys[$cat['parentid']])) {
				$category_db->delete(array('catid'=>$catid));
			}
		}
		return true;
	}
	/**
	 * 更新栏目缓存
	 */
	public function cache($siteid) {
		$category_db = pc_base::load_model('category_model');
		$categorys = array();
		$models = getcache('model','commons');
		foreach ($models as $modelid=>$model) {
			$datas = $category_db->select(array('modelid'=>$modelid),'catid,type,items',10000);
			$array = array();
			foreach ($datas as $r) {
				if($r['type']==0) $array[$r['catid']] = $r['items'];
			}
			setcache('category_items_'.$modelid, $array,'commons');
		}
		$array = array();
		$categorys = $category_db->select('`module`=\'content\'','catid,siteid',20000,'listorder ASC');
		foreach ($categorys as $r) {
			$array[$r['catid']] = $r['siteid'];
		}
		setcache('category_content',$array,'commons');
		$categorys = $this->categorys = array();
		$this->categorys = $category_db->select(array('siteid'=>$siteid, 'module'=>'content'),'*',10000,'listorder ASC');
		foreach($this->categorys as $r) {
			unset($r['module']);
			$setting = string2array($r['setting']);
			$r['create_to_html_root'] = $setting['create_to_html_root'];
			$r['ishtml'] = $setting['ishtml'];
			$r['content_ishtml'] = $setting['content_ishtml'];
			$r['category_ruleid'] = $setting['category_ruleid'];
			$r['show_ruleid'] = $setting['show_ruleid'];
			$r['workflowid'] = $setting['workflowid'];
			$r['isdomain'] = '0';
			if(!preg_match('/^(http|https):\/\//', $r['url'])) {
				$r['url'] = siteurl($r['siteid']).$r['url'];
			} elseif ($r['ishtml']) {
				$r['isdomain'] = '1';
			}
			$categorys[$r['catid']] = $r;
		}
		setcache('category_content_'.$siteid,$categorys,'commons');
		return true;
	}
	/**
	 * 找出子目录列表
	 * @param array $categorys
	 */
	private function get_categorys($categorys = array()) {
		if (is_array($categorys) && !empty($categorys)) {
			foreach ($categorys as $catid => $c) {
				$this->categorys[$catid] = $c;
				$result = array();
				foreach ($this->categorys as $_k=>$_v) {
					if($_v['parentid']) $result[] = $_v;
				}
				$this->get_categorys($r);
			}
		}
		return true;
	}
	/**
	 * 获取父栏目ID列表
	 * @param integer $catid              栏目ID
	 * @param array $arrparentid          父目录ID
	 * @param integer $n                  查找的层次
	 */
	private function get_arrparentid($catid, $arrparentid = '', $n = 1) {
		if($n > 5 || !is_array($this->categorys) || !isset($this->categorys[$catid])) return false;
		$parentid = $this->categorys[$catid]['parentid'];
		$arrparentid = $arrparentid ? $parentid.','.$arrparentid : $parentid;
		if($parentid) {
			$arrparentid = $this->get_arrparentid($parentid, $arrparentid, ++$n);
		} else {
			$this->categorys[$catid]['arrparentid'] = $arrparentid;
		}
		$parentid = $this->categorys[$catid]['parentid'];
		return $arrparentid;
	}

	/**
	 * 获取子栏目ID列表
	 * @param $catid 栏目ID
	 */
	private function get_arrchildid($catid) {
		$arrchildid = $catid;
		if(is_array($this->categorys)) {
			foreach($this->categorys as $id => $cat) {
				if($cat['parentid'] && $id != $catid && $cat['parentid']==$catid) {
					$arrchildid .= ','.$this->get_arrchildid($id);
				}
			}
		}
		return $arrchildid;
	}
	/**
	 * 获取父栏目路径
	 * @param  $catid
	 */
	function get_parentdir($catid) {
		$category_db = pc_base::load_model('category_model');
		if($this->categorys[$catid]['parentid']==0) return '';
		$r = $this->categorys[$catid];
		$setting = string2array($r['setting']);
		$url = $r['url'];
		$arrparentid = $r['arrparentid'];
		unset($r);
		if (strpos($url, '://')===false) {
			if ($setting['creat_to_html_root']) {
				return '';
			} else {
				$arrparentid = explode(',', $arrparentid);
				$arrcatdir = array();
				foreach($arrparentid as $id) {
					if($id==0) continue;
					$arrcatdir[] = $this->categorys[$id]['catdir'];
				}
				return implode('/', $arrcatdir).'/';
			}
		} else {
			if ($setting['create_to_html_root']) {
				if (preg_match('/^((http|https):\/\/)?([^\/]+)/i', $url, $matches)) {
					$url = $matches[0].'/';
					$rs = $category_db->get_one(array('url'=>$url), '`parentdir`,`catid`');
					if ($catid == $rs['catid']) return '';
					else return $rs['parentdir'];
				} else {
					return '';
				}
			} else {
				$arrparentid = explode(',', $arrparentid);
				$arrcatdir = array();
				krsort($arrparentid);
				foreach ($arrparentid as $id) {
					if ($id==0) continue;
					$arrcatdir[] = $this->categorys[$id]['catdir'];
					if ($this->categorys[$id]['parentdir'] == '') break;
				}
				krsort($arrcatdir);
				return implode('/', $arrcatdir).'/';
			}
		}
	}
	/**
	 * 获取父栏目是否生成到根目录
	 */
	private function get_sethtml($catid) {
		foreach($this->categorys as $id => $cat) {
			if($catid==$id) {
				$parentid = $cat['parentid'];
				if($this->categorys[$parentid]['sethtml']) {
					$this->sethtml = 1;
				}
				if($parentid) {
					$this->get_sethtml($parentid);
				}
			}
		}
	}
	/**
	 * 更新栏目链接地址
	 */
	private function update_url($catid) {
		$catid = intval($catid);
		if (!$catid) return false;
		$url = pc_base::load_app_class('url', 'content'); //调用URL实例
		return $url->category_url($catid);
	}
}