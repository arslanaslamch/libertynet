<?php
load_functions('cdn::cdn');
include path("plugins/cdn/engine/engine.php");
app()->cdnEngine = array(
	'hosted-cdn' => array(
		'title' => 'Hosted CDN',
		'engine' => 'HostedCDNEngine',
		'settings' => array(
			'key' => array(
				'type' => 'text',
				'title' => 'Hosted CDN Secret Key',
				'description' => 'Set your CDN self hosted secret key Please read the documentation for more details',
				'value' => 'myHostedCDNKey',
			),
			'endpoint' => array(
				'type' => 'text',
				'title' => 'Endpoint URL',
				'description' => 'Set your hosted CDN endpoint URL . Make sure / end it',
				'value' => 'http://www.cdndomain.com/'
			),
			'file' => array(
				'type' => 'text',
				'title' => 'Uploads Processor File Name',
				'description' => 'You can increase security of your hosted CDN by renaming the uploads processor file name , please read documentation for more details',
				'value' => 'processor'
			)
		)
	),
	'amazon-s3' => array(
		'title' => 'Amazon S3',
		'engine' => 'AmazonCDNEngine',
		'settings' => array(
			'bucket' => array(
				'title' => 'Amazon S3 Bucket Name',
				'description' => 'Set your amazon S3 Bucket name, please read the documentation for more details',
				'value' => '',
				'type' => 'text'
			),
			'id' => array(
				'title' => 'Amazon S3 Key ID',
				'description' => 'Set your amazon S3 Access Key Id, please read the documentation for more details',
				'value' => '',
				'type' => 'text'
			),
			'key' => array(
				'title' => 'Amazon S3 Secret Access Key',
				'description' => 'Set your amazon S3 Secret Access Key, please read the documentation for more details',
				'value' => '',
				'type' => 'text'
			),
			'endpoint' => array(
				'title' => 'Amazon S3 Endpoint URL',
				'description' => 'Set your amazon S3 endpoint url without http or https e.g <b>s3.amazonaws.com</b>',
				'value' => 's3.amazonaws.com',
				'type' => 'text'
			),
			'region' => array(
				'title' => 'Amazon S3 Region',
				'description' => 'Choose your amazon S3 region',
				'value' => 'us-east-1',
				'type' => 'selection',
				'data' => [
					'us-east-1' => 'US East (N. Virginia)',
					'us-east-2' => 'US East (Ohio)',
					'us-west-1' => 'US West (N. California)',
					'us-west-2' => 'US West (Oregon)',
					'af-south-1' => 'Africa (Cape Town)',
					'ap-east-1' => 'Asia Pacific (Hong Kong)',
					'ap-south-1' => 'Asia Pacific (Mumbai)',
					'ap-northeast-3' => 'Asia Pacific (Osaka-Local)',
					'ap-northeast-2' => 'Asia Pacific (Seoul)',
					'ap-southeast-1' => 'Asia Pacific (Singapore)',
					'ap-southeast-2' => 'Asia Pacific (Sydney)',
					'ap-northeast-1' => 'Asia Pacific (Tokyo)',
					'ca-central-1' => 'Canada (Central)',
					'cn-north-1' => 'China (Beijing)',
					'cn-northwest-1' => 'China (Ningxia)',
					'eu-central-1' => 'Europe (Frankfurt)',
					'eu-west-1' => 'Europe (Ireland)',
					'eu-west-2' => 'Europe (London)',
					'eu-south-1' => 'Europe (Milan)',
					'eu-west-3' => 'Europe (Paris)',
					'eu-north-1' => 'Europe (Stockholm)',
					'me-south-1' => 'Middle East (Bahrain)',
					'sa-east-1' => 'South America (São Paulo)',
				],
			)
		)
	)
);

//register admin menu
register_hook("admin-started", function () {
	register_asset("cdn::js/cdn.js");
	get_menu("admin-menu", "tools")->addMenu(lang("cdn::cdn-servers"), url("admincp/cdn/servers"), "admin-cdn-servers");
});

register_hook("upload", function ($uploader, $fileName) {

	if (config("cdn-process-uploads", true) and $uploader->allowCDN) {

		$server = get_usable_cdn();

		if ($server) {
			$obj = app()->cdnEngine[$server['type']]['engine'];

			$serverObj = new $obj();
			$file = $uploader->destinationPath . $fileName;
			$result = $serverObj->upload($server, $file, $uploader->baseDir . $fileName);
			if ($result and !stripos($uploader->result, '[cdn]')) {
				$uploader->result = $server['id'] . '[cdn]' . $uploader->result;
			}

			if ($result and !config('cdn-keep-files', true)) {
				//delete the file
				delete_file($file);
			}
		}
	}
});

register_hook('filter.url', function ($url) {
	if ($url and stripos($url, '[cdn]')) {
		list($serverId, $result) = explode('[cdn]', $url);
		$server = get_cdn_server($serverId);

		if ($server) {
			$obj = app()->cdnEngine[$server['type']]['engine'];

			$serverObj = new $obj();
			$url = $serverObj->output($result, $server);
		} else {
			$url = $result;
		}
	}
	return $url;
});

register_hook("delete.file", function ($path) {

	if ($path and stripos($path, '[cdn]')) {
		//lets remove base path

		$basePath = path();
		$newPath = str_replace($basePath, '', $path);
		list($serverId, $result) = explode('[cdn]', $newPath);
		$server = get_cdn_server($serverId);

		if ($server) {

			$obj = app()->cdnEngine[$server['type']]['engine'];

			$serverObj = new $obj();
			$serverObj->delete($result, $server);
		}
		$path = $basePath . $result;
	}
	return $path;
});

register_hook('filter.path', function ($path) {
	try {
		if ($path and stripos($path, '[cdn]')) {
			list($serverId, $path) = explode('[cdn]', $path);
		}
	} catch (Exception $e) {
	}
	return $path;
});

register_pager("admincp/cdn/servers", array('use' => "cdn::admincp@lists_pager", 'filter' => 'admin-auth', 'as' => 'admincp-cdn-servers'));
register_pager("admincp/cdn/add/servers", array('use' => "cdn::admincp@add_pager", 'filter' => 'admin-auth', 'as' => 'admincp-cdn-add-server'));
register_pager("admincp/cdn/server/manage", array('use' => "cdn::admincp@manage_pager", 'filter' => 'admin-auth', 'as' => 'admincp-cdn-manage'));
