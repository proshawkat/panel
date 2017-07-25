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
          $user = $_REQUEST['username'];
          $pass = $_REQUEST['password'];

          // echo $user."----------".$pass;

          // $servername="localhost";
          // $username="root";
          // $password="12345";
          // $database = "new_db";

          // $conn = mysqli_connect($servername, $username, $password, $database);
          $flag = 0;
          $conn = db_connect();

          $sql = "SELECT username,password FROM login_tb";
          $result = mysqli_query($conn,$sql);

           if (isset($_POST["submit"])) {
              if(mysqli_num_rows($result) > 0) {
                while($row=mysqli_fetch_assoc($result) ) {
                  $un = $row["username"];
                  $pa = $row["password"];

                  if(($user == $un) && ($pass == $pa)) {
                    $flag = 1;
                    break;
                  }
                  else {
                    $flag = 0;
                  }
                }
              }
           }

           if ($flag == 1) {
             header("Location:".BASE. '/shopview');
           }
           else {
            $flag = 2;
            $_SESSION['myVar'] = $flag;
            header("Location:".BASE. '/login');
           }
        }
    ?>

  </div>
  <?php
  	footing();
  ?>