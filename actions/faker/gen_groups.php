<?php

use Faker\Factory;
function hypefaker_get_group_content_access_mode($mode)
{
    switch ($mode) {
        case 'members_only':
            return ElggGroup::CONTENT_ACCESS_MODE_MEMBERS_ONLY;
        case 'unrestricted':
            return ElggGroup::CONTENT_ACCESS_MODE_UNRESTRICTED;
    }
}
set_time_limit(0);
$success = $error = 0;
$count = (int) get_input('count');
$featured_count = (int) get_input('featured_count');
$locale = elgg_get_plugin_setting('locale', 'hypefaker', 'en_US');
$faker = Factory::create($locale);
foreach (array(ACCESS_PRIVATE, ACCESS_LOGGED_IN, ACCESS_PUBLIC) as $visibility) {
    foreach (array(hypefaker_get_group_content_access_mode('members_only'), hypefaker_get_group_content_access_mode('unrestricted')) as $content_access_mode) {
        foreach (array(ACCESS_PRIVATE, ACCESS_PUBLIC) as $membership) {
            for ($i = 0; $i < $count; $i++) {
                $users = elgg_get_entities(array('types' => 'user', 'limit' => 1, 'order_by' => 'RAND()', 'metadata_names' => '__faker'));
                $owner = $users[0];
                $group = new ElggGroup();
                $group->name = $faker->sentence(5);
                $group->owner_guid = $owner->guid;
                $group->container_guid = $owner->guid;
                $group->description = $faker->text(500);
                $group->briefdescription = $faker->bs;
                $group->interests = $faker->words(10);
                $group->access_id = ACCESS_PUBLIC;
                $group->membership = $membership;
                $group->content_access_mode = $content_access_mode;
                $guid = $group->save();
                if (!$guid) {
                    $error++;
                    continue;
                }
                if ($visibility != ACCESS_PUBLIC && $visibility != ACCESS_LOGGED_IN) {
                    $visibility = $group->getOwnedAccessCollection('group_acl')->id;
                }
                if ($group->access_id != $visibility) {
                    $group->access_id = $visibility;
                }
                $group->__faker = true;
                // store this flag so we can easily find fake entities
                $group->join($owner);

                // In Elgg 3.x, use saveIconFromElggFile for on-demand icon generation
                $profile_icon_url = $faker->imageURL();
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $profile_icon_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                $file_contents = curl_exec($ch);
                $curl_info = curl_getinfo($ch);
                curl_close($ch);
                $mime_type = $curl_info['content_type'];
                if (substr_count($mime_type, 'image/')) {
                    $tmp = new ElggFile();
                    $tmp->owner_guid = $group->owner_guid;
                    $tmp->setFilename("groups/{$group->guid}_src.jpg");
                    $tmp->open('write');
                    $tmp->write($file_contents);
                    $tmp->close();

                    $group->saveIconFromElggFile($tmp, 'icon');
                    $tmp->delete();
                }

                elgg_create_river_item(array('view' => 'river/group/create', 'action_type' => 'create', 'subject_guid' => $owner->guid, 'object_guid' => $group->guid));
                $tool_options = elgg_get_config('group_tool_options');
                if ($tool_options) {
                    foreach ($tool_options as $group_option) {
                        $option_toggle_name = $group_option->name . "_enable";
                        $option_default = $group_option->default_on ? 'yes' : 'no';
                        $group->{$option_toggle_name} = $option_default;
                    }
                }
                if ($group->save()) {
                    $success++;
                    $groups[$group->guid] = $group;
                } else {
                    $error++;
                }
            }
        }
    }
}
if (!empty($groups) && $featured_count > 0) {
    $featured_group_keys = array_rand($groups, min($featured_count, count($groups)));
    if (!is_array($featured_group_keys)) {
        $featured_group_keys = [$featured_group_keys];
    }
    foreach ($groups as $key => $group) {
        if (in_array($key, $featured_group_keys)) {
            $group->featured_group = "yes";
        }
    }
}
if ($error) {
    elgg_register_success_message(elgg_echo('faker:gen_groups:error', array($success, $error)));
} else {
    elgg_register_success_message(elgg_echo('faker:gen_groups:success', array($success)));
}
return elgg_redirect_response(REFERRER);
