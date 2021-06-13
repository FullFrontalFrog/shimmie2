<?php

class KetMetadataInfo extends ExtensionInfo
{
    public const KEY = "ket_metadata";

    public $key = self::KEY;
    public $name = "Ket Metadata";
    public $authors = ["Ket Ralus"=>"ket.ralus@gmail.com"];
    public $license = self::LICENSE_WTFPL;
    public $description = "Allow admin to store and display fields of metadata for an image. Unlike the metadata extension from Gallery 1, this currently only handles the filename field. The title field is handled by the post_titles extension, and setting the artist(s) is done via tags only.";
}
