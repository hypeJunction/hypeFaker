<?php

$english = array(

	'admin:developers:faker' => 'Generate Demo Data',

	'faker:data' => 'Generated demo items',
	'faker:delete' => 'Delete demo items',

	'faker:gen_users' => 'Generate users',
	'faker:gen_users:count' => 'Number of users to generate',
	'faker:gen_users:password' => 'Default password to set',
	'faker:gen_users:success' => '%s user accounts were created',
	'faker:gen_users:error' => '%s user accounts were created<br />%s accounts failed to create with following messages (%s)',

	'faker:gen_friends' => '(re)Generate friendships and access collections',
	'faker:gen_friends:max' => 'Max number of friendships per user',
	'faker:gen_friends:success' => '%s friendship relationships have been generated; %s friend and user collections have been created',

	'faker:gen_groups' => 'Generate groups',
	'faker:gen_groups:count' => 'Number of groups for each Membership-Accessibility-Visibility combination (total of 12 combinations)',
	'faker:gen_groups:featured_count' => 'Number of groups to feature',
	'faker:gen_groups:success' => '%s groups were successfully created',
	'faker:gen_groups:error' => '%s groups were successfully created; %s groups could not be created',

	'faker:gen_group_members' => '(re)Generate group membership',
	'faker:gen_group_members:max' => 'Max number of members, membership requests and invites to add to each group',
	'faker:gen_group_members:success' => '%s members, %s invites, %s membership requests have been added to %s',

	'faker:gen_blogs' => 'Generate blogs',
	'faker:gen_blogs:count' => 'Number of users to use as basis for generating blogs (blogs will be generated for groups the member belongs to)',
	'faker:gen_blogs:success' => '%s blogs were successfully created',
	'faker:gen_blogs:error' => '%s blogs were successfully created; %s blogs could not be created',
	
);

add_translation('en', $english);
