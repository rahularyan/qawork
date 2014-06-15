<?php
/* don't allow this page to be requested directly from browser */	
if (!defined('QA_VERSION')) {
		header('Location: /');
		exit;
}
return array(
	"yes"                        => "Yes" ,
	"no"                         => "No" ,
	"light"                      => "Light" ,
	"dark"                       => "Dark" ,
	"qw_gp_badge_url_lable"      => "URL to the Google+ page" ,
	"layout_label"               => "Orientation of the badge" ,
	"portrait"                   => "Portrait" ,
	"landscape"                  => "Landscape" ,
	"qw_gp_showcoverphoto_label" => "Displays the cover photo in the badge if set to true and the photo exists." ,
	"showtagline_label"          => "Displays the user's tag line if set to true." ,
	"theme_label"                => "The color theme of the badge. Use dark when placing the badge on a page with a dark background." ,
	"qw_gp_badge_width_label"    => "The pixel width of the badge to render.(Recomended 360px )" ,
	"qw_gp_badge_type_lable"     => "Versions of the badges" ,
	"profile"                    => "Profile" ,
	"page"                       => "Page" ,
	"community"                  => "Community" ,
	"qw_gp_show_owners_label"    => "Displays a list of community owners if set to true. (only applicable for communities )" ,
	"qw_gp_showphoto_label"      => "Displays a list of community owners if set to true. (only applicable for communities )" ,
	"gp_badge"                   => "Google Plus Badge" ,
	);

