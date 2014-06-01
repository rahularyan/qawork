// helper functions to post to facebook wall and share some links 

function cs_init_facebook_api (applicationId , ajaxCallback , ajaxCallbackParam ) {
	// reutrn if applicationId is not set 
	if (!applicationId) {return false };
	if (typeof (FB) == 'undefined') {
		$.getScript("http://connect.facebook.net/en_US/all.js#xfbml=1", function () {
            if (!FB.cs_initialized) {
	            FB.init({
	                  appId: applicationId ,
	                  cookie: true,
	                  status: true,
	                  xfbml: true
		   		});
				//setting my costum marker to make sure init is invoked once and only once for a page load 
		   		FB.cs_initialized = true ; 
		   		ajaxCallback(applicationId , ajaxCallbackParam ) ;
	   		}
        });
	} 
	
}
/**
 * to login to facebook javascript 
 * @param  {[string]} applicationId [application id]
 * @return {[null]}
 */
function cs_login_to_facebook(applicationId) {
	// reutrn if applications id is not set 
	if (!applicationId) {return false };

	// first of all initialize facebook api
	cs_init_facebook_api(applicationId , cs_login_to_facebook , param ) ;
	if (typeof (FB) != 'undefined') {
		FB.login(function(response) {
		   if (response.authResponse) {
		     console.log('Welcome!  Fetching your information.... ');
		     FB.api('/me', function(response) {
		       console.log('Good to see you, ' + response.name + '.');
		     });
		   } else {
		     console.log('User cancelled login or did not fully authorize.');
		   }
	 	});
	}
}

/**
 * sample param
 * name: 'Some Name ',
   link: 'a link ',
 */
// https://developers.facebook.com/docs/reference/dialogs/send/
function cs_share_link_to_facebook(applicationId , param) { 
	// reutrn if applications id is not set 
	if (!applicationId) {return false };

	// first of all initialize facebook api
	cs_init_facebook_api(applicationId ,cs_share_link_to_facebook , param ) ;
	if (typeof (FB) != 'undefined') {
		param.method = 'send' ;
		FB.ui(param);
	}
}

/**
 * sample param
 * method: 'apprequests', 
   message: 'message'
 */
// https://developers.facebook.com/docs/reference/dialogs/requests/
function cs_invite_facebook_friends(applicationId , param) { 
	// reutrn if applications id is not set 
	if (!applicationId) {return false };

	// first of all initialize facebook api
	cs_init_facebook_api(applicationId , cs_invite_facebook_friends , param) ; 
	if (typeof (FB) != 'undefined') {
		param.method = 'apprequests' ;
		FB.ui(param);
	}
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
	// reutrn if applications id is not set 
	if (!applicationId) {return false };
	// first of all initialize facebook api
	cs_init_facebook_api(applicationId , cs_post_to_facebook_wall , param ) ; 
	
	if (typeof (FB) != 'undefined') {
		param.method = 'feed' ;
		FB.ui(param, callback);
    }
	function callback(response) {
	// kept empty for future purposes 
	}
}
