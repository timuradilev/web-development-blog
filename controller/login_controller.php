<?php
	//first, check whether user is authorized
	require_once "authentication.php";
	require_once "../model/user_model.php";
	require_once "../classes/user.php";

	class LoginController
	{
		public function __construct()
		{
			global $auth;
			if($auth->isAuthorized()) { // if user or admin
				header("Location: /"); // redirect to the main page
				exit();
			// if there are email and password, check them
			} elseif(!empty($_REQUEST['email']) && !empty($_REQUEST['password'])) {
				$userModel = new UserModel();
				$users = $userModel->getUsers();

				foreach($users as &$user) {
					if($_REQUEST['email'] === $user->getEmail())
						if(password_verify($_REQUEST['password'], $user->getHashedPassword())) {
							//new sid
							session_start([
								"name"            => "sid",
								"cookie_lifetime" => 2678400,
								"read_and_close"  => true
							]);
							$user->setSID(session_id());
							$user->setNewAuthDate();
							//uid
							setcookie("uid", $user->getUID(), time() + 2678400);

							//save user's data
							$userModel->updateUser($user);

							//redirect to the main page
							header("Location: /");
							exit();
						}
				}
				//if no such user or in case of incorrect password
				//redirect to this page to switch from POST to GET
				header("Location: ".$_SERVER['REQUESTED_URI']);
				exit();
			} // if no email or no password
			elseif (!empty($_REQUEST['email']) xor !empty($_REQUEST['password'])) {
				header("Location: ".$_SERVER['REQUEST_URI']);
				exit();
			} else {
				//nothing to do here. Show the login form further
			}
		}
	}

	$controller = new LoginController();