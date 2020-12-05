<?php
new UserClass("user", "user", [
	Permissions::CREATE_IMAGE => False,

	Permissions::EDIT_IMAGE_RATING => False,
	Permissions::BULK_EDIT_IMAGE_RATING => False,

	Permissions::EDIT_IMAGE_TAG => False,
	Permissions::BULK_EDIT_IMAGE_TAG => False,

	Permissions::MASS_TAG_EDIT => False,
	Permissions::EDIT_TAG_CATEGORIES => False,
]);
new UserClass("trusted", "user", [
	Permissions::VIEW_UNLISTED_IMAGES => True,
]);
?>
