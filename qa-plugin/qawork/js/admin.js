$(document).ready(function(){

	// Styling
	$( "#qw_bg_select" ).change(function() {
		if ($( "#qw_bg_select" ).val()=='bg_color')
			$( "#bg-color-container" ).show(500);
		else
			$( "#bg-color-container" ).hide(500);
	});
	$( "#qw_enble_back_to_top" ).change(function() {
		$( "#back_to_top_location_container" ).toggle(500);
	});
	$( "#qw_enable_adv_list" ).change(function() {
		$( "#ads_container" ).toggle(500);
	});
	
	$('#add_adv').on('click', function(e){
		e.preventDefault();
		var ads_list_count =  Number($("#adv_number").val()) + 1;
		questions_list_count =  Number($("#question_list_count").val());
		var list_options = '';
		for(var i=1; i<= questions_list_count; i++) {
			list_options += '<option value="' + i + '">' + i + '</option>';
		}
		adv_count =  Number($("#adv_number").val()) + 1;
		$("input[name=adv_number]").val(adv_count);
		$("#ads_container").append('<tr id="adv_box_' + adv_count + '"><th class="qa-form-tall-label th_' + adv_count + '">Advertisment #' + adv_count + '<span class="description">static advertisement</span></th><td class="qa-form-tall-data"><div class="clearfix"></div><img id="adv_preview_' + adv_count + '" src="" class="adv-preview img-thumbnail" style="display:none;"><div class="clearfix"></div><div id="adv_image_uploader_' + adv_count + '">Upload Icon</div><input type="hidden" value="" id="social_image_url_' + adv_count + '" name="social_image_url_' + adv_count + '"><span class="description">Image Title</span><input class="qa-form-tall-text" type="text" id="adv_image_title_' + adv_count + '" name="adv_image_title_' + adv_count + '" value=""><span class="description">Image link</span><input class="qa-form-tall-text" id="adv_image_link_' + adv_count + '" name="adv_image_link_' + adv_count + '" type="text" value=""><span class="description">Display After this number of questions</span><select id="adv_location_' + adv_count + '" name="adv_location_' + adv_count + '" class="qa-form-wide-select">' + list_options + '</select><button advid="' + adv_count + '" id="advremove" name="advremove" class="qa-form-tall-button advremove pull-right btn" type="submit">Remove This Advertisement</button></td></tr>');
		$('html, body').animate({
			scrollTop: $(".th_" + adv_count).offset().top
		}, 800);
		build_advertisement_uploader(adv_count);
	});
	$('#add_adsense').on('click', function(e){
		e.preventDefault();
		var ads_list_count =  Number($("#adv_number").val()) + 1;
		questions_list_count =  Number($("#question_list_count").val());
		var list_options = '';
		for(var i=1; i<= questions_list_count; i++) {
			list_options += '<option value="' + i + '">' + i + '</option>';
		}
		adv_count =  Number($("#adv_number").val()) + 1;
		$("input[name=adv_number]").val(adv_count);
		$("#ads_container").append('<tr id="adv_box_' + adv_count + '"><th class="qa-form-tall-label th_' + adv_count + '">Advertisment #' + adv_count + '<span class="description">Google Adsense Code</span></th><td class="qa-form-tall-data"><input class="qa-form-tall-text" id="adv_adsense_' + adv_count + '" name="adv_adsense_' + adv_count + '" type="text" value=""><span class="description">Display After this number of questions</span><select id="adv_location_' + adv_count + '" name="adv_location_' + adv_count + '" class="qa-form-wide-select">' + list_options + '</select><button advid="' + adv_count + '" id="advremove" name="advremove" class="qa-form-tall-button advremove pull-right btn" type="submit">Remove This Advertisement</button></td></tr>');    
		$('html, body').animate({
			scrollTop: $(".th_" + adv_count).offset().top
		}, 800);
	});
	$( document ).delegate( ".advremove", "click", function(e) {
		e.preventDefault();
		var adv_frame = $(this).parent().parent();
		adv_list_count =  Number($("#adv_number").val()) - 1;
		$("input[name=adv_number]").val(adv_list_count);
		adv_frame.fadeOut(500);
		setTimeout(function() {
			adv_frame.unbind();
			adv_frame.remove();
		}, 600);
	});
	

	$('#add_social').on('click', function(e){
		e.preventDefault();
		var social_list_count =  Number($("#social_count").val()) + 1;
		var list_options = '<option value="1">Upload Social Icon</option>';
		list_options += '<option value="icon-facebook" class="icon-facebook">Faebook</option>';
		list_options += '<option value="icon-twitter" class="icon-twitter">Twitter</option>';
		list_options += '<option value="icon-google" class="icon-google">Google</option>';
		
		$("input[name=social_count]").val(social_list_count);
		$("#social_container").append('<tr id="social_box_' + social_list_count + '"><th class="qa-form-tall-label social_th_' + social_list_count + '">Social Link #' + social_list_count + '<span class="description">choose Icon and link to your social profile</span></th><td class="qa-form-tall-data"><span class="description">Social Profile Link</span><input class="qa-form-tall-text" id="social_link_' + social_list_count + '" name="social_link_' + social_list_count + '" type="text" value=""><span class="description">Link Title</span><input class="qa-form-tall-text" type="text" id="social_title_' + social_list_count + '" name="social_title_' + social_list_count + '" value=""><span class="description">Choose Social Icon</span><select id="social_icon_' + social_list_count + '" name="social_icon_' + social_list_count + '" fieldid="' + social_list_count + '" class="qa-form-wide-select social-select">' + list_options + '</select><div class="social_icon_file_' + social_list_count + '"><span class="description">upload Social Icon</span><img id="social_image_preview_' + social_list_count + '" src="" class="social-preview img-thumbnail" style="display:none;"><div id="social_image_uploader_' + social_list_count + '">Upload Icon</div><input type="hidden" value="" id="social_image_url_' + social_list_count + '" name="social_image_url_' + social_list_count + '"><button advid="' + social_list_count + '" id="social_remove" name="social_remove" class="qa-form-tall-button social_remove pull-right btn" type="submit">Remove This Link</button></td></tr>');
		$('html, body').animate({
			scrollTop: $(".social_th_" + social_list_count).offset().top
		}, 800);
		build_social_uploader(social_list_count);
	});
	$( ".social-select" ).change(function() {
		if ($(this).val()==1)
			$('.social_icon_file_' + $(this).attr('sociallistid')).show(500);
		else 
			$('.social_icon_file_' + $(this).attr('sociallistid')).hide(500);
	});
	$( document ).delegate( ".social_remove", "click", function(e) {
		e.preventDefault();
		var social_frame = $(this).parent().parent();
		social_list_count =  Number($("#social_count").val()) - 1;
		$("input[name=social_count]").val(social_list_count);
		social_frame.fadeOut(500);
		setTimeout(function() {
			social_frame.unbind();
			social_frame.remove();
		}, 600);
	});
	
	$('input[type="colorpicker"]').spectrum({
		allowEmpty: true,
		preferredFormat: "name",
		showInput: true,
		showButtons: false,
		clickoutFiresChange: true,
		showInitial: true,
		showAlpha: true,
		showPalette: true,
		palette: [
			["rgb(0, 0, 0)", "rgb(67, 67, 67)", "rgb(102, 102, 102)", /*"rgb(153, 153, 153)","rgb(183, 183, 183)",*/
			"rgb(204, 204, 204)", "rgb(217, 217, 217)", /*"rgb(239, 239, 239)", "rgb(243, 243, 243)",*/ "rgb(255, 255, 255)"],
			["rgb(152, 0, 0)", "rgb(255, 0, 0)", "rgb(255, 153, 0)", "rgb(255, 255, 0)", "rgb(0, 255, 0)",
			"rgb(0, 255, 255)", "rgb(74, 134, 232)", "rgb(0, 0, 255)", "rgb(153, 0, 255)", "rgb(255, 0, 255)"],
			["rgb(230, 184, 175)", "rgb(244, 204, 204)", "rgb(252, 229, 205)", "rgb(255, 242, 204)", "rgb(217, 234, 211)",
			"rgb(208, 224, 227)", "rgb(201, 218, 248)", "rgb(207, 226, 243)", "rgb(217, 210, 233)", "rgb(234, 209, 220)",
			"rgb(221, 126, 107)", "rgb(234, 153, 153)", "rgb(249, 203, 156)", "rgb(255, 229, 153)", "rgb(182, 215, 168)",
			"rgb(162, 196, 201)", "rgb(164, 194, 244)", "rgb(159, 197, 232)", "rgb(180, 167, 214)", "rgb(213, 166, 189)",
			"rgb(204, 65, 37)", "rgb(224, 102, 102)", "rgb(246, 178, 107)", "rgb(255, 217, 102)", "rgb(147, 196, 125)",
			"rgb(118, 165, 175)", "rgb(109, 158, 235)", "rgb(111, 168, 220)", "rgb(142, 124, 195)", "rgb(194, 123, 160)",
			"rgb(166, 28, 0)", "rgb(204, 0, 0)", "rgb(230, 145, 56)", "rgb(241, 194, 50)", "rgb(106, 168, 79)",
			"rgb(69, 129, 142)", "rgb(60, 120, 216)", "rgb(61, 133, 198)", "rgb(103, 78, 167)", "rgb(166, 77, 121)",
			/*"rgb(133, 32, 12)", "rgb(153, 0, 0)", "rgb(180, 95, 6)", "rgb(191, 144, 0)", "rgb(56, 118, 29)",
			"rgb(19, 79, 92)", "rgb(17, 85, 204)", "rgb(11, 83, 148)", "rgb(53, 28, 117)", "rgb(116, 27, 71)",*/
			"rgb(91, 15, 0)", "rgb(102, 0, 0)", "rgb(120, 63, 4)", "rgb(127, 96, 0)", "rgb(39, 78, 19)",
			"rgb(12, 52, 61)", "rgb(28, 69, 135)", "rgb(7, 55, 99)", "rgb(32, 18, 77)", "rgb(76, 17, 48)"]
		]
	});
	
	$('[data-opts]').click(function(){
		$($(this).data('opts')).toggle();
	});
	
	$('body').on('qw_insert_image', function(e, params){
		console.log(params);
		if(!!params['for'] && params['for'] == 'logo'){
			$('#logo-preview').attr('src', params['url']).show();
			$('#qw_logo_field').attr('value', params['url']);
		}
		if(!!params['for'] && params['for'] == 'favicon'){
			$('#favicon-preview').attr('src', params['url']).show();
			$('#qw_favicon_field').attr('value', params['url']);
		}
		if(!!params['for'] && params['for'] == 'big-favicon'){
			$('#favicon-preview-big').attr('src', params['url']).show();
			$('#qw_favicon_big_field').attr('value', params['url']);
		}
	});
});