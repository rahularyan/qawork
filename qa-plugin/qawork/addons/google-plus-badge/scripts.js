(function() {
     // load the script asynchroneously
     $.getScript("https://apis.google.com/js/plusone.js", function () {});
})();

(function() {
    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
    po.src = 'https://apis.google.com/js/platform.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
  })();