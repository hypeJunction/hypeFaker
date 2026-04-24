<?php

use Faker\Factory;
set_time_limit(0);
$success = $error = 0;
$count = (int) get_input('count');
$locale = elgg_get_plugin_setting('locale', 'hypefaker', 'en_US');
$faker = Factory::create($locale);
for ($i = 0; $i < $count; $i++) {
    $users = elgg_get_entities(array('types' => 'user', 'limit' => 1, 'order_by' => 'RAND()', 'metadata_names' => '__faker'));
    $owner = $users[0];
    $containers = array($owner);
    $groups = $owner->getGroups(array(), 100);
    if ($groups) {
        $containers = array_merge($containers, $groups);
    }
    foreach ($containers as $container) {
        elgg_set_page_owner_guid($container->guid);
        $access_array = get_write_access_array($owner->guid);
        $access_id = array_rand($access_array, 1);
        $file = new ElggFile();
        $file->originalfilename = implode('_', $faker->words(3)) . '.jpg';
        $file->setFilename("files/{$file->originalfilename}");
        $file->owner_guid = $owner->guid;
        $file->container_guid = $container->guid;
        $file->title = $faker->sentence(6);
        $file->description = $faker->text(500);
        $file->tags = $faker->words(5);
        $file->access_id = $access_id;
        $file->__faker = true;
        $file_url = $faker->imageURL();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $file_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $file_contents = curl_exec($ch);
        $curl_info = curl_getinfo($ch);
        curl_close($ch);
        $mime_type = $curl_info['content_type'];
        $file->setMimeType($mime_type);
        $file->simpletype = elgg_get_file_simple_type($mime_type);
        $file->open('write');
        $file->write($file_contents);
        $file->close();
        if ($file->save()) {
            $success++;
            if (substr_count($mime_type, 'image/')) {
                // In Elgg 3.x, use saveIconFromElggFile for on-demand icon generation
                $file->saveIconFromElggFile($file, 'icon');
            }
            elgg_create_river_item(array('view' => 'river/object/file/create', 'action_type' => 'create', 'subject_guid' => $file->owner_guid, 'object_guid' => $file->guid));
        } else {
            $error++;
        }
    }
}
if ($error) {
    elgg_register_success_message(elgg_echo('faker:gen_files:error', array($success, $error)));
} else {
    elgg_register_success_message(elgg_echo('faker:gen_files:success', array($success)));
}
return elgg_redirect_response(REFERRER);
