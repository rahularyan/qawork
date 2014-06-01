var scripts = document.getElementsByTagName('script');
var myScript = scripts[ scripts.length - 1 ];
var queryString = myScript.src.replace(/^[^\?]+\??/,'');
var params = parseQuery( queryString );
var applicationId = params.applicationId ;

(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId="+applicationId ;
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));

function parseQuery ( query ) {
   var params = new Object();
   if ( ! query ) return params; // return empty object
   var pairs = query.split(/[;&]/);
   for ( var i = 0; i < pairs.length; i++ ) {
      var keyval = pairs[i].split('=');
      if ( ! keyval || keyval.length != 2 ) continue;
      var key = unescape( keyval[0] );
      var val = unescape( keyval[1] );
      val = val.replace(/\+/g, ' ');
      params[key] = val;
   }
   return params;
}
