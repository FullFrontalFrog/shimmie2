<?php

class KetMetadataSetDatePostedEvent extends Event
{
    public $image;
    public $date_posted;

	public function __construct(Image $image, String $date_posted)
	{
		$this->image = $image;
		$this->date_posted = trim($date_posted);
	}
}
