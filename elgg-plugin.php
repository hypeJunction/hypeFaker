<?php

return [
	'plugin' => [
		'version' => '4.0.0',
	],

	'bootstrap' => \hypeJunction\Faker\Bootstrap::class,

	'actions' => [
		'faker/delete'            => ['access' => 'admin'],
		'faker/gen_users'         => ['access' => 'admin'],
		'faker/gen_friends'       => ['access' => 'admin'],
		'faker/gen_groups'        => ['access' => 'admin'],
		'faker/gen_group_members' => ['access' => 'admin'],
		'faker/gen_blogs'         => ['access' => 'admin'],
		'faker/gen_bookmarks'     => ['access' => 'admin'],
		'faker/gen_files'         => ['access' => 'admin'],
		'faker/gen_pages'         => ['access' => 'admin'],
		'faker/gen_wire'          => ['access' => 'admin'],
		'faker/gen_messages'      => ['access' => 'admin'],
		'faker/gen_discussions'   => ['access' => 'admin'],
		'faker/gen_comments'      => ['access' => 'admin'],
		'faker/gen_likes'         => ['access' => 'admin'],
	],
];
