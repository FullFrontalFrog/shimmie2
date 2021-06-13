<?php

require_once "config.php";
require_once "events/ket_metadata_set_event.php";

class KetMetadata extends Extension
{
    public function get_priority(): int
    {
        return 69;
    }

	public function onInitExt(InitExtEvent $event)
	{
		$this->install();
	}

    private function onDatabaseUpgrade(DatabaseUpgradeEvent $event)
    {
		$this->install();
    }

    public function onImageInfoBoxBuilding(ImageInfoBoxBuildingEvent $event)
    {
        global $user;

		$event->add_part($this->theme->get_metadata_edit_html($event->image->filename, $user->can(Permissions::EDIT_IMAGE_FILENAME)), 11);
    }

	public function onImageInfoSet(ImageInfoSetEvent $event)
	{
        global $user;

        if ($user->can(Permissions::EDIT_IMAGE_FILENAME) && isset($_POST["filename"]))
        {
            send_event(new KetMetadataSetEvent($event->image, $_POST["filename"]));
        }
	}

	public function onKetMetadataSet(KetMetadataSetEvent $event)
	{
		global $database;
		$filename = $event->filename;
		$image_id = $event->image->id;
        $database->Execute("UPDATE images SET filename=:filename WHERE id=:id", ['filename'=>$filename, 'id'=>$image_id]);
        log_info("ket_metadata", "Filename for Image #{$image_id} set to: ".$filename);
	}



	private function install()
	{
		global $database, $config;
		if ($this->get_version(KetMetadataConfig::VERSION) < 1)
		{
			$database->Execute("ALTER TABLE `images` CHANGE `filename` `filename` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;");
			$database->Execute("CREATE INDEX images__filename ON images(filename)");
			$this->set_version(KetMetadataConfig::VERSION, 1);
		}
	}
}
