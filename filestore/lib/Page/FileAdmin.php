<?php
namespace filestore;
class Page_FileAdmin extends \Page
{
    // Used Model classes
    public $file_model_class = 'filestore/Model_File'; // filestore/Model_Image
    public $type_model_class = 'filestore/Model_Type';
    public $volume_model_class = 'filestore/Model_Volume';
    
    // Used View class for image list
    public $imagelist_view_class = 'filestore/View_ImageList';

	function init()
	{
		parent::init();
		
		/*
		// this way you can do if you use Tabs
		$this->api->stickyGET('tab');
		switch ($_GET['tab']) {
			case null:
			case 'file':
				$this->model = $this->file_model_class;
				break;
			case 'volume':
				$this->model = $this->volume_model_class;
				break;
			case 'type':
				$this->model = $this->type_model_class;
				break;
		}
		*/
	}
	
	function initMainPage()
	{
		// Upload form
		$f = $this->add('Form');
		$f->addField('upload', 'Upload_test', 'Upload Test')
            ->setModel($this->file_model_class);

		// Split page in columns
		$v = $this->add('View_Columns');

        // Volumes
		$c = $v->addColumn(6);
		$c->add('H3')->set('Storage Location');
        $g = $c->add('CRUD');
        $g  ->setModel($this->volume_model_class, null, array('name', 'dirname', 'stored_files_cnt', 'enabled'))
            ->setOrder('name asc');
        if ($g->grid) {
            $g->grid->addPaginator(50);
        }

        // Filetypes
		$c = $v->addColumn(6);
		$c->add('H3')->set('Allowed Filetypes');
        $g = $c->add('CRUD');
        $g  ->setModel($this->type_model_class, null, array('name', 'mime_type', 'allow'))
            ->setOrder('name asc');
        if ($g->grid) {
            $g->grid->addPaginator(50);
        }

        // Files
		$this->add('H3')->set('Files');
        $g = $this->add('CRUD');
        $m = $this->add($this->file_model_class);
        $g->setModel($m,
                $m->policy_soft_delete
                    ? array('original_filename', 'filename', 'filesize', 'dirname', 'url', 'filestore_type_id')
                    : array('original_filename', 'filename', 'filesize', 'dirname', 'url', 'filestore_type', 'deleted')
            )->setOrder('id desc');

        if (!$g->isEditing()) {
            // add paginator
            $g->grid->addPaginator(50);
            
            // add open button
            $self = $this;
            $g->grid->add('VirtualPage')
                ->addColumn('open', 'View file', 'Open')
                ->set(function($page)use($self){
                    $id = $_GET[$page->short_name.'_id'];

                    // find Filestore file
                    $m = $page->add($self->file_model_class)->load($id);
                    
                    // open as object
                    $url = $m->get('url');
                    $page->add('View')
                        ->setElement('object')
                        ->setAttr('type', $m->ref('filestore_type_id')->get('mime_type'))
                        ->setAttr('data', $m->get('url'))
                        ->setAttr('width', '100%')
                        ->setAttr('height', '500px')
                        ->setHTML('Your browser is to old to open this file inline<br/><a href="'.$url.'" target=_blank>'.$url.'</a>')
                        ;
                });
        }

        // Image list (show only if we use Model_Image class as file model)
        $m = $this->add($this->file_model_class);
        if ($m instanceof Model_Image) {
            $this->add('H3')->set('Images');
            $v = $this->add($this->imagelist_view_class);
            $v->setModel($m)->setOrder('id desc');
            $v->addPaginator(20);
        }
    }
}
