<?php
	define("USERROLE_ADMIN", 0);
	define("USERROLE_GUEST", 1);
	define("USERROLE_USER",  2);

	class User
	{
		protected $name;
		protected $email;
		protected $role;
		protected $hashedPassword;
		protected $uid; //user id is to set to cookie
		protected $sid;
		protected $lastAuthDate;

		public function __construct($name, $email, $role, $hashedPassword, $uid, $sid)
		{
			$this->name = $name;
			$this->email = $email;
			$this->role = $role;
			$this->hashedPassword = $hashedPassword;
			$this->uid = $uid;
			$this->sid = $sid;

			$this->sid = null;
			$this->lastAuthDate = time();
		}
		public function getEmail()
		{
			return $this->email;
		}
		public function getHashedPassword()
		{
			return $this->hashedPassword;
		}
		public function setSID($newSID)
		{
			$this->sid = $newSID;
		}
		public function getSID()
		{
			return $this->sid;
		}
		public function setUID($newUID)
		{
			$this->uid = $newUID;
		}
		public function getUID()
		{
			return $this->uid;
		}
		public function gerRole()
		{
			return $this->role;
		}
		public function setNewAuthDate()
		{
			$this->lastAuthDate = time();
		}
	}