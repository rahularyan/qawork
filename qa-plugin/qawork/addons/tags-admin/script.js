$(document).ready(function(){

	var qw_active_tag_to_edit,
		qw_tag_edit_code,
		qw_active_elm_on_tags,
		successMessage = '<strong>Success!</strong> The tag description has been saved.' ,
		errorMessage = '<strong>Ooopss!</strong> Server Error occured ' ,
		ajaxResponseElem = $('#tags-edit-modal .ta-ajax-response') , 
		prevDesc ;
	$('.edit-tag-item').click(function(e){
		e.preventDefault();
		qw_active_elm_on_tags = $(this);
		qw_active_tag_to_edit = $(this).data('tag');
		qw_tag_edit_code = $(this).closest('.tags-edit-list').data('code');
		prevDesc = $(this).next('p').text() ;
		$('#tag-modal-label span').text(qw_active_tag_to_edit);
		$('#tags-edit-modal input[name="title"]').val( qw_active_tag_to_edit);
		$('#tags-edit-modal textarea[name="description"]').val(prevDesc);
		ajaxResponseElem.not('.hidden').addClass('hidden');
		$('#tags-edit-modal').modal('toggle');
	});
	$('#save-tags').click(function(){
		var $btn = $(this) , 
			desc = $('#tags-edit-modal textarea[name="description"]').val() ; 
		if (prevDesc === desc) {
			return ;
		};
    	$btn.button('loading') ;
		ajaxResponseElem.not('.hidden').addClass('hidden');
		$.ajax({
			type:'POST',
			url:ajax_url,
			data: {
				action: 'save_tags',
				code: qw_tag_edit_code,
				tag: qw_active_tag_to_edit,
				description: desc ,
			},
			dataType: 'html',
			context:this,
		})
		.done(function (response) {		
				prevDesc = response ;		
				if(qw_active_elm_on_tags.next().is('p')){
					qw_active_elm_on_tags.next('p').text(response);
				}
				else{
					$('<p>'+response+'</p>').insertAfter(qw_active_elm_on_tags);
				}

				if (ajaxResponseElem.hasClass('alert-danger')) {
					ajaxResponseElem.removeClass('alert-danger');
				};
				ajaxResponseElem.addClass('alert-success');
				ajaxResponseElem.children('p.message').html(successMessage);
				if (ajaxResponseElem.hasClass('hidden')) {
					ajaxResponseElem.removeClass('hidden');
				};
			})
		.fail(function(response) {
				if (ajaxResponseElem.hasClass('alert-success')) {
					ajaxResponseElem.removeClass('alert-success');
				};
				ajaxResponseElem.addClass('alert-danger');
				ajaxResponseElem.children('p.message').html(errorMessage);
				if (ajaxResponseElem.hasClass('hidden')) {
					ajaxResponseElem.removeClass('hidden');
				};
		})
		.always(function() {
			$btn.button('reset');
		});
	});
});