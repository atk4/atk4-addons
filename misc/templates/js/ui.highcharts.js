/*
 * Easy integration of Highcharts.com
 *
 * in your code, use in following way:
 *
 * 1. $this->js(true)->_load("highcharts/js/highcharts.js");// download from
 * //hightchart.com and extract into templates/js
 * 2. $this->js(true)->_load("ui.highcharts"); //make sure that you have added
 * // this location for js includes
 *
 * above 1&2 can be done in place where you need charts OR in frontend, if you
 * use those frequently
 *
 * $this->add("View_HtmlElement")->setElement("div")
 *      ->js(true)
 *      ->univ()
 *      ->highchart("line", array(1,2,3,4...), array(
 *              0 => array(
 *                  "name" => "Name of series1",
 *                  "data" => array(10,20,30,40,50)
 *              ),
 *              ...
 *          ), array(
 *              "title" => array(
 *                  "text" => "Title of your chart",
 *               )
 *          )
 *      );
 *
 * Note, to use highcharts in commercial project, you need to obtain proper
 * licence from the site of developers of the highcharts.com
 *
 * brought to you by jancha, 2011
 *
 * */

(function($){

$.each({
    highchart: function(options){
        var defaults = {
            chart: {
                renderTo: this.jquery.attr("id"),
                width: 500,
                height: 500,
                zoomType: "xy",
            },
            title: {
                text: 'Title'
            }
        };
        $.extend(true, defaults, options);
        var chart = new Highcharts.Chart(
            defaults
        );
    }}, $.univ._import
)
    
})($);
