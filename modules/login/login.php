  <?php
    session_start();
    form_processor();
  	heading();
  ?>
  <div class="wrapper">
    <form class="form-signin" action="<?php echo BASE ?>/login/?process=login" method="post">       
      <h2 class="form-signin-heading">Please login</h2>
      <input type="text" class="form-control" name="username" placeholder="User Name" required="" autofocus="" />
      <input type="password" class="form-control" name="password" placeholder="Password" required=""/> <br> <?php

      if ($_SESSION['myVar'] == 2) { ?>
        <label style="color: red;">Username or Password don't match.</label>        
      <?php }
        $_SESSION['myVar'] = 0;
      ?>     
      <label class="checkbox">
        <input type="checkbox" value="remember-me" id="rememberMe" name="rememberMe"> Remember me
      </label><br>
      <button class="btn btn-lg btn-primary btn-block" type="submit" name="submit">Login</button>  
    </form>
    <?php
        function process_login() {
          // User Login
          // Jeson read
          $string = file_get_contents("files/json/users.json");
          $json_array = json_decode($string, true);

          // Get Username
          $user = $_REQUEST['username'];

          // Making Auth
          // redirect page based on success
          if ($json_array[$user] == md5($_REQUEST['password'])) {
            $_SESSION['username'] = $user;
            header("Location:".BASE. '/shopview');
          }
          else {
            $_SESSION['myVar'] = 2;
            header("Location:".BASE. '/login');
          }
        }
    ?>

  </div>
  <?php
  	footing();
  ?>