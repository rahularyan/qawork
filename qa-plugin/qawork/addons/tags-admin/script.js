$(document).ready(function(){

	var qw_active_tag_to_edit,
		qw_tag_edit_code,
		qw_active_elm_on_tags;
	
	$('.edit-tag-item').click(function(e){
		e.preventDefault();
		
		
		qw_active_elm_on_tags = $(this);
		qw_active_tag_to_edit = $(this).data('tag');
		qw_tag_edit_code = $(this).closest('.tags-edit-list').data('code');

		$('#tag-modal-label span').text(qw_active_tag_to_edit);
		$('#tags-edit-modal input[name="title"]').val( qw_active_tag_to_edit);
		$('#tags-edit-modal textarea[name="description"]').val($(this).next('p').text());
		$('#tags-edit-modal').modal('toggle');
		
		
	
	});
	$('#save-tags').click(function(){
		qw_animate_button(this);
		$.ajax({
			type:'POST',
			url:ajax_url,
			data: {
				action: 'save_tags',
				code: qw_tag_edit_code,
				tag: qw_active_tag_to_edit,
				description: $('#tags-edit-modal textarea[name="description"]').val(),
			},
			dataType: 'html',
			context:this,
			success: function (response) {				
				qw_remove_animate_button(this);
				
				if(qw_active_elm_on_tags.next().is('p'))
					qw_active_elm_on_tags.next('p').text(response);
				else
					$('<p>'+response+'</p>').insertAfter(qw_active_elm_on_tags);
			},
		});
	});

});