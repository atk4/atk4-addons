<?php
namespace filestore;
class Page_FileAdmin extends \Page
{
    // Used model classes
    public $file_model_class = 'filestore/Model_File';
    public $type_model_class = 'filestore/Model_Type';
    public $volume_model_class = 'filestore/Model_Volume';

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
        $g  ->setModel($this->type_model_class, null, array('name', 'mime_type'))
            ->setOrder('name asc');
        if ($g->grid) {
            $g->grid->addPaginator(50);
        }

        // Files
        $g = $this->add('CRUD');
        $g->setModel($this->file_model_class,
                array('original_filename', 'filename', 'filesize', 'deleted', 'dirname', 'url', 'filestore_type_id')
            )->setOrder('id desc');
        if ($g->grid) {
            $g->grid->addPaginator(50);
        }
	}
}
