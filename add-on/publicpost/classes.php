<?php
class Rcl_Child_Terms{

	function get_terms_post( $post_cat = array() ){

		$cat_list = array();
		foreach( $post_cat as $key => $p_cat ){
			foreach($post_cat as $pc){
				if($pc->parent==$p_cat->term_id){
					unset($post_cat[$key]);
					break;
				}

			}
		}
		$cnt = count($post_cat);
		foreach($post_cat as $data){
			if($cnt>1){
				if($data->parent==0) continue;
			}
			$cat_list[] = $data;
		}
		return $cat_list;
	}
}

class Rcl_List_Terms{

	public $a;
	public $ctg;
	public $sel;
	public $cat_list;
	public $allcats;
        public $selected;

	function get_select_list($allcats,$cat_list,$cnt,$ctg){
                if(!$allcats) return false;
		$catlist = '';
                //print_r($cat_list);
		if($ctg) $this->ctg = $ctg;
		$this->allcats = $allcats;
		$this->cat_list = $cat_list;
		for($this->sel=0;$this->sel<$cnt;$this->sel++){
                        $this->selected = false;
			$catlist .= '<p><select class="postform" name="cats[]">';
			if($this->sel>0) $catlist .= '<option value="">'.__('Not selected','rcl').'</option>';
			$catlist .= $this->get_option_list();
			$catlist .= '</select></p>';
		}
		return $catlist;
	}

	function get_option_list(){

		if($this->ctg){
			$ctg_ar = explode(',',$this->ctg);
			$cnt_c = count($ctg_ar);
		}
                $catlist = '';
		foreach($this->allcats as $cat){

			$this->a = 0;

			if($this->ctg){

				for($z=0;$z<$cnt_c;$z++){
					if($ctg_ar[$z]==$cat->term_id){
						$catlist .= $this->get_loop_child($cat);
					}
				}

			}else{
				if($cat->parent!=0) continue;
				$catlist .= $this->get_loop_child($cat);
			}
		}
		return $catlist;
	}

	function get_loop_child($cat){
                $catlist = false;
		$child = $this->get_child_option($cat->term_id,$this->a);
		if($child){
                    $catlist = '<optgroup label="'.$cat->name.'">'.$child.'</optgroup>';
        }else{

            $selected = '';
            if(!$this->selected&&$this->cat_list){
                foreach($this->cat_list as $key=>$sel){
                    if($sel->term_id==$cat->term_id){
                        //echo $sel->term_id.' - '.$cat->term_id.'<br>';
                        $selected = selected($sel->term_id,$cat->term_id,false);
                        $this->selected = true;
                        unset($this->cat_list[$key]);
                        break;
                    }
                }
            }

            $catlist = '<option '.$selected.' value="'.$cat->term_id.'">'.$cat->name.'</option>';
            }
            return $catlist;
	}

	function get_child_option($term_id,$a){
                $catlist = false;
		foreach($this->allcats as $cat){
			if($cat->parent!=$term_id) continue;
			$child = '';
			$b = '-'.$a;
			$child = $this->get_child_option($cat->term_id,$b);
			if($child){
                            $catlist .= '<optgroup label=" '.$b.' '.$cat->name.'">'.$child.'</optgroup>';
                        }else{
                            $selected = '';
                            if(!$this->selected&&$this->cat_list){
                                foreach($this->cat_list as $key=>$sel){
                                    if($sel->term_id==$cat->term_id){
                                        $selected = selected($sel->term_id,$cat->term_id,false);
                                        $this->selected = true;
                                        unset($this->cat_list[$key]);
                                        break;
                                    }
                                }
                            }
                            $catlist .= '<option '.$selected.' value="'.$cat->term_id.'">'.$cat->name.'</option>';
                        }
			$this->a = $a;
		}
		return $catlist;
	}

	function get_parent_option($child,$term_id,$a){
		foreach($this->allcats as $cat){
			if($cat->term_id!=$term_id) continue;
			$parent = '<optgroup label="'.$cat->name.'">'.$child.'</optgroup>';
		}
		return $parent;
	}

}

class Rcl_Edit_Terms_List{

	public $cats;
	public $new_cat = array();

	function get_terms_list($cats,$post_cat){
		$this->cats = $cats;
		$this->new_cat = $post_cat;
		$cnt = count($post_cat);
		for($a=0;$a<$cnt;$a++){
			foreach((array)$cats as $cat){
				if($cat->term_id!=$post_cat[$a]) continue;
				if($cat->parent==0) continue;
				$this->new_cat = $this->get_parents($cat->term_id);
			}
		}
		return $this->new_cat;
	}
	function get_parents($term_id){
		foreach($this->cats as $cat){
			if($cat->term_id!=$term_id) continue;
			if($cat->parent==0) continue;
			$this->new_cat[] = $cat->parent;
			$this->new_cat = $this->get_parents($cat->parent);
		}
		return $this->new_cat;
	}
}

class Rcl_Thumb_Form{

	public $post_id;
	public $thumb = 0;
	public $id_upload;

	public function __construct($p_id=false,$id_upload='postupload') {
		$this->post_id = $p_id;
		$this->id_upload = $id_upload;
		$this->gallery_init();
    }

	function gallery_init(){
		global $rcl_options;

		if($this->post_id) $this->thumb = get_post_meta($this->post_id, '_thumbnail_id',1);
		if(!$rcl_options['media_downloader_recall']) $this->gallery_rcl();
		else $this->thumbnail_post();
	}

	function gallery_rcl(){
		global $user_ID;

		if($this->post_id) $gal = get_post_meta($this->post_id, 'recall_slider', 1);
		else $gal = 0;

		/*echo '<small>Для вывода изображений в определенных местах своей публикации вы можете<br>использовать шорткоды [art id="123"], размещая их в том месте публикации, где желаете видеть изображение. Можно указать размер изображения thumbnail,medium или full, например: [art id="123" size="medium"]. Требуемый размер также можно указывать числовыми значениями через запятую (ширина, высота), например: [art id="123" size="450,300"]</small>';*/
		//echo '</p>';

		if($this->post_id){
			$args = array(
				'post_parent' => $this->post_id,
				'post_type'   => 'attachment',
				'numberposts' => -1,
				'post_status' => 'any'
			);
			if($this->id_upload=='postupload') $args['post_mime_type'] = 'image';
			$child = get_children( $args );
			if($child){ foreach($child as $ch){$temp_gal[]['ID']=$ch->ID;} }

		}else{
			$temp_gal = unserialize(get_the_author_meta('tempgallery',$user_ID));
		}

                $attachlist = '';
		if($temp_gal){
                    $attachlist = $this->get_gallery_list($temp_gal);
		}

		echo '<ul id="temp-files">'.$attachlist.'</ul>';
		echo '<p><label><input ';
		//if(!$this->post_id) echo 'checked="checked"';
		echo 'type="checkbox" '.checked($gal,1,false).' name="add-gallery-rcl" value="1"> - '.__('Display all attached images in the gallery.','rcl').'</label>
		<div id="status-temp"></div>
		<div id="'.$this->id_upload.'">
		<div class="b-upload__dnd">

			  <div class="b-upload__hint">'.__('To add files to the download queue','rcl').'</div>
			  <div class="js-files b-upload__files">
				 <div class="js-file-tpl b-thumb" data-id="<%=uid%>" title="<%-name%>, <%-sizeText%>">
					<div data-fileapi="file.remove" class="b-thumb__del">✖</div>
					<div class="b-thumb__preview">
					   <div class="b-thumb__preview__pic"></div>
					</div>
					<% if( /^image/.test(type) ){ %>
					   <div data-fileapi="file.rotate.cw" class="b-thumb__rotate"></div>
					<% } %>
					<div class="b-thumb__progress progress progress-small"><div class="bar"></div></div>
					<div class="b-thumb__name"><%-name%></div>
				 </div>
			  </div>
			  <hr>
			  <div class="btn recall-button btn-success btn-small js-fileapi-wrapper">
				 <span>'.__('Add','rcl').'</span>
				 <input name="filedata" type="file">
			  </div>
		   </div>
		</div>';
	}

	function get_gallery_list($temp_gal){
		$attachlist = '';
		foreach((array)$temp_gal as $attach){
			$mime_type = get_post_mime_type( $attach['ID'] );
			$attachlist .= rcl_get_html_attachment($attach['ID'],$mime_type);
		}
		return $attachlist;
	}

	function thumbnail_post(){
		echo '<div id="thumbblock-post">
			<div id="thumbnail_rcl" class="alignleft">';
			if($this->thumb){
				$thumb_url = wp_get_attachment_image_src( $this->thumb, 'thumbnail' );
				echo '<span class="delete"></span><img width="100" height="100" src="'.$thumb_url[0].'"><input type="hidden" name="thumb" value="'.$this->thumb.'">';
			}
			echo '</div>
			<h3>'.__('Thumbnail','rcl').'</h3>
			<div>'.rcl_get_button(__('To assign a thumbnail','rcl'),'#',array('id'=>'add_thumbnail_rcl')).'</div>
		</div>';
	}
}