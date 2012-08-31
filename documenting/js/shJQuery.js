// vi:sw=2 ts=2

(function($){
  $.widget('atk.syntaxhighlighter', {

    options : {
      toolbar: false
    },

    _create: function(){
      var brush = SyntaxHighlighter.brushes[this.options.lang];
      var id=this.element.attr('data-short');

      if (!brush)
        return;

      // instantiate brush
      var highlighter = new brush();

      var container = this.element.find('pre');
      var code = container.text();

      highlighter.init(this.options);


      container.replaceWith(highlighter.getHtml(code));

      var self=this;

      var f=function(e){
        var t=$('<textarea/>');
        self.element.find('.syntaxhighlighter').addClass('source');
        t.val(code);
        self.element.find('.container').append(t);
        t.focus();
        t.select();
        self.element.find('.code').unbind('dblclick');
        t.keyup(function(e){
          if(e.keyCode==27)t.blur();
        });
        t.click(function(e){ 
          e.preventDefault(); 
          e.stopPropagation();
          this.select();
          t.unbind('click');  // don't mess with user
        });
        t.blur(function(){ 
          self.element.find('.syntaxhighlighter').removeClass('source');
          t.remove(); 
          self.element.find('.code').dblclick(f);
        });
      }
      this.element.find('.copy_link').click(f);
      this.element.find('.code').dblclick(f);

      this.element.find('.gutter .line').each(function(){
        $(this)
        .wrap('<a>')
        .parent()
        .attr('href','#'+id+'_'+$(this).text())
        .attr('name',''+id+'_'+$(this).text())
        ;
      });
    }

  });
}(jQuery));
