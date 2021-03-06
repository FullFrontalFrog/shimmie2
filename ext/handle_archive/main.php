<?php

class ArchiveFileHandler extends Extension
{
    public function onInitExt(InitExtEvent $event)
    {
        global $config;
        $config->set_default_string('archive_extract_command', 'unzip -d "%d" "%f"');
    }

    public function onSetupBuilding(SetupBuildingEvent $event)
    {
        $sb = new SetupBlock("Archive Handler Options");
        $sb->add_text_option("archive_tmp_dir", "Temporary folder: ");
        $sb->add_text_option("archive_extract_command", "<br>Extraction command: ");
        $sb->add_label("<br>%f for archive, %d for temporary directory");
        $event->panel->add_block($sb);
    }

    public function onDataUpload(DataUploadEvent $event)
    {
        if ($this->supported_ext($event->type)) {
            global $config;
            $tmp = sys_get_temp_dir();
            $tmpdir = "$tmp/shimmie-archive-{$event->hash}";
            $cmd = $config->get_string('archive_extract_command');
            $cmd = str_replace('%f', $event->tmpname, $cmd);
            $cmd = str_replace('%d', $tmpdir, $cmd);
            exec($cmd);
            $results = add_dir($tmpdir);
            if (count($results) > 0) {
                // Not all themes have the add_status() method, so need to check before calling.
                if (method_exists($this->theme, "add_status")) {
                    $this->theme->add_status("Adding files", $results);
                }
            }
            deltree($tmpdir);
            $event->image_id = -2; // default -1 = upload wasn't handled
        }
    }

    private function supported_ext($ext)
    {
        $exts = ["zip"];
        return in_array(strtolower($ext), $exts);
    }
}
