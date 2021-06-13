<?php

class KetMetadataSetEvent extends Event
{
    public $image;
    public $filename;

	public function __construct(Image $image, String $filename)
	{
		$this->image = $image;
		$this->filename = trim($filename);
	}
}
