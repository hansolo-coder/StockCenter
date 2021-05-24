<?php
	/**
	 * login manager
	 */
	class login
	{
		/**
		 * displays the login form
		 */
		function displayLogin($username)
		{
			include_once './classes/widgets/formLogin.class.php';

			$form = new formLogin();
			$form->action = "login";
			if (isset($username))
			  $form->username = $username;
			$form->display();
		}

		
		/**
		 * processes the login
		 */
		function processLogin()
		{
			include_once './classes/widgets/formLogin.class.php';

			$form = new formLogin();
			$form->process();
		}
	}
?>
