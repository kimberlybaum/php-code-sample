

<header>

  <h1 id="title">Autoshop</h1>

  <?php
  if(!isset($current_user) || $current_user == NULL) { ?>
    <div id = "login">
      <!-- if not logged in display form -->
      <form id = "login_form" action = "<?php echo htmlspecialchars( $_SERVER['PHP_SELF'] )?>" method="post" novalidate>
        <h2>Login</h2>
        <div>
          <label>Username:</label>
          <input type="text" name="username" required/>
        </div>
        <div>
          <label>Password:</label>
          <input type="password" name="password" required/>
          <button type="submit" name="login_submit"> -> </button>
        </div>
      </form>
    </div>

  <?php } if ( is_user_logged_in()) { ?>

    <div id = "logout">
      <?php
      $logout_url = htmlspecialchars($_SERVER['PHP_SELF']) . '?' . http_build_query(array('logout' => ''));

      echo '<h2 id = "logout-user"> Signed in as <span>' . htmlspecialchars($current_user['username']) . '</span></h2>';
      echo '<h3 id = "logout-link"><a href="' . $logout_url . '"> Log Out ' . '</a></h3>';
      ?>

    </div>
  <?php } ?>

</header>
