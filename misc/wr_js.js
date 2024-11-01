jQuery(document).ready(function(){
	jQuery("#wr_confirm_password").keyup(checkPasswordMatch);
	jQuery('#desc1').hide();
	jQuery('#desc3').hide();
	jQuery('#divCheckPasswordMatch').val(1);

	jQuery('#btn-link').on('click',function(){
	  var win = window.open("https://webranger.pandoralabs.net", '_blank');
	  win.focus();
	});

	jQuery('#wr-form').submit(function (e){
		var pass_confirm = jQuery("#divCheckPasswordMatch").val();
		if(pass_confirm == 0)
		{
			alert("Passwords do not match.");
			e.preventDefault();
		}
	});

	jQuery('#option-1').on('click',function(){
		jQuery('#cp_tracker').hide(400);
		jQuery('#fname_tracker').hide(400);
		jQuery('#lname_tracker').hide(400);
		jQuery('#divCheckPasswordMatch').hide(400);

		jQuery('#desc2').hide(400);
		jQuery('#desc3').hide(400);
		jQuery('#desc1').show(400);

		jQuery('#wr_title_tracker').show(400);
		jQuery('#wr_email_tracker').show(400);
		jQuery('#wr_password_tracker').show(400);
		jQuery('#btn-rng').show(400);


		jQuery('#wr_email').prop('required',true);
		jQuery('#wr_password').prop('required',true);
		jQuery('#wr_confirm_password').prop('required',false);
		jQuery('#wr_first_name').prop('required',false);
		jQuery('#wr_last_name').prop('required',false);
		jQuery('#divCheckPasswordMatch').val(1);
	});

	jQuery('#option-2').on('click',function(){
		jQuery('#desc1').hide(400);
		jQuery('#desc3').hide(400);
		jQuery('#desc2').show(400);

		jQuery('#cp_tracker').show(400);
		jQuery('#fname_tracker').show(400);
		jQuery('#lname_tracker').show(400);
		jQuery('#divCheckPasswordMatch').show(400);
		jQuery('#wr_title_tracker').show(400);
		jQuery('#wr_email_tracker').show(400);
		jQuery('#wr_password_tracker').show(400);
		jQuery('#btn-rng').show(400);

		jQuery('#wr_confirm_password').prop('required',true);
		jQuery('#wr_first_name').prop('required',true);
		jQuery('#wr_last_name').prop('required',true);
		jQuery('#divCheckPasswordMatch').val(0);
	});

	jQuery('#option-3').on('click',function(){
		jQuery('#desc1').hide(400);
		jQuery('#desc2').hide(400);
		jQuery('#desc3').show(400);

		jQuery('#btn-rng').hide(400);
		jQuery('#cp_tracker').hide(400);
		jQuery('#fname_tracker').hide(400);
		jQuery('#lname_tracker').hide(400);
		jQuery('#divCheckPasswordMatch').hide(400);
		jQuery('#wr_title_tracker').hide(400);
		jQuery('#wr_email_tracker').hide(400);
		jQuery('#wr_password_tracker').hide(400);

		jQuery('#wr_email').prop('required',false);
		jQuery('#wr_password').prop('required',false);
		jQuery('#wr_confirm_password').prop('required',false);
		jQuery('#wr_first_name').prop('required',false);
		jQuery('#wr_last_name').prop('required',false);
		jQuery('#divCheckPasswordMatch').val(1);
	});

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

function checkPasswordMatch() {
    var password = jQuery("#wr_password").val();
    var confirmPassword = jQuery("#wr_confirm_password").val();

    if (password != confirmPassword)
        {
		jQuery("#divCheckPasswordMatch").html("Passwords do not match.").val(0).css({color:'red'});
	}
    else
        jQuery("#divCheckPasswordMatch").html("Passwords match.").val(1).css({color:'green'});
}