<?php
/*
    This file is part of WebRanger.

    WebRanger is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    WebRanger is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with WebRanger.  If not, see <http://www.gnu.org/licenses/>.
*/
    
class WebRanger_View
{
	public function generate_wr_page()
	{
		#Get Options
		$options = get_option( 'wr_op_array' );
		?>
			<div class="wrap"> <!--begin class-wrap-->
      			<h2>WebRanger</h2>

      			<!--Notification Message-->
      			<div id='message' class='error notice'><p><strong>WebRanger is not yet configured.</strong></p></div>
      			
      			<?php if(isset($_GET['error_code']))
      				{?>
					<div id='message' class='error notice'><p><strong>WebRanger Error: Activation Failure</strong></p></div>
      			<?php } ?>
      		
	      			<form method="post" action="admin-post.php" id ="wr-form">
	      			<table class="form-table">

	      				<input type="hidden" name="action" value="wr_register" />

						<tr>
							<th scope="row" style = "padding:0;"><h3>Activation Method</h3></th>
							<td style = "padding:0;">
								<fieldset><legend class="screen-reader-text"><span>Activation Method</span></legend>
								<label title='New Account'><input type='radio' name='option-form' value='0'  checked='checked' id ="option-2" /> Register New Account</label><br/>
								<label style="padding-left:20px;" title='Existing Account'><input type='radio' name='option-form' value='1' id = "option-1"/> Use Existing Account</label><br />
								<label style="padding-left:20px;" title='Existing Sensor'><input type='radio' name='option-form' value='2' id ="option-3" /> Use Existing Sensor</label><br/>
								</fieldset>
							</td>
						</tr>

						<tr id = "general-tooltip">
							<td scope="row" colspan="2">
								<p id="desc1">Under this option, it is assumed you already have an <b>existing WebRanger Console account </b> and wish to subscribe a free WebRanger under the existing account.  </p>
								<p id="desc2">Under this option, it is assumed you <b>don’t have an existing WebRanger Console account and wish to signup for one</b>. This is equivalent in signing up in our <a href="https://www.pandoralabs.net/webranger/sign-up">website</a> and adding a free WebRanger under the registered account. <b>If you're unfamiliar with WebRanger, this is likely to be your option.</b></p>
								<p id="desc3">Under this option, it is assumed you <b>have an existing active WebRanger in your console account</b> and wish to activate it under this website. </p>
							</td>
						</tr>

						<tr id ="wr_title_tracker">
							<th scope="row" style = "padding: 0;"><h3 style = "margin: 0;">WebRanger Account</h3></th>
						</tr>

	      				<tr id ="wr_email_tracker">
							<th scope="row"><label for="wr_email">Email</label></th>
							<td>
									<i style="color:red;">*</i>
									<input id = "wr_email" class="regular-text" type="email" name="wr_account_email" required/>
							</td>
						</tr>

						<tr id ="wr_password_tracker">
							<th scope="row"><label for="wr_password">Password</label></th>
							<td> <i style="color:red;">*</i><input id = "wr_password" class="regular-text" type="password" name="wr_account_identification" required/></td>
						</tr>

						<tr id="cp_tracker">
							<th scope="row"><label for="wr_confirm_password">Confirm Password</label></th>
							<td>
								<i style="color:red;">*</i>
								<input id = "wr_confirm_password" class="regular-text" type="password" name="wr_confirm_password"/> 
								<div id="divCheckPasswordMatch"></div>
							</td>
						</tr>

						<tr id="fname_tracker">
							<th scope="row"><label for="wr_first_name">First Name</label></th>
							<td> <i style="color:red;">*</i><input id = "wr_first_name" class="regular-text" type="text" name="wr_account_fname"/></td> 
						</tr>

						<tr id="lname_tracker">
							<th scope="row"><label for="wr_last_name">Last Name</label></th>
							<td> <i style="color:red;">*</i> <input id = "wr_last_name" class="regular-text" type="text" name="wr_account_lname"/></td>
						</tr>

						<br>
						<tr>
							<th scope="row" style = "padding: 25px 0px 0px 0px;"><h3 style = "margin: 0;">Sensor Settings</h3></th>
						</tr>

	      				<tr>
							<th scope="row"><label for="wr_sensor_name">WebRanger Sensor</label></th>
							<td> 
								<div data-tip="Name of your WebRanger Sensor (give it something unique that reminds you of your website)">
								<i style="color:red;">*</i><input id = "wr_sensor_name" class="regular-text" type="text" name="wr_sensor_name" required/> 
								<b><i style="color:grey;">Must only contain alphanumeric and the underscore('_') characters</i> </b>
								</div>
							</td>
						</tr>

						<tr>
							<th scope="row"><label for="wr_sensor_key">WebRanger Key</label></th>
							<td>
								<div data-tip="This key is used for security purposes (enter your own personal key or generate one)">
									<i style="color:red;">*</i>
									<input id = "wr_sensor_key" class="regular-text" type="text" name="wr_sensor_key" required/>
									<input type="button" id="btn-rng" class="button button-secondary" value="Generate Random Key"/>
								</div>
							</td>
						</tr>						
						
						<?php wp_nonce_field('wr_op_verify'); ?>
					</table>
						<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Activate WebRanger"  />
							<input type="button" id="btn-link" class="button button-secondary" value="Go to WebRanger Console"/>
						</p>
					</form>
      		</div> <!--end class-wrap-->

		<?php
	}

	public function generate_wr_activated_page()
	{
		?>
			<div class="wrap"> <!--begin class-wrap-->
				<h2>WebRanger</h2>
				<?php 
					$sensor_details = $this->get_sensor_keys();
				 ?>
			     <?php if(isset($_GET['error_code']))
  				{?>
				<div id='message' class='error notice'><p><strong>WebRanger Error: Update Failed</strong></p></div>
  				<?php } ?>

				<div id='message' class='updated notice'><p><strong>WebRanger is currently securing your site.</strong></p></div>
				<input type="button" id="btn-wr-link" class="button button-primary" value="Go to WebRanger Console"/>
				<input type="button" id="btn-wr-link" class="button button-secondary" value="Test WebRanger" onclick="httpGetAsync()" />

				<br><br><br><br>
				<form method="post" action="admin-post.php" id ="wr-mod-form">
	      			<table class="form-table">
	      				<input type="hidden" name="action" value="wr_mod" />
						<tr>
							<th scope="row" style = "padding: 0;"><h3 style = "margin: 0;">Key Management</h3></th>
						</tr>

	      				<tr>
							<th scope="row"><label for="wr_sensor_name">WebRanger Sensor</label></th>
							<td> 
								<div data-tip="Name of your WebRanger Sensor (give it something unique that reminds you of your website)">
								<i style="color:red;">*</i><input id = "wr_sensor_name" class="regular-text" type="text" name="wr_sensor_name" required value="<?php echo($sensor_details['identifier']); ?>"/> 
								<b><i style="color:grey;">Must only contain alphanumeric and the underscore('_') characters</i> </b>
								</div>
							</td>
						</tr>

						<tr>
							<th scope="row"><label for="wr_sensor_key">WebRanger Key</label></th>
							<td>
								<div data-tip="This key is used for security purposes (enter your own personal key or generate one)">
									<i style="color:red;">*</i>
									<input id = "wr_sensor_key" class="regular-text" type="text" name="wr_sensor_key" required value="<?php echo($sensor_details['key']); ?>"/>
								</div>
							</td>
						</tr>							      				
	      			</table>
	      			<?php wp_nonce_field('wr_op_verify_mod'); ?>
	      			<p class="submit">
	      				<input type="submit" name="submit" class="button button-primary" value="Update Keys"/>
	      				<input type="submit"  style = "text-shadow: none; background:#CC2E2E; border-color:red;" name="submit" class="button button-primary" value="Reset WebRanger"  onclick="return confirmReset()"/>
	      			</p>

	      		</form>
			</div>
		<?php
	}

	private function get_sensor_keys()
	{
		$sensor_details = array();
		if(file_exists(dirname(__FILE__)."/WebRanger/lib/IDS/Config/Sensor.ini.php"))
		{
			$tmp = parse_ini_file(dirname(__FILE__)."/WebRanger/lib/IDS/Config/Sensor.ini.php");

			$sensor_details['identifier'] = $tmp['sensor_username'];
			$sensor_details['key'] = $tmp['sensor_password'];	


			return $sensor_details;
		}
		return false;
	}	
}

?>