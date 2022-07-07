<?php

namespace app\core\exceptions;


class NotFoundException extends \Exception
{
	protected $message = 'Page Not Found';
	protected $code    = 404;

}