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
  $('#post-title, #post-tags, #post-description, #post-directory')
  .blur(function() { $(this).val($(this).val().trim().replace(/\s{2,}/,' ')); })
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
  /*
  $('.post-row')
  .mouseover(function() {
    $(this).contents().find('.delete-post').show();
  })
  .mouseout(function() {
    $(this).contents().find('.delete-post').hide();
  });
  */
  
  /**
   * 
   * INLINE AJAX EDITORS
   * 
   */
  
  var status400 = 'A bad request was made. Either your input is too long or the directory could not be found.';
  var status404 = 'For some reason, the post you are editing could not be found.';
  var status500 = 'There was a problem with the database when editing your post. Try again.';
  
  /**
   * POST ROW TITLE EDITOR
   */
  $('div.post-row div.post-title a.edit-title').click(function() {
    var abel = $(this).parent();
    var old_title = abel.children('h2').text();
    var original_title_html = abel.contents().clone(true);
    var post_id = abel.parent().attr('id').replace(/[a-z\-]/g,'');
    
    var editor = $(document.createElement('input'))
    .attr('type','text')
    .attr('class','post-title-input')
    .attr('value', old_title)
    .blur(function() { $(this).val($(this).val().trim().replace(/\s{2,}/,' ')); })
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
          abel.contents().find('.post-title-spinner').show();
          var new_title = abel.children('input').val().trim().replace(/\s{2,}/,' ');
          if (new_title.length > 0 && new_title.length <= 250 && new_title != old_title) {
            $.ajax({
              dataType: 'json',
              url: BASE_URL+'admin/update/title/'+post_id,
              type: 'POST',
              data: {
                title: new_title
              },
              statusCode: {
                400: function() {
                  alert(status400);
                },
                404: function() {
                  alert(status404);
                },
                500: function() {
                  alert(status500);
                }
              },
              success: function(data) {
                abel.contents().find('.post-title-spinner').hide();
                if (data.error === undefined) {
                  var new_title_html = original_title_html;
                  var n = 0;
                  
                  $(new_title_html[n]).children('a').text(data.title);
                  abel.empty().append(new_title_html);
                } else {
                  alert(data.error);
                }
              }
            });
          } else {
            abel.empty().append(original_title_html);
          }
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
        })
    );
    
    var spinner = $(document.createElement('li'))
    .append($(document.createElement('img')).attr('src', BASE_URL+'images/spinner.gif'))
    .attr('class','post-title-spinner')
    .hide();

    var commander = $(document.createElement('ul'))
    .attr('class','post-title-commander')
    .append(chars_left)
    .append(submit_button)
    .append(cancel_button)
    .append(spinner);

    abel.append(commander);
  });
  
  /**
   * POST ROW TAGS EDITOR
   */
  $('div.post-row div.post-tags a.edit-tags').click(function() {
    var abel = $(this).parent();
    var old_tags = '';
    abel.find('.tags-list').children('li').each(function() {
      old_tags += ' '+$(this).text();
    });
    old_tags = old_tags.replace(/^\s+/,'');
    
    var original_tags_html = abel.contents().clone(true);
    var post_id = abel.parent().attr('id').replace(/[a-z\-]/g,'');
    
    var editor = $(document.createElement('input'))
    .attr('type','text')
    .attr('class','post-tags-input')
    .attr('value', old_tags)
    .blur(function() { $(this).val($(this).val().trim().replace(/\s{2,}/,' ')); })
    .bind('keyup blur', function(event) {
      var chars_left = 250 - $(this).val().length;
      abel.contents().find('.chars-left').text(chars_left);
    });
    
    abel.empty()
    .append($(document.createElement('strong')).append('tagged'))
    .append(' ')
    .append(editor);

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
          abel.contents().find('.post-tags-spinner').show();
          var new_tags = abel.children('input').val().trim().replace(/\s{2,}/,' ');
          if (new_tags != '' && new_tags != old_tags && new_tags.length <= 250) {
            $.ajax({
              dataType: 'json',
              url: BASE_URL+'admin/update/tags/'+post_id,
              type: 'POST',
              data: {
                tags: new_tags
              },
              statusCode: {
                400: function() {
                  alert(status400);
                },
                404: function() {
                  alert(status404);
                },
                500: function() {
                  alert(status500);
                }
              },
              success: function(data) {
                abel.contents().find('.post-tags-spinner').hide();
                if (data.error === undefined) {
                  var new_tags_html = original_tags_html;
                  var n = 2;
                  
                  $(new_tags_html[n]).empty();
                  
                  var tags = data.tags.split(' ');
                  for (var i=0; i<tags.length; i++) {
                    var li = $(document.createElement('li'))
                    .append($(document.createElement('a'))
                      .attr('href', BASE_URL+'search-tags/'+tags[i])
                      .attr('target','_blank')
                      .append(tags[i])
                    );
                    if (i == tags.length-1) {
                      li.addClass('last');
                    }
                    $(new_tags_html[n]).append(li);
                  }
                  
                  abel.empty().append(new_tags_html);
                } else {
                  alert(data.error);
                }
              }
            });
          } else {
            abel.empty().append(original_tags_html);
          }
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
        })
    );
    
    var spinner = $(document.createElement('li'))
    .append($(document.createElement('img')).attr('src', BASE_URL+'images/spinner.gif'))
    .attr('class','post-tags-spinner')
    .hide();

    var commander = $(document.createElement('ul'))
    .attr('class','post-tags-commander')
    .append(chars_left)
    .append(submit_button)
    .append(cancel_button)
    .append(spinner);

    abel.append(commander);
  });
  
  /**
   * POST ROW DESCRIPTION EDITOR
   */
  $('div.post-row div.post-description p').click(function() {
    var abel = $(this).parent();
    var old_description = $(this).text();
    var original_description_html = abel.contents().clone(true);
    var post_id = abel.parent().attr('id').replace(/[a-z\-]/g,'');
    
    var editor = $(document.createElement('textarea'))
    .attr('class','post-description-textarea')
    .append(old_description)
    .blur(function() { $(this).val($(this).val().trim().replace(/\s{2,}/,' ')); })
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
          abel.contents().find('.post-description-spinner').show();
          var new_description = abel.children('textarea').val().trim().replace(/\s{2,}/,' ');
          if (new_description.length > 0 && new_description.length <= 250 && new_description != old_description) {
            $.ajax({
              dataType: 'json',
              url: BASE_URL+'admin/update/description/'+post_id,
              type: 'POST',
              data: {
                description: new_description
              },
              statusCode: {
                400: function() {
                  alert(status400);
                },
                404: function() {
                  alert(status404);
                },
                500: function() {
                  alert(status500);
                }
              },
              success: function(data) {
                abel.contents().find('.post-description-spinner').hide();
                if (data.error === undefined) {
                  var new_description_html = original_description_html;
                  var n = 0;
                  
                  $(new_description_html[n]).text(data.description);
                  
                  abel.empty().append(new_description_html);
                } else {
                  alert(data.error);
                }
              }
            });
          } else {
            abel.empty().append(original_description_html);
          }
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
          abel.empty().append(original_description_html);
        })
    );
    
    var spinner = $(document.createElement('li'))
    .append($(document.createElement('img')).attr('src', BASE_URL+'images/spinner.gif'))
    .attr('class','post-description-spinner')
    .hide();

    var commander = $(document.createElement('ul'))
    .attr('class','post-description-commander')
    .append(chars_left)
    .append(submit_button)
    .append(cancel_button)
    .append(spinner);

    abel.append(commander);
  });
  
  /**
   * POST ROW DIRECTORY EDITOR
   */
  $('div.post-row div.post-meta ul li.post-path .post-directory').click(function() {
    var abel = $(this).parent();
    var old_directory = $(this).text();
    var original_directory_html = abel.contents().clone(true);
    var post_id = abel.parent().parent().parent().attr('id').replace(/[a-z\-]/g,'');
    
    var editor = $(document.createElement('input'))
    .attr('type','text')
    .attr('class','post-directory-input')
    .attr('value', old_directory)
    .blur(function() { $(this).val($(this).val().trim().replace(/\s{2,}/,' ')); })
    .bind('keyup blur', function(event) {
      var chars_left = 250 - $(this).val().length;
      abel.contents().find('.chars-left').text(chars_left);
    });
    
    abel.children('.post-directory').remove();
    abel.append(editor);
    
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
          abel.contents().find('.post-directory-spinner').show();
          var new_directory = abel.children('input').val().trim().replace(/\s{2,}/,' ');
          if (new_directory.length > 0 && new_directory.length <= 250 && new_directory != old_directory) {
            $.ajax({
              dataType: 'json',
              url: BASE_URL+'admin/update/directory/'+post_id,
              type: 'POST',
              data: {
                directory: new_directory
              },
              statusCode: {
                400: function() {
                  alert(status400);
                },
                404: function() {
                  alert(status404);
                },
                500: function() {
                  alert(status500);
                }
              },
              success: function(data) {
                abel.contents().find('.post-directory-spinner').hide();
                if (data.error === undefined) {
                  var new_directory_html = original_directory_html;
                  var n = 2;
                  
                  $(new_directory_html[n]).text(new_directory);
                  abel.empty().append(new_directory_html);
                } else {
                  alert(data.error);
                }
              }
            });
          } else {
            abel.empty().append(original_directory_html);
          }
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
          abel.empty().append(original_directory_html);
        })
    );
    
    var spinner = $(document.createElement('li'))
    .append($(document.createElement('img')).attr('src', BASE_URL+'images/spinner.gif'))
    .attr('class','post-directory-spinner')
    .hide();

    var commander = $(document.createElement('ul'))
    .attr('class','post-directory-commander')
    .append(chars_left)
    .append(submit_button)
    .append(cancel_button)
    .append(spinner);

    abel.append(commander);
  });
});