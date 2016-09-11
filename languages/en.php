<?php

$english = array(

	'admin:developers:faker' => 'Generate Demo Data',

	'faker:data' => 'Generated demo items',
	'faker:delete' => 'Delete demo items',
	'faker:delete:success' => 'Demo data has been deleted successfully',
	'faker:delete:error' => '%s items were left behind...',

	'faker:gen_users' => 'Generate users',
	'faker:gen_users:count' => 'Number of users to generate',
	'faker:gen_users:password' => 'Default password to set',
	'faker:gen_users:email_domain' => 'Email Domain (do not accidentally spam people)',
	
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

	'faker:gen_bookmarks' => 'Generate bookmarks',
	'faker:gen_bookmarks:count' => 'Number of users to use as basis for generating bookmarks (bookmarks will be generated for groups the member belongs to)',
	'faker:gen_bookmarks:success' => '%s bookmarks were successfully created',
	'faker:gen_bookmarks:error' => '%s bookmarks were successfully created; %s bookmarks could not be created',

	'faker:gen_files' => 'Generate files',
	'faker:gen_files:count' => 'Number of users to use as basis for generating files (files will be generated for groups the member belongs to)',
	'faker:gen_files:success' => '%s files were successfully created',
	'faker:gen_files:error' => '%s files were successfully created; %s files could not be created',

	'faker:gen_pages' => 'Generate pages',
	'faker:gen_pages:count' => 'Number of users to use as basis for generating pages (pages will be generated for groups the member belongs to)',
	'faker:gen_pages:max_children' => 'Max number of subpages to create',
	//'faker:gen_pages:depth' => 'Depth of the tree',
	'faker:gen_pages:success' => '%s pages were successfully created',
	'faker:gen_pages:error' => '%s pages were successfully created; %s pages could not be created',

	'faker:gen_wire' => 'Generate wire posts',
	'faker:gen_wire:count' => 'Number of top level wire posts to generate',
	'faker:gen_wire:max_replies' => 'Max number of replies per wire post',
	'faker:gen_wire:success' => '%s wire posts were successfully created',
	'faker:gen_wire:error' => '%s wire posts were successfully created; %s wire posts could not be created',

	'faker:gen_messages' => 'Generate messages',
	'faker:gen_messages:count' => 'Number of messages per user to generate',
	'faker:gen_messages:success' => '%s messages ere successfully created',
	'faker:gen_messages:error' => '%s messages were successfully created; %s messages could not be created',

	'faker:gen_discussions' => 'Generate discussions',
	'faker:gen_discussions:count' => 'Number of users to use as basis for generating discussions (discussions will be generated for groups the member belongs to)',
	'faker:gen_discussions:reply_count' => 'Number of replies per discussion',
	'faker:gen_discussions:success' => '%s discussions were successfully created',
	'faker:gen_discussions:error' => '%s discussions were successfully created; %s discussions could not be created',

	'faker:gen_comments' => 'Generate comments',
	'faker:gen_comments:count' => 'Number of comments per entity',
	'faker:gen_comments:reply_count' => 'Number of replies per comment (if nested comments are supported)',
	'faker:gen_comments:success' => '%s comments were successfully created',
	'faker:gen_comments:error' => '%s comments were successfully created; %s comments could not be created',

	'faker:gen_likes' => 'Generate likes',
	'faker:gen_likes:count' => 'Max number of likes per entity',
	'faker:gen_likes:success' => '%s likes were successfully created',
	'faker:gen_likes:error' => '%s likes were successfully created; %s likes could not be created',

	'faker:gen_location' => 'Generate location',
	'faker:gen_location:real' => 'Generate entity location from real-world country capitals',
	'faker:gen_location:success' => 'Location has been generated for %s entities',
	'faker:gen_location:error' => 'Location has been generated for %s of %s entities',
);

add_translation('en', $english);
