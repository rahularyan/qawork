// helper functions to post to facebook wall and share some links 



function cs_init_facebook_api (applicationId) {
	if (!FB.cs_initialized) {
		FB.init({
                  appId: applicationId ,
                  cookie: true,
                  status: true,
                  xfbml: true
   		});
		//setting my costum marker to make sure init is invoked once and only once for a page load 
   		FB.cs_initialized = true ; 
	}
	
}
/**
 * sample param
 * name: 'Some Name ',
   link: 'a link ',
 */
// https://developers.facebook.com/docs/reference/dialogs/send/
function cs_share_link_to_facebook(applicationId , param) { 
	// first of all initialize facebook api
	cs_init_facebook_api(applicationId) ;
	param.method = 'send' ;
	FB.ui(param);
}

/**
 * sample param
 * method: 'apprequests', 
   message: 'message'
 */
// https://developers.facebook.com/docs/reference/dialogs/requests/
function cs_invite_facebook_friends(applicationId , param) { 
	// first of all initialize facebook api
	cs_init_facebook_api(applicationId) ; 
	param.method = 'apprequests' ;
	FB.ui(param);
}

// https://developers.facebook.com/docs/reference/dialogs/feed/
/**
 *  method: 'feed',
	link: 'url to post',
	picture: 'pic url to post ',
	name: 'some name ',
	caption: 'caption',
	description: 'desc.'
 */
function cs_post_to_facebook_wall(applicationId , param) { 
	// first of all initialize facebook api
	cs_init_facebook_api(applicationId) ; 
	param.method = 'feed' ;
	function callback(response) {
	// kept empty for future purposes 
	}
	FB.ui(param, callback);
}
