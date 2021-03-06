<?php
namespace Icicle\Stream;

interface StreamInterface
{
    /**
     * Determines if the stream is still open.
     *
     * @return bool
     *
     * @api
     */
    public function isOpen();
    
    /**
     * Closes the stream, making it unreadable or unwritable.
     *
     * @api
     */
    public function close();
}
