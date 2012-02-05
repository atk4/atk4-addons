/*
 * Easy integration of Highcharts.com
 *
 * in your code, use in following way:
 *
 * 1. $this->js(true)->_load("highcharts.js");// download from
 * //highchart.com and extract into templates/default/js
 * 2. $this->js(true)->_load("ui.highcharts"); //make sure that you have added
 * // this location for js includes
 *
 * above 1&2 can be done in place where you need charts OR in frontend, if you
 * use those frequently
 *
 * $this->add("View_HtmlElement")->setElement("div")
 *      ->js(true)
 *      ->univ()
 *      ->highchart($data);
 *
 * For options, refer to: http://www.highcharts.com/stock/ref/
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
            }
        };
        $.extend(true, options, custom);
        var chart = new Highcharts.StockChart(
            options
        );
    }}, $.univ._import
)
    
})($);
