<?phpclass rcl_addons{    public function __construct() {		add_action('init', array(&$this, 'update_status_addon_recall_activate')); 		add_action('init', array(&$this, 'update_options_rcl_activate'));		add_action('init', array(&$this, 'upload_addon_recall_activate'));		add_action('admin_init', array(&$this, 'update_group_addon_recall_activate'));		add_action('admin_menu', array(&$this, 'wp_recall_addons_panel'),20);		add_action('init', array(&$this, 'get_include_activate_addons_recall'),1);    }		function wp_recall_addons_panel(){		add_submenu_page( 'manage-wprecall', __('Add-on менеджер','rcl'), __('Add-on менеджер','rcl'), 'edit_plugins', 'manage-addon-recall', array( $this, 'recall_addon_manage'));				}		function get_include_activate_addons_recall(){		global $active_addons;		$active_addons = get_site_option('active_addons_recall');                $path_addon_rcl = RCL_PATH.'add-on/';                 $path_addon_theme = TEMPLATEPATH.'/recall/add-on/'; 		foreach((array)$active_addons as $key=>$addon){                    if(!$addon) continue;                    if(file_exists($path_addon_theme.$key.'/index.php')){			                        include_once($path_addon_theme.$key.'/index.php');                    }else if(file_exists($path_addon_rcl.$key.'/index.php')){                        include_once($path_addon_rcl.$key.'/index.php');                    }else{                        unset($active_addons[$key]);                    }		}	}		function actual_url_rcl(){		$wpurl = get_bloginfo('wpurl');		$wpurl = explode('/',$wpurl);		$cnd = count($wpurl);		if($wpurl[$cnd]){ 			$dom = '/'.$wpurl[$cnd];		}else{			$s = count(explode('.',$wpurl[$cnd-1]));			if($s==1) $dom = '/'.$wpurl[$cnd-1];		}		$dir = $_SERVER['DOCUMENT_ROOT'].$dom;		return $dir;	}	function recall_addon_manage(){		global $active_addons;		//$dir   = RCL_PATH.'add-on';                                $paths = array(RCL_PATH.'add-on',TEMPLATEPATH.'/recall/add-on') ;                                 foreach($paths as $path){                    if(file_exists($path)){                        $addons = scandir($path,1);                        $a=0;                        foreach((array)$addons as $namedir){                                $addon_dir = $path.'/'.$namedir;                                $index_src = $addon_dir.'/index.php';                                if(!file_exists($index_src)) continue;                                $info_src = $addon_dir.'/info.txt';                                if(file_exists($info_src)){                                        $info = file($info_src);	                                        $addons_data[$namedir] = $this->get_parse_addon_info($info);                                        $addons_data[$namedir]['src'] = $index_src;                                        $a++;                                        flush();                                }		                        }                    }                }		$cnt_all = count($addons_data);		$cnt_act = count($active_addons);		$cnt_inact = $cnt_all - $cnt_act;				$table = '<div class="wrap">			<div id="icon-plugins" class="icon32"><br></div>			<h2>'.__('Add-ons Wp-Recall','rcl').'</h2>';			if(isset($_GET['update-addon'])){				switch($_GET['update-addon']){					case 'activate': $text_notice = __('Дополнение <strong>активировано</strong>. Возможно, что на странице настроек Wp-Recall появились новые настройки.','rcl'); $type='updated'; break;					case 'deactivate': $text_notice = __('Дополнение <strong>деактивировано</strong>.','rcl'); $type='updated'; break;					case 'delete': $text_notice = __('Файлы и данные дополнения были <strong>удалены</strong>.','rcl'); $type='updated'; break;					case 'error-info': $text_notice = __('Дополнение не было загружено. У дополнения отсутствует корректный заголовок.','rcl'); $type='error'; break;				}				$this->get_update_scripts_file_rcl();				$this->get_update_scripts_footer_rcl();				minify_style_rcl();				$table .='<div id="message" class="'.$type.'"><p>'.$text_notice.'</p></div>';			}						$table .= '			<h4>'.__('Установить дополнение к Wp-Recall в формате .zip','rcl').'</h4>			<p class="install-help">'.__('Если у вас есть архив дополнения для wp-recall в формате .zip, здесь можно загрузить и установить его.','rcl').'</p>			<form class="wp-upload-form" action="/" enctype="multipart/form-data" method="post">							<label class="screen-reader-text" for="addonzip">'.__('Архив плагина','rcl').'</label>				<input id="addonzip" type="file" name="addonzip">				<input id="install-plugin-submit" class="button" type="submit" value="'.__('Установить','rcl').'" name="install-addon-submit">				'.wp_nonce_field('install-addons-rcl','_wpnonce',true,false).'			</form>			<ul class="subsubsub">				<li class="all"><b>'.__('Все','rcl').'<span class="count">('.$cnt_all.')</span></b>|</li>				<li class="active"><b>'.__('Активные','rcl').'<span class="count">('.$cnt_act.')</span></b>|</li>				<li class="inactive"><b>'.__('Неактивные','rcl').'<span class="count">('.$cnt_inact.')</span></b></li>			</ul>			<form action="" method="post">				'.wp_nonce_field('action-addons','_wpnonce',true,false).'				<input type="hidden" value="active" name="plugin_status">				<input type="hidden" value="1" name="paged">				<div class="tablenav top">';					$table .= '<div class="alignleft actions bulkactions">						<select name="group-addon-action">							<option selected="selected" value="">Действия</option>							<option value="deactivate">Деактивировать</option>							<option value="activate">Активировать</option>						</select>						<input id="doaction" class="button action" type="submit" value="Применить" name="">					</div>';				$table .= '</div>				<table class="wp-list-table widefat plugins" cellspacing="0">				<thead>					<tr>						<th id="cb" class="manage-column column-cb check-column" style="" scope="col">';						$table .= '<label class="screen-reader-text" for="cb-select-all-1">Выделить все</label>						<input id="cb-select-all-1" type="checkbox">';						$table .= '</th>						<th id="name" class="manage-column column-name" style="" scope="col">'.__('Дополнения','rcl').'</th>						<th id="description" class="manage-column column-description" style="" scope="col">'.__('Описание','rcl').'</th>					</tr>				</thead>';				foreach((array)$addons_data as $key=>$addon){					if($active_addons&&isset($active_addons[$key])) $status = 1;					else $status = 0;					$table .= '<tr id="better-wp-security" class="'.($status ? "active" : "inactive" ).'">						<th class="check-column" scope="row">';							$table .= '<label class="screen-reader-text" for="checkbox_'.$key.'">Выбрать '.$addon['name'].'</label>							<input id="checkbox_'.$key.'" type="checkbox" value="'.$key.'" name="checked[]">';						$table .= '</th>						<td class="plugin-title">							<strong>'.$addon['name'].'</strong>							<div class="row-actions visible">';							if($active_addons&&isset($active_addons[$key])){								$table .= '<span class="inactivate">								<a title="'.__('Деактивировать плагин','rcl').'" href="'.wp_nonce_url( get_bloginfo('wpurl').'/?action-addon=update&status=deactivate&addon='.$key, 'action_addon' ).'">'.__('Деактивировать','rcl').'</a>								</span>';																if($key=='magazin'){									$table .= '|<span class="options">									<a title="'.__('Настройки','rcl').'" href="'.get_bloginfo('wpurl').'/wp-admin/admin.php?page=manage-wpm-options&options='.$key.'">'.__('Настройки','rcl').'</a>									</span>';								}else{																$table .= '|<span class="options">									<a title="'.__('Настройки','rcl').'" href="'.get_bloginfo('wpurl').'/wp-admin/admin.php?page=manage-wprecall&options='.$key.'">'.__('Настройки','rcl').'</a>									</span>';								}							}else{								$table .= '<span class="inactivate">								<a title="'.__('Активировать плагин','rcl').'" href="'.wp_nonce_url( get_bloginfo('wpurl').'/wp-admin/admin.php?action-addon=update&status=activate&addon='.$key, 'action_addon' ).'">'.__('Активировать','rcl').'</a>								</span>|								<span class="inactivate">								<a title="'.__('Удалить','rcl').'" href="'.wp_nonce_url( get_bloginfo('wpurl').'/wp-admin/admin.php?action-addon=update&status=delete&addon='.$key, 'action_addon' ).'">'.__('Удалить','rcl').'</a>								</span>';							}							$table .= '</div>						</td>						<td class="column-description desc">							<div class="plugin-description">							<p>'.$addon['description'].'</p>							</div>							<div class="active second plugin-version-author-uri">							'.__('Версия','rcl').' '.$addon['version'].' | '.__('Автор','rcl').': <a title="'.__('Перейти на страницу автора','rcl').'" href="'.$addon['url'].'">'.$addon['author'].'</a>													</div>						</td>					</tr>';				}				$table .= '</table>			</form>		</div>';				echo $table;	}	function rcl_removeDir( $path ){            if ( $content_del_cat = glob( $path.'/*') ){                foreach ( $content_del_cat as $object ){                    if ( is_dir( $object ) ){                        $this->rcl_removeDir( $object );                        }else {                            @chmod( $object, 0777 );                            unlink( $object );                        }                    }                }            @chmod( $object, 0777 );            rmdir( $path );	}	/*function update_status_addon_recall(){			}*/				function update_status_addon_recall_activate ( ) {	  if ( isset( $_GET['action-addon'] ) ) {		if( !wp_verify_nonce( $_GET['_wpnonce'], 'action_addon' ) ) return false;                global $wpdb, $user_ID, $active_addons;		if ( ! current_user_can('activate_plugins') ) wp_die(__('Вы не можете управлять подлючениями плагинов на этом сайте.','rcl'));                                $paths = array(TEMPLATEPATH.'/recall/add-on',RCL_PATH.'add-on');                		if($_GET['status']=='activate'){                    foreach($paths as $path){                        if(file_exists($path.'/'.$_GET['addon'].'/index.php')){                             $active_addons[$_GET['addon']]['src'] = $path.'/'.$_GET['addon'].'/';                            if(file_exists($path.'/'.$_GET['addon'].'/activate.php')) include($path.'/'.$_GET['addon'].'/activate.php');                            break;                        }                    }                    update_site_option('active_addons_recall',$active_addons);			                    wp_redirect( get_bloginfo('wpurl').'/wp-admin/admin.php?page=manage-addon-recall&update-addon=activate' );exit;		}		if($_GET['status']=='deactivate'){                    foreach((array)$active_addons as $key=>$src){                        if($_GET['addon']!=$key){                             $new_active_list[$key] = $src;                        }else{                            foreach($paths as $path){                                if(file_exists($path.'/'.$_GET['addon'].'/deactivate.php')){                                     include($path.'/'.$_GET['addon'].'/deactivate.php');                                    break;                                }                            }                        }                    }                    update_site_option('active_addons_recall',$new_active_list);                         wp_redirect( get_bloginfo('wpurl').'/wp-admin/admin.php?page=manage-addon-recall&update-addon=deactivate' );exit;		}		if($_GET['status']=='delete'){                    foreach($paths as $path){                        if(file_exists($path.'/'.$_GET['addon'])){                            if(file_exists($path.'/'.$_GET['addon'].'/delete.php')) include($path.'/'.$_GET['addon'].'/delete.php');                            $this->rcl_removeDir( $path.'/'.$_GET['addon'] );                            break;                        }                    }                    wp_redirect( get_bloginfo('wpurl').'/wp-admin/admin.php?page=manage-addon-recall&update-addon=delete' );exit;		}	  }	}	function get_update_scripts_file_rcl(){		//$upload_dir = wp_upload_dir();                global $rcl_options;                $opt_slider = "''";                if(isset($rcl_options['slide-pause'])&&$rcl_options['slide-pause']){                    $pause = $rcl_options['slide-pause']*1000;                    $opt_slider = "{auto:true,pause:$pause}";                }                		$path = TEMP_PATH.'scripts/';		if(!is_dir($path)){			mkdir($path);			chmod($path, 0755);		}		$filename = 'header-scripts.js';		$file_src = $path.$filename;				$f = fopen($file_src, 'w');				$scripts = "var SliderOptions = ".$opt_slider.";"                        . "jQuery(function(){";		$scripts = apply_filters('file_scripts_rcl',$scripts);		$scripts .= "});";		$scripts = apply_filters('javascripts_rcl',$scripts); 		$scripts = str_replace(array("\r\n", "\r", "\n", "\t"), " ", $scripts);		$scripts =  preg_replace('/ {2,}/',' ',$scripts);		fwrite($f, $scripts);		fclose($f);	}	function get_update_scripts_footer_rcl(){		//$upload_dir = wp_upload_dir();		$path = TEMP_PATH.'scripts/';		if(!is_dir($path)){			mkdir($path);			chmod($path, 0755);		}		$filename = 'footer-scripts.js';		$file_src = $path.$filename;				$f = fopen($file_src, 'w');                		$scripts = '';		$scripts = apply_filters('file_footer_scripts_rcl',$scripts);                if(!isset($scripts)) return false;		if($scripts) $scripts = "jQuery(function(){".$scripts." FileAPI.each(examples, function (fn){ fn(); });});";		//$scripts = apply_filters('javascripts_rcl',$scripts); 		$scripts = str_replace(array("\r\n", "\r", "\n", "\t"), " ", $scripts);		$scripts =  preg_replace('/ {2,}/',' ',$scripts);		fwrite($f, $scripts);		fclose($f);	}	function update_group_addon_recall_activate ( ) {            	  if ( isset( $_POST['group-addon-action'] ) ) {              		//add_action( 'wp', array(&$this, 'update_group_addon_recall') );              global $wpdb;		global $user_ID;		global $active_addons;                //print_r($_POST);exit;		if(!$_POST['checked']|| !wp_verify_nonce($_POST['_wpnonce'],'action-addons') ){			wp_redirect( get_bloginfo('wpurl').'/wp-admin/admin.php?page=manage-addon-recall' );exit;		}		                $paths = array(TEMPLATEPATH.'/recall/add-on',RCL_PATH.'add-on');                		if($_POST['group-addon-action']=='activate'){			foreach((array)$_POST['checked'] as $key){                            foreach($paths as $path){                                if(file_exists($path.'/'.$key.'/index.php')){                                     $active_addons[$key]['src'] = $path.'/'.$key.'/';                                    if(file_exists($path.'/'.$key.'/activate.php')) include($path.'/'.$key.'/activate.php');                                    break;                                }                            }						}			update_site_option('active_addons_recall',$active_addons);     			wp_redirect( get_bloginfo('wpurl').'/wp-admin/admin.php?page=manage-addon-recall&update-addon=activate' );exit;		}		if($_POST['group-addon-action']=='deactivate'){			foreach((array)$_POST['checked'] as $key){				foreach((array)$active_addons as $name=>$src){                                    if($name!=$key){                                        $new_active_list[$name] = $src;                                    }else{                                        foreach($paths as $path){                                            if(file_exists($path.'/'.$key.'/deactivate.php')){                                                 include($path.'/'.$key.'/deactivate.php');                                                break;                                            }                                        }                                    }								}								$active_addons = '';				$active_addons = $new_active_list;				$new_active_list = '';						}			update_site_option('active_addons_recall',$active_addons);     			wp_redirect( get_bloginfo('wpurl').'/wp-admin/admin.php?page=manage-addon-recall&update-addon=deactivate' );exit;		}	  }	}	function upload_addon_recall(){				//$dir_src = RCL_PATH.'add-on/';	                        $paths = array(TEMPLATEPATH.'/recall/add-on',RCL_PATH.'add-on');                        $filename = $_FILES['addonzip']['tmp_name'];            $f1 = current(wp_upload_dir()) . "/" . basename($filename);            copy($filename,$f1);            $zip = new ZipArchive;            $res = $zip->open($f1);            if($res === TRUE){                                for ($i = 0; $i < $zip->numFiles; $i++) {                    //echo $zip->getNameIndex($i).'<br>';                    if($i==0) $dirzip = $zip->getNameIndex($i);                    if($zip->getNameIndex($i)==$dirzip.'info.txt'){                            $info = true;                    }                }                if(!$info){                      $zip->close();                      wp_redirect( get_bloginfo('wpurl').'/wp-admin/admin.php?page=manage-addon-recall&update-addon=error-info' );exit;                }                foreach($paths as $path){                      if(file_exists($path.'/')){                          $rs = $zip->extractTo($path.'/');                          break;                      }                }                $zip->close();                unlink($f1);                if($rs){                                        wp_redirect( get_bloginfo('wpurl').'/wp-admin/admin.php?page=manage-addon-recall&update-addon=upload' );exit;                }else{                       wp_die('Распаковка архива не удалась.');                }            } else {                    wp_die('ZIP-архив не найден.');            }			}	function upload_addon_recall_activate ( ) {	  if ( isset( $_POST['install-addon-submit'] ) ) {		if( !wp_verify_nonce( $_POST['_wpnonce'], 'install-addons-rcl' ) ) return false;		add_action( 'wp', array(&$this, 'upload_addon_recall') );	  }	}		function get_parse_addon_info($info){		$addon_data = array();		$cnt = count($info);		if($cnt==1)$info = explode(';',$info[0]);		foreach((array)$info as $string){			if($cnt>1) $string = str_replace(';','',$string);			if ( false !== strpos($string, 'Name:') ){				preg_match_all('/(?<=Name\:)[A-zА-я0-9\-\_\:\/\.\,\?\=\&\@\s\(\)]*/iu', $string, $string_value);				$addon_data['name'] = $string_value[0][0];				continue;			}			if ( false !== strpos($string, 'Version:') ){							preg_match_all('/(?<=Version\:)[A-zА-я0-9\-\_\:\/\.\,\?\=\&\@\s]*/iu', $string, $version_value);				$addon_data['version'] = $version_value[0][0];				continue;			}			if ( false !== strpos($string, 'Description:') ){				preg_match_all('/(?<=Description\:)[A-zА-я0-9\-\_\:\/\.\,\?\=\&\@\s\(\)]*/iu', $string, $desc_value);				$addon_data['description'] = $desc_value[0][0];				continue;			}			if ( false !== strpos($string, 'Author:') ){				preg_match_all('/(?<=Author\:)[A-zА-я0-9\-\_\:\/\.\,\?\=\&\@\s]*/iu', $string, $author_value);				$addon_data['author'] = $author_value[0][0];				continue;			}			if ( false !== strpos($string, 'Url:') ){				preg_match_all('/(?<=Url\:)[A-zА-я0-9\-\_\:\/\.\?\=\&\@\s]*/iu', $string, $url_value);				$addon_data['url'] = $url_value[0][0];				continue;			}		}		return $addon_data;	}	function update_options_rcl_activate ( ) {            global $rcl_options;	  if ( isset( $_POST['primary-rcl-options'] ) ) {		if( !wp_verify_nonce( $_POST['_wpnonce'], 'update-options-rcl' ) ) return false;				if($_POST['login_form_recall']==1&&!isset($_POST['page_login_form_recall'])){			$_POST['page_login_form_recall'] = wp_insert_post(array('post_title'=>'Вход и регистрация','post_content'=>'[loginform]','post_status'=>'publish','post_author'=>1,'post_type'=>'page','post_name'=>'rcl-login'));		}				foreach((array)$_POST as $key => $value){			if($key=='primary-rcl-options') continue;			$options[$key]=$value;		}		update_option('primary-rcl-options',$options);                		$rcl_options = $options;                		if( current_user_can('edit_plugins') ){                        $this->get_update_scripts_file_rcl();			$this->get_update_scripts_footer_rcl();			minify_style_rcl();		}				wp_redirect(get_bloginfo('wpurl').'/wp-admin/admin.php?page=manage-wprecall');		exit;	  }	}	}$rcl_addons = new rcl_addons();