<?php 
heading(); 
session_start();
form_processor();
if (!isset($_SESSION['username'])) {
    header("Location:".BASE. '/login');
}
?>

<section>
	<div class="shop_user_wrapper" id="shop_user_wrapper">
		<div class="container">
			<div class="row">
		  		<div class="col-sm-6 col-md-6">
		  			<div class="shop_list_logo_area">
		  				<h4>Funnel Buildr Admin Panel</h4>
		  			</div>
		  		</div>
		  		<div class="col-sm-6 col-md-6">
		  			<div class="shopper_login">
		  				<ul>
		  					<li>
		  						<p><?php echo $_SESSION['username']; ?></p>
		  					</li>
		  					<li>
		  						<a href="<?php echo BASE; ?>/logout">Logout</a>
		  					</li>
		  				</ul>
		  			</div>	
		  		</div>
		  	</div>
		</div>
	</div>
</section>
<section>
	<div class="container">
		<div class="row">
		  	<div class="col-md-12">
			  	<div class="sshower_wrapper">
			  		<div class="shop_header_list">
			  			<h3>User Panel</h3>
			  		</div>
			  		<style type="text/css">
			  			
			  		</style>
			  		<div class="sitebar_wrapper">
				  		<div class="col-sm-3 col-md-4">
				  			<div class="left_sitebar">
				  			
				  				<ul>
					  				<li><a href="<?php echo BASE; ?>/shopview"><i class="fa fa-angle-double-right" aria-hidden="true"></i> Shops</a></li>
					  				<li><a href="<?php echo BASE; ?>/user"><i class="fa fa-angle-double-right" aria-hidden="true"></i> User Panel</a></li>
					  			</ul>
				  			</div>
				  		</div>

				  		<div class="col-sm-9 col-md-8">
					  		<div class="right_sitebar">
					  			<?php 
					  					echo "<h3 style='text-align:center;'>". get_flash_message()['message'] ."</h3>";
					  				
					  				  
					  				 //else{
					  					//echo "<h3 style='color: red;'>". get_flash_message()['message'] ."</h3>";
					  				// } 
					  				?>
					  			<div class="user_form">
					  				<form class="form-inline" action="<?php echo BASE ?>/user/?process=users" method="post">
										<div class="form-group">
											<label>Username: </label>
											<input name="username" type="text" class="form-control" placeholder="Username">
										</div>
										<div class="form-group password">
											<label>Password: </label>
											<input name="password" type="password" class="form-control" placeholder="Password">
										</div>
										<button type="submit" class="btn btn-default">Submit</button>
									</form>
					  			</div>
					  			<table class="table">
					  				<thead>
					  					<tr>
					  						<th>Username</th>
					  						<th>Password</th>
					  						<th></th>
					  					</tr>
					  				</thead>
				  				  	<tbody>
				  				  		<?php 
				  				  			$string = file_get_contents("files/json/users.json");
	    									$json_array = json_decode($string, true);

				  				  			$i=1;
				  				  			foreach($json_array as $k=>$v){
				  				  		 ?>
				  				  		<tr>
				  				  			<td><?php echo $k; ?> </td>
				  				  			<td><?php echo $v;  ?></td>
				  				  			<td><a title="Delete" href="<?php echo BASE; ?>/user/?udelete=<?php echo $i; ?>"><i class="fa fa-times" aria-hidden="true"></i></a></td>
				  				  		</tr>
				  				  		<?php $i++; } ?>
				  				  	</tbody>
					  			</table>
					  		</div>
				  		</div>
				  	</div>
			  	</div>
			</div>
  		</div>
	</div>
</section>
<?php 

	 if ( isset($_REQUEST['udelete']) ) {
	        udelete( $_REQUEST['udelete'] );
	    }
	 
	 function udelete($id){

	    $file = file_get_contents("files/json/users.json");
	    $json = json_decode($file, true);
	    $new_array;
	    $i = 1;
	    foreach ($json as $key => $value) {
            if($i != $id){
               $new_array[$key] = $json[$key];
            }
            $i++;
        }
        file_put_contents($file, json_encode($new_array));
	 }
	
 ?>
<section>
	<div class="shop_footer_area">
		<div class="container">
			<div class="row">
			  <div class="col-md-12">
			  	<div class="shop_footer_wrapper">
			  		<p>&copy; All Rights Reserved by Hektor</p>
			  	</div>
			  </div>
			</div>
		</div>
	</div>
</section>
<style type="text/css">

</style>
<?php footing(); ?>

<?php 
	function process_users(){
		$new_user = $_REQUEST['username'];
	    $string = file_get_contents("files/json/users.json");
	    $json_array = json_decode($string, true);
	    
	   // echo empty($json_array[$new_user]);
	    if(empty($json_array[$new_user])){
			// New user added into JSON file
			$json_array[$new_user] = md5($_REQUEST['password']);

			//Save new user
			file_put_contents('files/json/users.json', json_encode($json_array));
			set_flash_message("Created successfully",1);
			header("Location:".BASE. '/user');
	    }else{
			set_flash_message("User already exists",0);
			header("Location:".BASE. '/user');
	    }
	}


?>



