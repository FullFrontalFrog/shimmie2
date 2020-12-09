<?php

require_once "config.php";
require_once "events/post_title_set_event.php";

class PostTitles extends Extension
{
    public function get_priority(): int
    {
        return 60;
    }

    public function onInitExt(InitExtEvent $event)
    {
        global $config;

        $config->set_default_bool(PostTitlesConfig::DEFAULT_TO_FILENAME, false);
        $config->set_default_bool(PostTitlesConfig::SHOW_IN_WINDOW_TITLE, false);

        global $database;

        if ($this->get_version(PostTitlesConfig::VERSION) < 1) {
            $database->Execute("ALTER TABLE images ADD COLUMN title varchar(255) NULL");
            $this->set_version(PostTitlesConfig::VERSION, 1);
        }
    }

    private function onDatabaseUpgrade(DatabaseUpgradeEvent $event)
    {
        global $database;

        if ($this->get_version(PostTitlesConfig::VERSION) < 1) {
            $database->Execute("ALTER TABLE images ADD COLUMN title varchar(255) NULL");
            $this->set_version(PostTitlesConfig::VERSION, 1);
        }
    }

    public function onDisplayingImage(DisplayingImageEvent $event)
    {
        global $config;

        if ($config->get_bool(PostTitlesConfig::SHOW_IN_WINDOW_TITLE)) {
            $image = $event->get_image();
            $title = self::get_title($image);

            $tags = $image->get_tag_list();
            $matches = [];
            preg_match_all("/\@\w+/", $tags, $matches);
            $artists = $matches[0];
            $artist_count = count($artists);
            $html = " By ";
            for ($i = 0; $i < $artist_count; $i++)
            {
                $a = $artists[$i];
                if ($i > 0)
                {
                    $html .= ", ";
                }
                if ($a == "@KetRalus")
                {
                    $html .= "Ket✦Ralus";
                }
                else
                {
                    $html .= str_replace("@", "", $a);
                }
            }

            $event->set_title($title.$html);
        }
    }

    public function onImageInfoBoxBuilding(ImageInfoBoxBuildingEvent $event)
    {
        global $user;

        $event->add_part($this->theme->get_title_set_html(self::get_title($event->image), $user->can(Permissions::EDIT_IMAGE_TITLE)), 10);
    }

    public function onImageInfoSet(ImageInfoSetEvent $event)
    {
        global $user;

        if ($user->can(Permissions::EDIT_IMAGE_TITLE) && isset($_POST["post_title"])) {
            $title = $_POST["post_title"];
            send_event(new PostTitleSetEvent($event->image, $title));
        }
    }

    public function onPostTitleSet(PostTitleSetEvent $event)
    {
        $this->set_title($event->image->id, $event->title);
    }

    public function onSetupBuilding(SetupBuildingEvent $event)
    {
        $sb = new SetupBlock("Post Titles");
        $sb->start_table();
        $sb->add_bool_option(PostTitlesConfig::DEFAULT_TO_FILENAME, "Default to filename", true);
        $sb->add_bool_option(PostTitlesConfig::SHOW_IN_WINDOW_TITLE, "Show in window title", true);
        $sb->end_table();

        $event->panel->add_block($sb);
    }



    private function set_title(int $image_id, string $title)
    {
        global $database;
        $database->Execute("UPDATE images SET title=:title WHERE id=:id", ['title'=>$title, 'id'=>$image_id]);
        log_info("post_titles", "Title for Image #{$image_id} set to: ".$title);
    }

    public static function get_title(Image $image): string
    {
        global $config;

        $title = $image->title??"";
        if (empty($title) && $config->get_bool(PostTitlesConfig::DEFAULT_TO_FILENAME)) {
            $info = pathinfo($image->filename);
            if (array_key_exists("extension", $info)) {
                $title = basename($image->filename, '.' . $info['extension']);
            } else {
                $title = $image->filename;
            }
        }
        return $title;
    }
}
