<?php

namespace MythicalDash\Addons\imagehostbridge;

use MythicalDash\App;

class ShareXApi
{
	/**
	 * Show an error response
	 * 
	 * @param \MythicalDash\App $app The application instance
	 * @param string $error The error message
	 * 
	 * @return void The response will be sent to the client
	 */
	public static function showError(App $app, string $error): void
	{
		$app->BadRequest($error, [
			"status" => 400,
			"data" => [
				"error" => $error,
			]
		]);
	}

	/**
	 * Show a success response
	 * 
	 * @param \MythicalDash\App $app The application instance
	 * @param string $url The URL of the image
	 * @param string $thumbnail The thumbnail of the image
	 * @param string $delete The delete URL of the image
	 * 
	 * @return void The response will be sent to the client
	 */
	public static function showSuccess(App $app, string $url, string $thumbnail, string $delete): void
	{
		$app->OK("Success", [
			"status" => 200,
			"data" => [
				"link" => $url,
				"thumbnail" => $thumbnail,
				"delete" => $delete,
			]
		]);
	}

	/**
	 * Create a ShareX config
	 * 
	 * @return array The ShareX config
	 */
	public static function createConfig(string $app_name, string $app_url, string $upload_key, array $headers = ["x-From-MythicalDash" => "ShareX"]): array
	{
		return [
			"Version" => "17.0.0",
			"Name" => $app_name,
			"DestinationType" => "ImageUploader",
			"RequestMethod" => "POST",
			"RequestURL" => $app_url . "/api/user/images/upload",
			"Headers" => $headers,
			"Body" => "MultipartFormData",
			"Arguments" => [
				"upload_api" => $upload_key
			],
			"FileFormName" => "file",
			"URL" => "{json:data.link}",
			"ThumbnailURL" => "{json:data.thumbnail}",
			"DeletionURL" => "{json:data.delete}",
			"ErrorMessage" => "{json:data.error}"
		];
	}
}