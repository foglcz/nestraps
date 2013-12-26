<?php

use Nette\Security,
	Nette\Utils\Strings;


/**
 * Users authenticator.
 */
class Authenticator extends Nette\Object implements Security\IAuthenticator
{
	const
		TABLE_NAME = 'users',
		COLUMN_ID = 'id',
		COLUMN_NAME = 'username',
		COLUMN_PASSWORD = 'password',
		COLUMN_ROLE = 'role',
		PASSWORD_MAX_LENGTH = 4096;

	/** @var Nette\Database\Connection */
	private $database;


	public function __construct(Nette\Database\Connection $database)
	{
		$this->database = $database;
	}


	/**
	 * Performs an authentication.
	 * @return Nette\Security\Identity
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($username, $password) = $credentials;
		$row = $this->database->table(self::TABLE_NAME)->where(self::COLUMN_NAME, $username)->fetch();

		if (!$row) {
			throw new Security\AuthenticationException('The username is incorrect.', self::IDENTITY_NOT_FOUND);
		}

		if ($row[self::COLUMN_PASSWORD] !== $this->calculateHash($password, $row[self::COLUMN_PASSWORD])) {
			throw new Security\AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);
		}

		$arr = $row->toArray();
		unset($arr[self::COLUMN_PASSWORD]);
		return new Nette\Security\Identity($row[self::COLUMN_ID], $row[self::COLUMN_ROLE], $arr);
	}


	/**
	 * Computes salted password hash.
	 * @param  string
	 * @return string
	 */
	public static function calculateHash($password, $salt = NULL)
	{
		if ($password === Strings::upper($password)) { // perhaps caps lock is on
			$password = Strings::lower($password);
		}
		$password = substr($password, 0, self::PASSWORD_MAX_LENGTH);
		return crypt($password, $salt ?: '$2a$07$' . Strings::random(22));
	}

}
