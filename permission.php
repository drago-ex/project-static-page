<?php

declare(strict_types=1);

/**
 * Basic access rules of this package for drago-ex/permission.
 * Generated into the project by: php vendor/bin/create-permission --packages
 */
return [
	// Registers the page administration; administrators are allowed everything.
	[
		'className' => 'PermissionProvider',
		'namespace' => 'App\Presentation\Backend\Page',
		'resource' => 'Backend:Page',
		'outputDir' => 'app/Presentation/Backend/Page',
		'allow' => ['RoleAdmin'],
	],
	// Everyone may read the published pages.
	[
		'className' => 'PermissionProvider',
		'namespace' => 'App\Presentation\Front\Page',
		'resource' => 'Front:Page',
		'outputDir' => 'app/Presentation/Front/Page',
		'allow' => ['RoleGuest,self::Resource'],
	],
];
