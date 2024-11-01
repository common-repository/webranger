jQuery(document).ready(function(){
    jQuery('#btn-rng').on('click',function(){
        var text = "";
        var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

        for( var i=0; i < 10; i++ )
            text += possible.charAt(Math.floor(Math.random() * possible.length));

        jQuery('#wr_sensor_key').val(text);
    });

    jQuery('#btn-wr-link').on('click',function(){
      var win = window.open("https://webranger.pandoralabs.net", '_blank');
      win.focus();
    });
});

function confirmReset() 
{
    if (confirm("Clicking OK will reset WebRanger. Are you sure you want to proceed?") === true) 
    {
       return true;
    } 
    else 
    {
       return false;
    }
}

function httpGetAsync()
{
    var xmlHttp = new XMLHttpRequest();
    var host = "http://"+window.location.hostname+"?s='or1=1'";

    xmlHttp.onreadystatechange = function() 
    { 
        if (xmlHttp.readyState == 4 && xmlHttp.status == 200)
        {
          alert("Test is done. Check your WebRanger console for events.");
        }
        else if(xmlHttp.readyState == 4 && xmlHttp.status != 200)
        {
          alert("Fail in sending test request.");
        }
    }
    xmlHttp.open("GET", host, true); // true for asynchronous 
    xmlHttp.send(null);
}


