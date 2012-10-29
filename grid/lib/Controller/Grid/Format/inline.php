<?php
namespace grid;
class Controller_Grid_Format_inline extends \AbstractController {
	public $edit_fields=null;
	function initField($f,$d){
		if($id=$_GET[$this->owner->name.'_reload_row']){

			$this->owner->model->addCondition(
				$this->owner->model->id_field, $id);

			$g=$this->owner;

			$this->api->addHook('pre-render',function() use($g){
				$g->precacheTemplate();
				foreach($g->getIterator() as $g->current_id=>$g->current_row){
					$g->formatRow();
					$result=$g->rowRender($g->row_t);
					if($g->api->jquery)$g->api->jquery->getJS($g);
					throw new \Exception_StopRender($result);
				}
			});
		}
	}
    function editFields(array $fields){
    	$this->edit_fields=$fields;
    }
	function formatField($field){
		$g=$this->owner;

        $val=$g->current_row[$field.'_original'];
        $g->current_row_html[$field]='<span class="grid_inline" style="display: block" id="'.($s=$g->name.'_'.$field.'_inline_'.
            $g->current_id).'" >'.
            '<i style="float: left" class="atk-icon atk-icons-red atk-icon-office-pencil"></i>'.
            $g->current_row[$field].
            '&nbsp;</span>';
        $js=$g->js(true)->_selector('#'.$s)->click(
                $g->js()->_enclose()->_selectorThis()->parent()->atk4_load($this->api->url(null,array($s=>true)))
                );

		if($_GET[$this->owner->name.'_reload_cell'] && $_GET['id']==$g->current_id && $_GET['field']==$field){
			echo '<script>'.$js.'</script>';
			echo $g->current_row_html[$field]?:htmlspecialchars($g->current_row[$filed]);
			exit;
		}

        if($_GET[$s]){
            // clicked on link
            $this->api->stickyGET($s);
            $f=$g->owner->add('Form',$s,null,array('form_empty','form'));
            $f->setModel($g->model,$this->edit_fields?:array($field));


            $fields=array();
            foreach($f->elements as $ff)if($ff instanceof \Form_Field){
            	$ff->setCaption('');
            	$fields[]=$ff;

	            // ESC = stop editing
            	$ff->js(true)->univ()->onKey(27,
            		$g->js(null,$f->js()->remove())->_enclose()
            		->atk4_grid('reloadRow',$g->current_id)
            		);
            }

            // get last field
            $fl=end($fields);
            $ff=reset($fields);
            //$ff->set($val);
            $fl->js('blur',$f->js()->submit());

            // Tab - save and move on
            $fl->js(true)->univ()->onKey(9,
            	$f->js()->_enclose()->closest('td')->nextAll()->_fn('add',array(
            		$f->js()->closest('tr')->next()->children()
            		))->find('span.grid_inline')->eq(0)->click()
            	,'shiftKey',false);

            $ff->js(true)->univ()->onKey(9,
            	$f->js()->_enclose()->closest('tr')->prev()->children()->_fn('add',array(
            		$f->js()->closest('td')->prevAll()
            		))->find('span.grid_inline')->last()->click()
            	,'shiftKey',true);
            // TODO: blurring is not working very well with multiple fields

            $ff->js(true)->css(array('width'=>'100%'));
            $_GET['cut_object']=$f->name;

            $f->onSubmit(function($f)use($g,$field){
            	$f->update();
                //$g->js()->atk4_grid('reloadRow',$g->current_id)->execute();
                $f->js()->closest('td')->atk4_loader('loadURL',$g->api->url(null,
                	array($g->name.'_reload_cell'=>true,'id'=>$g->current_id,'field'=>$field)
                ))->execute();
            });

            $f->recursiveRender();
        }
	}
}