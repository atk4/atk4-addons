/*
 * Easy integration of Highcharts.com
 *
 * in your code, use in following way:
 *
 * 1. $this->js(true)->load("highcharts/js/highcharts.js");// download from
 * //hightchart.com and extract into templates/js
 * 2. $this->js(true)->load("ui.highcharts"); //make sure that you have added
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
    highstock: function(custom){
        var options = {
            chart: {
                renderTo: this.jquery.attr("id"),
            },
            title: {
                text: 'Title'
            },
            xAxis: {
                maxZoom: 60
            },
            yAxis: {
                title: {
                    text: 'Values'
                }
            },
            series: [{
                name: "line1",
                data: [[Date.UTC(2003,8,24), 1], [Date.UTC(2003,8,25), 2]]
            }],
            rangeSelector: {
                selected: 1
            }
        };
        $.extend(true, options, custom);
        var chart = new Highcharts.StockChart(
            options
        );
    }}, $.univ._import
)
    
})($);
