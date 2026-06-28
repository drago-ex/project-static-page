<?php

declare(strict_types=1);

namespace App\Presentation\Backend\Page;

use Drago\Permission\Provider;
use Drago\Permission\Role;
use Nette\Security\Permission;


final class PermissionProvider implements Provider
{
	private const string Resource = 'Backend:Page';


	public function register(Permission $acl): void
	{
		$acl->addResource(self::Resource);
		$acl->allow(Role::RoleAdmin);
	}
}
