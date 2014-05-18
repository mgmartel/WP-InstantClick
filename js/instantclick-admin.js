jQuery(function($) {

  var default_content = "\
// Before InstantClick init:\n\
\n\
\n\
InstantClick.init(0);\n\
// After InstantClick init:\n\
\n",
      $textarea = $('#instantclick-editor'),
      instantClickEditor = CodeMirror($textarea.get(0),{
        value: default_content,
        mode: 'javascript',
        lineNumbers: true
      });

  //
  // 'Preload On' parameter
  //

  var $param = $('<span class="instantclick-param CodeMirror-uneditable"></span>');
  instantClickEditor.markText({line: 3, ch: 18}, {line: 3, ch: 19}, {
    replacedWith: $param[0]
  });

  var set_param = function(to) {
    if ( 'mousedown' === to )
      to = "'mousedown'";
    else if ( '50' === to || '100' === to )
      to = parseInt(to);
    else
      to = '';

    $param.text(to);
  };

  set_param($('[name="instantclick[preload_on]"]:checked').val());
  $('[name="instantclick[preload_on]"]').change(function(){
    set_param( $(this).val() );
  });

  //
  // Code editor
  //

  // Has to be from last to first, otherwise
  // the line numbers will not be correct
  var sections = {
    after_init: {
      start: 5,
      uneditable: [3,4]
    },
    before_init: {
      start: 1,
      uneditable: [0,0]
    }
  };

  $.each(sections,function(label,obj){

    obj.marker = instantClickEditor.markText({line: obj.uneditable[0], ch: 0}, {line: obj.uneditable[1] + 1, ch: 0}, {
      className: 'CodeMirror-uneditable',
      readOnly: true,
      atomic: true,
      // If inclusiveLeft is on after the first editable section, the user can put
      // two read-only sections together, leaving it compeletly uneditable.
      inclusiveLeft: obj.uneditable[0] === 0
    });

    var $i = obj.input = $('#instantclick-script-' + label),
        v  = $i.val(),
        p  = {line:obj.start, ch:0};

    instantClickEditor.replaceRange(v,p,p);
  });

  $('#instantclick-settings').submit(function(){

    var end_prev = {line:instantClickEditor.lastLine() + 1,ch:0};
    $.each(sections,function(){

      var obj = this,
          end_uneditable = obj.marker.find().to,
          text           = instantClickEditor.getRange(end_uneditable,end_prev);

      obj.input.val(text);
      end_prev = obj.marker.find().from;
    });

  });

});