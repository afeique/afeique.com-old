/**
 * 
 * ADMIN JS
 * 
 */

$(function() {
  /**
   * 
   * POST PUBLISHER
   * 
   */
  
  /**
   * BIND CHARS LEFT TO TITLE, TAGS, DESCRIPTION
   */
  $('#post-title, #post-tags, #post-description')
  .blur(function() { $(this).val($(this).val().trim()); })
  .bind('keyup blur', function(event) {
    var chars_left = 250 - $(this).val().length;
    $(this).parent().contents().find('.chars-left').text(chars_left);
  });
  
  /**
   * 
   * POST EDITOR BUTTONS
   * 
   */
  
  /**
   * HIDE EVERYTHING
   */
  $('.edit-title, .edit-tags, .delete-post').hide();
  
  /**
   * TITLE EDITOR BUTTON
   */
  $('.post-title')
  .mouseover(function() {
    $(this).contents('.edit-title').show();
  })
  .mouseout(function() {
    $(this).contents('.edit-title').hide();
  });
  
  /**
   * POST TAGS EDITOR BUTTON
   */
  $('.post-tags')
  .mouseover(function() {
    $(this).contents('.edit-tags').show();
  })
  .mouseout(function() {
    $(this).contents('.edit-tags').hide();
  });
  
  /**
   * DELETE POST BUTTON
   */
  $('.post-row')
  .mouseover(function() {
    $(this).contents().find('.delete-post').show();
  })
  .mouseout(function() {
    $(this).contents().find('.delete-post').hide();
  });
  
  /**
   * 
   * INLINE AJAX EDITORS
   * 
   */
  
  var editing = 0;
  
  /**
   * POST ROW TITLE EDITOR
   */
  $('div.post-row div.post-title a.edit-title').click(function() {
    if (!editing) {
      editing = 1;

      var abel = $(this).parent();
      var old_title = abel.children('h2').text();
      var original_title_html = abel.contents().clone(true);
      var post_id = abel.parent().attr('id').replace(/[a-z\-]/g,'');
      
      var editor = $(document.createElement('input'))
      .attr('type','text')
      .attr('id','post-title-input')
      .attr('value', old_title)
      .blur(function() { $(this).val($(this).val().trim()); })
      .bind('keyup blur', function(event) {
        var chars_left = 250 - $(this).val().length;
        abel.contents().find('.chars-left').text(chars_left);
      });
      
      abel.empty().append(editor);

      var chars_left = $(document.createElement('li'))
      .append($(document.createElement('span'))
        .addClass('chars-left')
        .append(250 - abel.children('input').val().length)
      );
      
      var submit_button = $(document.createElement('li'))
      .append(
          $(document.createElement('a')).append('submit').attr('title','submit').attr('href','javascript: void(0)')
          .button({
            text: false,
            icons: {primary: 'ui-icon-check'}
          })
          .click(function() {
            var new_title = abel.children('input').text().trim();
            if (new_title.length > 0 && new_title.length <= 250 && new_title != old_title) {
              $.ajax({
                url: BASE_URL+'admin/edit-title',
                type: 'POST',
                data: {
                  id: post_id,
                  description: new_title
                }
              });
            } else {
              abel.empty().append(original_title_html);
            }
            
            editing = 0;
          })
      );

      var cancel_button = $(document.createElement('li'))
      .append(
          $(document.createElement('a')).append('cancel').attr('title','cancel').attr('href','javascript: void(0)')
          .button({
            text: false,
            icons: {primary: 'ui-icon-closethick'}
          })
          .click(function() {
            abel.empty().append(original_title_html);
            editing = 0;
          })
      );

      var commander = $(document.createElement('ul'))
      .attr('id','post-title-commander')
      .append(chars_left)
      .append(submit_button)
      .append(cancel_button);

      abel.append(commander);
    }
  });
  
  /**
   * POST ROW TAGS EDITOR
   */
  $('div.post-row div.span-24 div.post-tags a.edit-tags').click(function() {
    if (!editing) {
      editing = 1;

      var abel = $(this).parent().parent();
      var old_tags = '';
      abel.contents().find('.tags-list').children('li').each(function() {
        old_tags += ' '+$(this).text();
      });
      old_tags = old_tags.replace(/^\s+/,'');
      
      var original_tags_html = abel.contents().clone(true);
      var post_id = abel.parent().attr('id').replace(/[a-z\-]/g,'');
      
      var editor = $(document.createElement('input'))
      .attr('type','text')
      .attr('id','post-tags-input')
      .attr('value', old_tags)
      .blur(function() { $(this).val($(this).val().trim()); })
      .bind('keyup blur', function(event) {
        var chars_left = 250 - $(this).val().length;
        abel.contents().find('.chars-left').text(chars_left);
      });
      
      abel.empty().append($(document.createElement('strong')).append('tagged')).append(' ').append(editor);

      var chars_left = $(document.createElement('li'))
      .append($(document.createElement('span'))
        .addClass('chars-left')
        .append(250 - abel.children('input').val().length)
      );
      
      var submit_button = $(document.createElement('li'))
      .append(
          $(document.createElement('a')).append('submit').attr('title','submit').attr('href','javascript: void(0)')
          .button({
            text: false,
            icons: {primary: 'ui-icon-check'}
          })
          .click(function() {
            var new_tags = abel.children('input').text().trim();
            if (new_tags != '' && new_tags != old_tags && new_tags.length <= 250) {
              $.ajax({
                url: BASE_URL+'admin/edit-tags',
                type: 'POST',
                data: {
                  id: post_id,
                  description: new_tags
                }
              });
            } else {
              abel.empty().append(original_tags_html);
            }
            
            editing = 0;
          })
      );

      var cancel_button = $(document.createElement('li'))
      .append(
          $(document.createElement('a')).append('cancel').attr('title','cancel').attr('href','javascript: void(0)')
          .button({
            text: false,
            icons: {primary: 'ui-icon-closethick'}
          })
          .click(function() {
            abel.empty().append(original_tags_html);
            editing = 0;
          })
      );

      var commander = $(document.createElement('ul'))
      .attr('id','post-tags-commander')
      .append(chars_left)
      .append(submit_button)
      .append(cancel_button);

      abel.append(commander);
    }
  });
  
  /**
   * POST ROW DESCRIPTION EDITOR
   */
  $('div.post-row div.span-24 div.post-description').click(function() {
    if (!editing) {
      editing = 1;

      var abel = $(this).parent();
      var old_description = $(this).text();
      var old_description_html = $(this).clone(true);
      var post_id = abel.parent().attr('id').replace(/[a-z\-]/g,'');
      
      var editor = $(document.createElement('textarea'))
      .attr('id','post-description-textarea')
      .append(old_description)
      .blur(function() { $(this).val($(this).val().trim()); })
      .bind('keyup blur', function(event) {
        var chars_left = 250 - $(this).val().length;
        abel.contents().find('.chars-left').text(chars_left);
      });
      
      abel.empty().append(editor);

      var chars_left = $(document.createElement('li'))
      .append($(document.createElement('span'))
        .addClass('chars-left')
        .append(250 - abel.children('textarea').val().length)
      );
      
      var submit_button = $(document.createElement('li'))
      .append(
          $(document.createElement('a')).append('submit').attr('title','submit').attr('href','javascript: void(0)')
          .button({
            text: false,
            icons: {primary: 'ui-icon-check'}
          })
          .click(function() {
            var new_description = abel.children('textarea').val().trim();
            if (new_description.length > 0 && new_description.length <= 250 && new_description != old_description) {
              $.ajax({
                url: BASE_URL+'admin/edit-description',
                type: 'POST',
                data: {
                  id: post_id,
                  description: new_description
                }
              });
            } else {
              abel.empty().append(old_description_html);
            }
            
            editing = 0;
          })
      );

      var cancel_button = $(document.createElement('li'))
      .append(
          $(document.createElement('a')).append('cancel').attr('title','cancel').attr('href','javascript: void(0)')
          .button({
            text: false,
            icons: {primary: 'ui-icon-closethick'}
          })
          .click(function() {
            abel.empty().append(old_description_html);
            editing = 0;
          })
      );

      var commander = $(document.createElement('ul'))
      .attr('id','post-description-commander')
      .append(chars_left)
      .append(submit_button)
      .append(cancel_button);

      abel.append(commander);
    }
  });
});