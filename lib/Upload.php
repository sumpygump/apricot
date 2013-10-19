<?php
/**
 * Upload class file 
 *
 * @package Flawless
 * @subpackage Library
 */

/**
 * Upload class
 *
 * Defines error constants for upload errors
 * 
 * @package Flawless
 * @subpackage Library
 * @author Jansen Price <jansen.price@sierra-bravo.com>
 * @version $Id$
 */
class Upload
{
    /**
     * @var array An array of error messages keyed by UPLOAD_ERR constants
     */
    public static $errors = array(
        UPLOAD_ERR_OK         => 'OK',
        UPLOAD_ERR_INI_SIZE   => 'File size too large (ini setting)',
        UPLOAD_ERR_FORM_SIZE  => 'File size too large (form setting)',
        UPLOAD_ERR_PARTIAL    => 'Only partial file uploaded',
        UPLOAD_ERR_NO_FILE    => 'No file received',
        UPLOAD_ERR_NO_TMP_DIR => 'No temporary directory',
        UPLOAD_ERR_CANT_WRITE => 'Cannot write file to disk',
    );
}
