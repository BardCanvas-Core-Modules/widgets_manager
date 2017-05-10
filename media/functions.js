
function prepare_widget_edition_serialization($form, options)
{
    $form.find('textarea.CodeMirror').each(function()
    {
        var $this  = $(this);
        var editor = $this.data('editor');
        var val    = editor.getValue();
        
        $this.val(val);
    });
}

function prepare_widget_edition_submission(formData, $form, options)
{
    $('#widget_edition_dialog').block(blockUI_default_params);
}

function process_widget_edition_response(response, statusText, xhr, $form)
{
    $('body').css('overflow', 'auto');
    var $dialog = $('#widget_edition_dialog');
    var $widget = $dialog.data('widget');
    
    if( response.indexOf('OK:') != 0 )
    {
        $dialog.unblock();
        
        alert( response );
        return;
    }
    
    $dialog.unblock();
    $widget.toggleClass('modified', true);
    $widget.find('.update_target').html( response.substring(3) );
    $widget.find('.title').toggleClass('greengo', true);
    cancel_widget_edition( $dialog );
}

function cancel_widget_edition( $dialog )
{
    var $widget = $dialog.data('widget');
    
    $dialog.dialog('destroy').remove();
    $widget.attr('data-is-being-edited', 'false');
}

function render_widget_editing_form(trigger, sidebar, widget_handler, seed)
{
    var $trigger = $(trigger);
    var $widget  = $trigger.closest('.widget');
    var data     = $widget.find('input[data-container="true"]').val();
    var state    = $widget.find('input[data-state="true"]').val();
    
    var url    = $_FULL_ROOT_PATH + '/widgets_manager/scripts/render_edit_form.php';
    var params = {
        sidebar: sidebar,
        id:      widget_handler,
        seed:    seed,
        state:   state,
        data:    data
    };
    
    $widget.attr('data-is-being-edited', 'true');
    $widget.block(blockUI_smallest_params);
    $.post(url, params, function(response)
    {
        $widget.unblock();
        
        if( response.indexOf('OK:') != 0 )
        {
            alert( response );
            $widget.attr('data-is-being-edited', 'false');
            
            return;
        }
        
        var width  = $(window).width()  - 20; if( width  > 540 ) width  = 540;
        var height = $(window).height() - 20; if( height > 800 ) height = 800;
        
        var html = '<div id="widget_edition_dialog" style="display: none">'
                + response.substring(3)
                + '</div>';
        $('body').append(html);
        
        var $dialog = $('#widget_edition_dialog');
        var $form   = $('#widget_edition_form');
        
        $dialog.data('widget', $widget);
        $form.ajaxForm({
            target:          '#widget_edition_target',
            beforeSerialize: prepare_widget_edition_serialization,
            beforeSubmit:    prepare_widget_edition_submission,
            success:         process_widget_edition_response
        });
        $dialog.dialog({
            modal:   true,
            width:   width,
            height:  height,
            title:   weditor_title,
            open:    function() { $('body').css('overflow', 'hidden'); prepare_widget_dialog_form_controls( $(this) ); },
            close:   function() { $('body').css('overflow', 'auto'); cancel_widget_edition( $(this) ); },
            buttons: [
                {
                    text:  weditor_ok_caption,
                    icons: { primary: "ui-icon-check" },
                    click: function() { $('#widget_edition_form').submit(); }
                }, {
                    text:  weditor_cancel_caption,
                    icons: { primary: "ui-icon-cancel" },
                    click: function() { $(this).dialog('close'); }
                }
            ]
        })
    });
}

function prepare_widget_dialog_form_controls($dialog)
{
    $dialog.find('textarea.CodeMirror:not(.wrapped)').each(function()
    {
        var element = $(this).get(0);
        
        var editor = CodeMirror.fromTextArea(element, {
            // viewPortMargin: Infinity,
            lineNumbers:    true,
            mode:           'htmlmixed'
        });
        $(this).data('editor', editor);
    });
    
    $dialog.find('textarea.CodeMirror.wrapped').each(function()
    {
        var element = $(this).get(0);
        
        var editor = CodeMirror.fromTextArea(element, {
            // viewPortMargin: Infinity,
            lineNumbers:    true,
            lineWrapping:   true,
            mode:           'htmlmixed'
        });
        $(this).data('editor', editor);
    });
    
    $dialog.find('textarea.expandible_textarea').expandingTextArea();
}

function toggle_widget_state_class(trigger)
{
    var $trigger  = $(trigger);
    var $widget   = $trigger.closest('.widget');
    var value     = $trigger.find('input').val();
    var set_class = value == 'enabled' ? 'state_active' : 'state_disabled';
    $widget.toggleClass('state_disabled state_active', false).toggleClass(set_class, true);
}

function add_page_tag_input_box()
{
    $('#widget_edition_form').find('.missing_page_tags').append(
        '<div class="input missing_page_tag_entry">\n' +
        '    <input type="text" name="page_tags[]" value="">\n' +
        '    <button onclick="$(this).closest(\'div\').fadeOut(\'fast\', function() { $(this).remove(); }); return false;">\n' +
        '        <i class="fa fa-trash"></i>\n' +
        '    </button>\n' +
        '</div>\n'
    );
    
}

function prepare_widgets_form_submission(formData, $form, options)
{
    $form.block(blockUI_default_params);
    stop_notifications_getter();
}

function process_widgets_form_response(response, statusText, xhr, $form)
{
    if( response != 'OK' )
    {
        $form.unblock();
        alert(response);
        start_notifications_getter();
        
        return;
    }
    
    location.href = $_PHP_SELF + '?wasuuup=' + wasuuup();
}

function delete_widget(trigger)
{
    if( ! confirm($_GENERIC_CONFIRMATION) ) return;
    
    var sidebar = $(trigger).closest('ul').attr('data-sidebar');
    
    $(trigger).closest('.widget').fadeOut('fast', function()
    {
        $(this).remove();
        eval_toggling_no_widgets_for_sidebar(sidebar);
    });
}

function show_widget_addition_form(sidebar)
{
    var url = $_FULL_ROOT_PATH + '/widgets_manager/scripts/render_active_widgets_selector.php';
    var params = {
        sidebar: sidebar,
        wasuuup: wasuuup()
    };
    
    $.blockUI(blockUI_default_params);
    $.get(url, params, function(response)
    {
        $.unblockUI();
        
        var html = '<div id="new_widget_selector" style="display: none;">' + response + '</div>';
        $('body').append(html);
        
        var width   = $(window).width()  - 20; if(width  > 500) width  = 500;
        var height  = $(window).height() - 20; if(height > 600) height = 600;
        var $dialog = $('#new_widget_selector');
        $dialog.dialog({
            modal:     true,
            width:     width,
            maxHeight: height,
            title:     (sidebar == 'left_sidebar' ? wcreator_title_left : wcreator_title_right),
            open:      function() { $('body').css('overflow', 'hidden'); },
            close:     function() { $('body').css('overflow', 'auto'); $(this).dialog('destroy').remove(); },
            buttons:   [
                {
                    text:  weditor_ok_caption,
                    icons: { primary: "ui-icon-check" },
                    click: function() { insert_selected_widget(sidebar); }
                }, {
                    text:  cancel_caption,
                    icons: { primary: "ui-icon-cancel" },
                    click: function() { $(this).dialog('close'); }
                }
            ]
        })
    });
}

function insert_selected_widget(sidebar)
{
    var $dialog   = $('#new_widget_selector');
    var $selected = $dialog.find('input[type="radio"]:checked');
    var markup    = $selected.closest('.field').find('.markup').html();
    
    $dialog.dialog('close');
    $('body').css('overflow', 'auto');
    
    markup = $('<textarea />').html(markup).text();
    var $ul = $('ul[data-sidebar="' + sidebar + '"]');
    $ul.append(markup);
    $ul.sortable('refresh');
    eval_toggling_no_widgets_for_sidebar(sidebar);
    
    set_engine_pref('widgets_manager_widget_editor_basics', '', function()
    {
        $ul.find('li:last').find('button[data-action="edit"]').click();
    });
}

function eval_toggling_no_widgets_for_sidebar(sidebar)
{
    var $ul = $('ul[data-sidebar="' + sidebar + '"]');
    if( $ul.find('li').length == 0 ) $ul.closest('section').find('.no_widgets').show();
    else                             $ul.closest('section').find('.no_widgets').hide();
}

$(document).ready(function()
{
    $('#left_widgets').sortable({handle: '.handle'});
    $('#right_widgets').sortable({handle: '.handle'});
    
    $('#sidebar_widgets_form').ajaxForm({
        target:       '#sidebar_widgets_target',
        beforeSubmit: prepare_widgets_form_submission,
        success:      process_widgets_form_response
    })
});
