<?php
/**
 * Qi Fileupload
 *
 * @package Qi
 */

/**
 * Fileupload
 *
 * Qi_Fileupload PHP Class to process file uploads easily
 * ======================================================
 *
 * Usage, setup, and license at the bottom of this page (README)
 *
 * Error codes
 * ----
 *  - [0] - "No file was uploaded"
 *  - [1] - "Maximum file size exceeded"
 *  - [2] - "Maximum image size exceeded"
 *  - [3] - "Only specified file type may be uploaded"
 *  - [4] - "File already exists" (save only)
 *  - [5] - "Unable to copy file (it may already exist)"
 *
 * @package Qi
 * @author David Fox <david.fox@foo.com>
 * @author Dave Tufts <dave.tufts@foo.com>
 * @author Jansen Price <jansen.price@gmail.com>
 * @copyright 1999-2003 David Fox, Dave Tufts
 * @version 2.10
 *
 * @changes v2.10 - Modified the convert cmd to not convert to backslashes (now good on linux)
 * @changes v2.9 - Added vars thumbStyle, thumbStyleString, method
 *     _get_rawthumbfilename(); Can specify how you want the thumbnails to be named.
 * @changes v2.8 - Added var createThumb, and noThumb() method, to tell whether
 *     you want a thumbnail created as well, defaults to true.  Also added
 *     functionality to use ImageMagick's convert.exe to resize for thumbs.
 * @changes v2.7 - Added new error code [5] to _save_file() method, fixed minor
 *     bug if unable to write to upload directory
 * @changes v2.6 - Added $this->acceptable_file_types. Fixed minor bug fix in
 *     upload() - if file 'type' is null
 * @changes v2.5.2 - Added Italian (it) error messgaing
 * @changes v2.5.1 - Added German (de) and Dutch (nl) error messgaing
 * @changes v2.4 - Added error messgae language preferences
 * @changes v2.3.1 - Bugfix for upload $path in $this->_save_file()
 * @changes v2.3 - Initialized all variables (compatibale with PHP error notices)
 * @changes v2.2 - Changed ereg() to stristr() whenever possible
 */
class Qi_Fileupload
{
    /**
     * Data array of file uploaded
     *
     * @var array
     */
    public $file;

    /**
     * List of acceptable file types
     *
     * @var mixed
     */
    public $acceptable_file_types;

    /**
     * Error message
     *
     * @var mixed
     */
    public $error;

    /**
     * Error messages (deprecated)
     *
     * @var mixed
     */
    public $errors; // Deprecated (only for backward compatability)

    /**
     * Accepted
     *
     * @var mixed
     */
    public $accepted;

    /**
     * Max file size (in bytes)
     *
     * @var int
     */
    public $max_filesize;

    /**
     * Max allowed image width (in pixels)
     *
     * @var int
     */
    public $max_image_width;

    /**
     * Max allowed image height (in pixels)
     *
     * @var int
     */
    public $max_image_height;

    /**
     * Path to save file
     *
     * @var mixed
     */
    public $path;

    /**
     * Flag indicating file needs resize
     *
     * @var bool
     */
    public $needsResize;

    /**
     * Flag indicating to create thumbnail image
     *
     * @var bool
     */
    public $createThumb;

    /**
     * Flag whether image needs thumbnail
     *
     * @var int
     */
    public $needsThumb;

    /**
     * How thumbnail files are named
     *
     * Possible values are 'infix' or 'folder'
     *
     * @var string
     */
    public $thumbStyle;

    /**
     * What the name of the thumbnail will be
     *
     * @var string
     */
    public $thumbStyleString;

    /**
     * Full path to Imagemagick convert binary
     *
     * @var string
     */
    public $convert_path;

    /**
     * Class constructor, sets error messaging language preference
     *
     * Example invocation
     * <pre>
     *     $uploader = new Qi_Fileupload();
     * </pre>
     *
     * @return void
     */
    public function __construct()
    {
        $this->error            = '';
        $this->createThumb      = true;
        $this->thumbStyle       = 'infix';
        $this->thumbStyleString = 'th';
    }

    /**
     * Set the convertpath (location of Imagemagick convert)
     *
     * @param string $cpath Path to convert
     * @return void
     */
    public function convertpath($cpath)
    {
        $this->convertpath = $cpath;
    }

    /**
     * thumbStyle
     *
     * @param string $style Style
     * @param string $string String
     * @return void
     */
    public function thumbStyle($style, $string)
    {
        $this->thumbStyle = $style;

        $this->thumbStyleString = $string;
    }

    /**
     * Set the maximum file size in bytes ($size), allowable by the object.
     *
     * PHP's configuration file also can control the maximum upload size, which
     * is set to 2 or 4  megs by default. To upload larger files, you'll have
     * to change the php.ini file first.
     *
     * @param int $size File size in bytes
     * @return void
     */
    public function max_filesize($size)
    {
        $this->max_filesize = (int) $size;
    }

    /**
     * Sets the maximum pixel dimensions. Will only be checked if the
     * uploaded file is an image
     *
     * @param int $width Maximum pixel width of image uploads
     * @param int $height Maximum pixel height of image uploads
     * @return void
     */
    public function max_image_size($width, $height)
    {
        $this->max_image_width  = (int) $width;
        $this->max_image_height = (int) $height;
    }

    /**
     * Set option to not create a thumbnail
     *
     * @return void
     */
    public function no_thumb()
    {
        $this->createThumb = false;
    }

    /**
     * Sets the maximum pixel dimensions for the thumbnail. Will only be checked if the
     * uploaded file is an image
     *
     * @param int $width Maximum pixel width of image thumbnail
     * @param int $height Maximum pixel height of image thumbnail
     * @return void
     */
    public function max_thumb_size($width, $height)
    {
        $this->max_thumb_width  = (int) $width;
        $this->max_thumb_height = (int) $height;
    }

    /**
     * Process the file upload
     *
     * Checks if the file is acceptable and uploads it to PHP's default upload diretory
     *
     * @param string $filename Form field name of uploaded file
     * @param string $accept_type Acceptable mime-types
     * @param string $extension Default filename extenstion
     * @param string $path Path to save file
     * @param int $mode File upload mode
     * @return array (0 => filename, 1 => thumbnail filename)
     */
    public function upload($filename='', $accept_type='', $extension='', $path='', $mode=2)
    {
        $this->acceptable_file_types = trim($accept_type); // used by error messages

        if (!isset($_FILES) || !is_array($_FILES[$filename]) || !$_FILES[$filename]['name']) {
            $this->error    = $this->get_error(0);
            $this->accepted = false;
            return false;
        }

        // Copy PHP's global $_FILES array to a local array
        $this->file = $_FILES[$filename];

        $this->file['file'] = $filename;

        // Initialize empty array elements
        if (!isset($this->file['extension'])) {
            $this->file['extension'] = "";
        }

        if (!isset($this->file['type'])) {
            $this->file['type'] = "";
        }

        if (!isset($this->file['size'])) {
            $this->file['size'] = "";
        }

        if (!isset($this->file['width'])) {
            $this->file['width'] = "";
        }

        if (!isset($this->file['height'])) {
            $this->file['height'] = "";
        }

        if (!isset($this->file['tmp_name'])) {
            $this->file['tmp_name'] = "";
        }

        if (!isset($this->file['raw_name'])) {
            $this->file['raw_name'] = "";
        }

        if (!isset($this->file['raw_th_name'])) {
            $this->file['raw_th_name'] = "";
        }

        if (!isset($this->file['th_name'])) {
            $this->file['th_name'] = "";
        }

        // test max size

        if ($this->max_filesize && ($this->file["size"] > $this->max_filesize)) {
            $this->error    = $this->get_error(1);
            $this->accepted = false;
            return false;
        }

        if (stristr($this->file["type"], "image")) {
            /* IMAGES */
            $image = getimagesize($this->file["tmp_name"]);

            $this->file["width"]  = $image[0];
            $this->file["height"] = $image[1];

            // test max image size
            if (($this->max_image_width || $this->max_image_height)
                && (($this->file["width"] > $this->max_image_width)
                || ($this->file["height"] > $this->max_image_height))
            ) {
                $this->needsResize = true;
            }

            // test max thumb size
            if (($this->max_thumb_width || $this->max_thumb_height)
                && (($this->file["width"] > $this->max_thumb_width)
                || ($this->file["height"] > $this->max_thumb_height))
                && $this->createThumb
            ) {
                $this->needsThumb = true;
            }

            // Image Type is returned from getimagesize() function
            switch($image[2]) {
            case 1:
                $this->file["extension"] = ".gif";
                break;
            case 2:
                $this->file["extension"] = ".jpg";
                break;
            case 3:
                $this->file["extension"] = ".png";
                break;
            case 4:
                $this->file["extension"] = ".swf";
                break;
            case 5:
                $this->file["extension"] = ".psd";
                break;
            case 6:
                $this->file["extension"] = ".bmp";
                break;
            case 7:
                $this->file["extension"] = ".tif";
                break;
            case 8:
                $this->file["extension"] = ".tif";
                break;
            default:
                $this->file["extension"] = $extension;
                break;
            }
        } elseif (!ereg("(\.)([a-z0-9]{3,5})$", $this->file["name"]) && !$extension) {
            // Try and autmatically figure out the file type
            // For more on mime-types: http://httpd.apache.org/docs/mod/mod_mime_magic.html
            switch($this->file["type"]) {
            case "text/plain":
                $this->file["extension"] = ".txt";
                break;
            case "text/richtext":
                $this->file["extension"] = ".txt";
                break;
            default:
                break;
            }
        } else {
            $this->file["extension"] = $extension;
        }

        // check to see if the file is of type specified
        if ($this->acceptable_file_types) {
            if (trim($this->file["type"])
                && stristr($this->acceptable_file_types, $this->file["type"])
            ) {
                $this->accepted = true;
            } else {
                $this->accepted = false;
                $this->error    = $this->get_error(3);
            }
        } else {
            $this->accepted = true;
        }

        $this->_save_file($path, $mode);
        $convertpath = $this->convertpath;

        if ($this->needsThumb) {
            //attempt to resize the image.
            $newsize  = $this->max_thumb_width . "x" . $this->max_thumb_height;
            $filename = $path . $this->file["name"];
            $th_name  = $path . $this->file["th_name"];

            $cmd = $convertpath . " \"$filename\" -resize $newsize \"$th_name\"";

            $conv = shell_exec($cmd);
        }

        if ($this->needsResize) {
            //attempt to resize the image.
            $newsize  = $this->max_image_width . "x" . $this->max_image_height;
            $filename = $path . $this->file["name"];
            $th_name  = $path . $this->file["name"];

            $cmd = $convertpath." \"$filename\" -resize $newsize \"$th_name\"";

            $conv = shell_exec($cmd);
        }

        //return (bool) $this->accepted;
        return array($this->file["name"], $this->file['th_name']);
    }

    /**
     * Get raw thumb filename
     *
     * @return string
     */
    private function _get_rawthumbfilename()
    {
        switch($this->thumbStyle) {
        case 'infix':
            $this->file['raw_th_name'] = $this->file['raw_name'].".".$this->thumbStyleString;
            break;
        case 'folder':
        case 'dir':
        case 'directory':
            $thumbDir = $this->path . $this->thumbStyleString;

            if (!file_exists($thumbDir)) {
                if (@mkdir($thumbDir)) {
                    chmod($thumbDir, "0777");
                }
            }
            $this->file['raw_th_name'] = $this->thumbStyleString."/".$this->file['raw_name'];
            break;
        }
    }

    /**
     * Cleans up the filename, copies the file from PHP's temp location to $path,
     * and checks the overwrite_mode
     *
     * @param string $path File path to your upload directory
     * @param int $overwrite_mode 1 = overwrite existing file
     *     2 = rename if filename already exists (file.txt becomes file_copy0.txt)
     *     3 = do nothing if a file exists
     * @return bool
     */
    private function _save_file($path, $overwrite_mode=1)
    {
        if ($this->error) {
            return false;
        }

        if ($path[strlen($path)-1] != "/") {
            $path = $path . "/";
        }

        $this->path = $path;

        $copy    = "";
        $n       = 1;
        $success = false;

        if ($this->accepted) {
            // Clean up file name (only lowercase letters, numbers and underscores)
            $this->file["name"] = ereg_replace(
                "[^a-z0-9._]", "",
                str_replace(
                    array(" ", "%20"),
                    array("_", "_"),
                    strtolower($this->file["name"])
                )
            );

            // Clean up text file breaks
            if (stristr($this->file["type"], "text")) {
                $this->cleanup_text_file($this->file["tmp_name"]);
            }

            // get the raw name of the file (without its extenstion)
            if (ereg("(\.)([a-z0-9]{2,5})$", $this->file["name"])) {
                $pos = strrpos($this->file["name"], ".");
                if (!$this->file["extension"]) {
                    $this->file["extension"] = substr($this->file["name"], $pos, strlen($this->file["name"]));
                }
                $this->file['raw_name'] = substr($this->file["name"], 0, $pos);
            } else {
                $this->file['raw_name'] = $this->file["name"];
                if ($this->file["extension"]) {
                    $this->file["name"] = $this->file["name"] . $this->file["extension"];
                }
            }

            switch((int) $overwrite_mode) {
            case 1: // overwrite mode
                if (@copy($this->file["tmp_name"], $this->path . $this->file["name"])) {
                    $success = true;
                } else {
                    $success     = false;
                    $this->error = $this->get_error(5);
                }
                //write thumb
                if ($this->createThumb) {
                    //$this->file['raw_th_name'] = $this->file['raw_name'].".th";
                    $this->_get_rawthumbfilename();
                    $this->file['th_name'] = $this->file['raw_th_name'].$this->file['extension'];

                    if (@copy($this->file["tmp_name"], $this->path . $this->file["th_name"])) {
                        $success = true;
                    } else {
                        $success     = false;
                        $this->error = $this->get_error(5);
                    }
                }
                break;
            case 2: // create new with incremental extension
                while (file_exists($this->path . $this->file['raw_name'] . $copy . $this->file["extension"])) {
                    $copy = "_copy" . $n;
                    $n++;
                }

                $this->file["name"] = $this->file['raw_name'] . $copy . $this->file["extension"];

                if (@copy($this->file["tmp_name"], $this->path . $this->file["name"])) {
                    $success = true;
                } else {
                    $success     = false;
                    $this->error = $this->get_error(5);
                }

                //write thumb
                if ($this->createThumb) {
                    //$this->file['raw_th_name'] = $this->file['raw_name'].$copy.".th";
                    $this->_get_rawthumbfilename();
                    $this->file['th_name'] = $this->file['raw_th_name'].$this->file['extension'];

                    if (@copy($this->file["tmp_name"], $this->path . $this->file["th_name"])) {
                        $success = true;
                    } else {
                        $success     = false;
                        $this->error = $this->get_error(5);
                    }
                }

                break;
            default: // do nothing if exists, highest protection
                if (file_exists($this->path . $this->file["name"])) {
                    $this->error = $this->get_error(4);
                    $success     = false;
                } else {
                    if (@copy($this->file["tmp_name"], $this->path . $this->file["name"])) {
                        $success = true;
                    } else {
                        $success     = false;
                        $this->error = $this->get_error(5);
                    }
                }
                break;
            }

            if (!$success) {
                unset($this->file['tmp_name']);
            }
            return (bool) $success;
        } else {
            $this->error = $this->get_error(3);
            return false;
        }
    }

    /**
     * Gets the correct error message for language set by constructor
     *
     * @param int $error_code Error code
     * @return string
     */
    public function get_error($error_code='')
    {
        $error_message = array();
        $error_code    = (int) $error_code;

        // English
        $error_message[0] = "No file was uploaded";
        $error_message[1] = "Maximum file size exceeded. File may be no larger than " . $this->max_filesize/1000 . " KB (" . $this->max_filesize . " bytes).";
        $error_message[2] = "Maximum image size exceeded. Image may be no more than " . $this->max_image_width . " x " . $this->max_image_height . " pixels.";
        $error_message[3] = "Only " . str_replace("image/", "", str_replace("|", " or ", $this->acceptable_file_types)) . " files may be uploaded.";
        $error_message[4] = "File '" . $this->path . $this->file["name"] . "' already exists.";
        // $error_message[5] = "<font face=verdana,arial,helvetica size=2 color=blue><br>Oops</font>. Unable to copy the file, because it probably already exists, to '" . $this->path . "'";
        $error_message[5] = "<font face=verdana,arial,helvetica size=2 color=blue><br>Oops</font>. Unable to copy the file - it is probably already in the online folder.";

        // for backward compatability:
        $this->errors[$error_code] = $error_message[$error_code];

        return $error_message[$error_code];
    }

    /**
     * Convert Mac and/or PC line breaks to UNIX by opening
     * and rewriting the file on the server
     *
     * @param string $file Path and name of text file
     * @return void
     */
    public function cleanup_text_file($file)
    {
        // chr(13) = CR (carridge return) = Macintosh
        // chr(10) = LF (line feed) = Unix
        // Win line break = CRLF
        $new_file  = '';
        $old_file  = '';
        $fcontents = file($file);

        while (list ($line_num, $line) = each($fcontents)) {
            $old_file .= $line;
            $new_file .= str_replace(chr(13), chr(10), $line);
        }

        if ($old_file != $new_file) {
            // Open the uploaded file, and re-write it with the new changes
            $fp = fopen($file, "w");
            fwrite($fp, $new_file);
            fclose($fp);
        }
    }
}

/*
<readme>
Qi_Fileupload can be used to upload files of any type
to a web server using a web browser. The uploaded file's name will
get cleaned up - special characters will be deleted, and spaces
get replaced with underscores, and moved to a specified
directory (on your server). fileupload-class.php also does its best to
determine the file's type (text, GIF, JPEG, etc). If the user
has named the file with the correct extension (.txt, .gif, etc),
then the class will use that, but if the user tries to upload
an extensionless file, PHP does can identify text, gif, jpeg,
and png files for you. As a last resort, if there is no
specified extension, and PHP can not determine the type, you
can set a default extension to be added.

SETUP:
Make sure that the directory that you plan on uploading
files to has enough permissions for your web server to
write/upload to it. (usually, this means making it world writable)
- cd /your/web/dir
- chmod 777 <fileupload_dir>

The HTML FORM used to upload the file should look like this:
<form method="post" action="upload.php" enctype="multipart/form-data">
<input type="file" name="userfile">
<input type="submit" value="Submit">
</form>

USAGE:
// Create a new instance of the class
$my_uploader = new uploader;

// OPTIONAL: set the max filesize of uploadable files in bytes
$my_uploader->max_filesize(90000);

// OPTIONAL: if you're uploading images, you can set the max pixel dimensions
$my_uploader->max_image_size(150, 300); // max_image_size($width, $height)

// UPLOAD the file
$my_uploader->upload("userfile", "", ".jpg");

// MOVE THE FILE to its final destination
// $mode = 1 :: overwrite existing file
// $mode = 2 :: rename new file if a file
// with the same name already
// exists: file.txt becomes file_copy0.txt
// $mode = 3 :: do nothing if a file with the
// same name already exists
$my_uploader->_save_file("/your/web/dir/fileupload_dir", int $mode);

// Check if everything worked
if ($my_uploader->error) {
    echo $my_uploader->error . "<br>";
} else {
    // Successful upload!
    $file_name = $my_uploader->file['name'];
    print($file_name . " was <font face=verdana,arial,helvetica size=2 color=blue>successfully</font> uploaded!");
}
</readme>

<license>
///// fileupload-class.php /////
Copyright (c) 1999, 2002, 2003 David Fox, Angryrobot Productions
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions
are met:
1. Redistributions of source code must retain the above copyright
notice, this list of conditions and the following disclaimer.
2. Redistributions in binary form must reproduce the above
copyright notice, this list of conditions and the following
disclaimer in the documentation and/or other materials provided
with the distribution.
3. Neither the name of author nor the names of its contributors
may be used to endorse or promote products derived from this
software without specific prior written permission.

DISCLAIMER:
THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS "AS IS"
AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED
TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A
PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE AUTHOR OR
CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF
USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED
AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING
IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF
THE POSSIBILITY OF SUCH DAMAGE.

</license>
 */
