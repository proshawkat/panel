<?php
	// echo "ldjflsj";
  // Inialize session
  session_start();

// Delete certain session
  unset($_SESSION['username']);
  // Delete all session variables
  // session_destroy();
  // echo "ldjflsj";

 // Jump to login page
 header("Location:".BASE. '/login');

  ?>