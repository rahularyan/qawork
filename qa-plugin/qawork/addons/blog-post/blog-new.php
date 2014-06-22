<?php

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
    header('Location: ../');
    exit;
}

class qw_blog_post_new {

    var $directory;
    var $urltoroot;
    var $page_url = 'blog/new';

    function load_module($directory, $urltoroot) {
        $this->directory = $directory;
        $this->urltoroot = $urltoroot;
    }

    function match_request($request) {
        if ($request == $this->page_url) return true;

        return false;
    }

    function process_request($request) {

		//	Prepare content for theme

		$qa_content=qa_content_prepare();
		$qa_content['site_title']=qa_lang_html('qw_blog_post/create_new_blog_post');
		$qa_content['title']=qa_lang_html('qw_blog_post/create_new_blog_post');
		
		$qa_content['custom']= $this->page_content();
		
		return $qa_content;			
       
    }
	
	function page_content(){
		ob_start();
		?>
			<div id="new-blog-post">
				<form>
					<div class="row">
						<div class="col-md-8">
							<?php echo qw_do_action('blog_post_form'); ?>
							<button type="submit" class="btn btn-default">Submit</button>
							<input type="hidden" value="<?php echo qa_get_form_security_code('new_blog_post'); ?>" />
						</div>
						<div class="col-md-4">
						
						</div>
				</form>
			</div>
		<?php		
		return ob_get_clean();
	}

}

/*
	Omit PHP closing tag to help avoid accidental output
*/