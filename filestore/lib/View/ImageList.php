<?php
namespace filestore;
class View_ImageList extends \CompleteLister
{
    /**
     * Paginator object
     *
     * @see addPaginator()
     */
    protected $paginator = null;

	function init()
	{
		parent::init();
	}
	
    /**
     * Renders single row
     *
     * @param SQLite $template template to use for row rendering
     *
     * @return string HTML of rendered template
     */
    function rowRender($template)
    {
        $template->trySet('width', $this->model->default_thumb_width);
        return parent::rowRender($template);
    }

    /**
     * Default template
     *
     * @return void
     */
    function defaultTemplate()
    {
    	$this->addLocations(); // add addon files to pathfinder
        return array('view/imagelist');
    }

    /**
     * Add addon files to pathfinder
     * 
     * @return void
     */
	function addLocations()
	{
        $l = $this->api->locate('addons', __NAMESPACE__, 'location');
		$addon = $this->api->locate('addons', __NAMESPACE__);
        $this->api->pathfinder->addLocation($addon, array(
        	'template' => 'templates',
            //'css' => 'templates/css',
        ))->setParent($l);
	}
	
    /**
     * Adds paginator to the lister
     *
     * @param int $ipp row count per page
     * @param array $options
     *
     * @return $this
     */
    function addPaginator($ipp = 25, $options = null)
    {
        // adding ajax paginator
        if ($this->paginator) {
            return $this->paginator;
        }
        $this->paginator = $this->add('Paginator', $options);
        $this->paginator->ipp($ipp);
        return $this;
    }
}
