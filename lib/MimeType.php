<?php
/**
 * Mime type class file
 *
 * @package Mimetype
 */

/**
 * MimeType will detect the mime type of a file using various methods depending 
 * on availability of installed extensions.
 *
 * This was inspired by a blog post by Chris Jean
 * http://chrisjean.com/2009/02/14/generating-mime-type-in-php-is-not-magic/
 *
 * @package Mimetype
 * @author Jansen Price <jansen.price@gmail.com>
 * @version $Id$
 */
class MimeType
{
    /**
     * Get the mime type of a file using the best available method
     *
     * @param string $filename File name to check
     * @return string
     */
    public static function getFileMimeType($filename)
    {
        if (!file_exists($filename) && !is_readable($filename)) {
            throw new Exception("Cannot read file '$filename'");
        }

        if (class_exists('finfo')
            && defined('FILEINFO_MIME')
        ) {
            return self::_getFileMimeTypeUsingFinfo($filename);
        }

        if (function_exists('mime_content_type')) {
            return self::_getFileMimeTypeUsingMimeContentType($filename);
        }

        return self::_getFileMimeTypeUsingFallback($filename);
    }

    /**
     * Get the mime type using Fileinfo
     *
     * @param string $filename File name to check
     * @return string
     */
    protected static function _getFileMimeTypeUsingFinfo($filename)
    {
        $finfo = new finfo(FILEINFO_MIME);
        $filetype = $finfo->file($filename);

        // Return up to the semi colon because after that is the charset which 
        // we don't care about here.
        return substr($filetype, 0, strpos($filetype, ';'));
    }

    /**
     * Get mime type using the mime_content_type function (deprecated)
     *
     * @param string $filename File name to check
     * @return string
     */
    protected static function _getFileMimeTypeUsingMimeContentType($filename)
    {
        return mime_content_type($filename);
    }

    /**
     * Get mime type by using a lookup table with the extension
     *
     * @param string $filename File name to check
     * @return string
     */
    protected static function _getFileMimeTypeUsingFallback($filename)
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        if (array_key_exists($extension, self::$mimeTypes)) {
            return self::$mimeTypes[$extension];
        }
    }

    /**
     * List of extension -> mime types
     *
     * @var array
     */
    public static $mimeTypes = array(
        'ai'      => 'application/postscript',
        'aif'     => 'audio/x-aiff',
        'aifc'    => 'audio/x-aiff',
        'aiff'    => 'audio/x-aiff',
        'asc'     => 'text/plain',
        'asf'     => 'video/x-ms-asf',
        'asx'     => 'video/x-ms-asf',
        'au'      => 'audio/basic',
        'avi'     => 'video/x-msvideo',
        'bcpio'   => 'application/x-bcpio',
        'bin'     => 'application/octet-stream',
        'bmp'     => 'image/bmp',
        'bz2'     => 'application/x-bzip2',
        'cdf'     => 'application/x-netcdf',
        'chrt'    => 'application/x-kchart',
        'class'   => 'application/octet-stream',
        'cpio'    => 'application/x-cpio',
        'cpt'     => 'application/mac-compactpro',
        'csh'     => 'application/x-csh',
        'css'     => 'text/css',
        'csv'     => 'text/plain',
        'dcr'     => 'application/x-director',
        'dir'     => 'application/x-director',
        'djv'     => 'image/vnd.djvu',
        'djvu'    => 'image/vnd.djvu',
        'dll'     => 'application/octet-stream',
        'dms'     => 'application/octet-stream',
        'doc'     => 'application/msword',
        'dvi'     => 'application/x-dvi',
        'dxr'     => 'application/x-director',
        'eps'     => 'application/postscript',
        'etx'     => 'text/x-setext',
        'exe'     => 'application/octet-stream',
        'ez'      => 'application/andrew-inset',
        'flv'     => 'video/x-flv',
        'gif'     => 'image/gif',
        'gtar'    => 'application/x-gtar',
        'gz'      => 'application/x-gzip',
        'hdf'     => 'application/x-hdf',
        'hqx'     => 'application/mac-binhex40',
        'htm'     => 'text/html',
        'html'    => 'text/html',
        'ice'     => 'x-conference/x-cooltalk',
        'ief'     => 'image/ief',
        'iges'    => 'model/iges',
        'igs'     => 'model/iges',
        'img'     => 'application/octet-stream',
        'iso'     => 'application/octet-stream',
        'jad'     => 'text/vnd.sun.j2me.app-descriptor',
        'jar'     => 'application/x-java-archive',
        'jnlp'    => 'application/x-java-jnlp-file',
        'jpe'     => 'image/jpeg',
        'jpeg'    => 'image/jpeg',
        'jpg'     => 'image/jpeg',
        'js'      => 'application/x-javascript',
        'json'    => 'text/plain',
        'kar'     => 'audio/midi',
        'kil'     => 'application/x-killustrator',
        'kpr'     => 'application/x-kpresenter',
        'kpt'     => 'application/x-kpresenter',
        'ksp'     => 'application/x-kspread',
        'kwd'     => 'application/x-kword',
        'kwt'     => 'application/x-kword',
        'latex'   => 'application/x-latex',
        'lha'     => 'application/octet-stream',
        'lzh'     => 'application/octet-stream',
        'm3u'     => 'audio/x-mpegurl',
        'man'     => 'application/x-troff-man',
        'me'      => 'application/x-troff-me',
        'mesh'    => 'model/mesh',
        'mid'     => 'audio/midi',
        'midi'    => 'audio/midi',
        'mif'     => 'application/vnd.mif',
        'mov'     => 'video/quicktime',
        'movie'   => 'video/x-sgi-movie',
        'mp2'     => 'audio/mpeg',
        'mp3'     => 'audio/mpeg',
        'mpe'     => 'video/mpeg',
        'mpeg'    => 'video/mpeg',
        'mpg'     => 'video/mpeg',
        'mpga'    => 'audio/mpeg',
        'ms'      => 'application/x-troff-ms',
        'msh'     => 'model/mesh',
        'mxu'     => 'video/vnd.mpegurl',
        'nc'      => 'application/x-netcdf',
        'odb'     => 'application/vnd.oasis.opendocument.database',
        'odc'     => 'application/vnd.oasis.opendocument.chart',
        'odf'     => 'application/vnd.oasis.opendocument.formula',
        'odg'     => 'application/vnd.oasis.opendocument.graphics',
        'odi'     => 'application/vnd.oasis.opendocument.image',
        'odm'     => 'application/vnd.oasis.opendocument.text-master',
        'odp'     => 'application/vnd.oasis.opendocument.presentation',
        'ods'     => 'application/vnd.oasis.opendocument.spreadsheet',
        'odt'     => 'application/vnd.oasis.opendocument.text',
        'ogg'     => 'application/ogg',
        'otg'     => 'application/vnd.oasis.opendocument.graphics-template',
        'oth'     => 'application/vnd.oasis.opendocument.text-web',
        'otp'     => 'application/vnd.oasis.opendocument.presentation-template',
        'ots'     => 'application/vnd.oasis.opendocument.spreadsheet-template',
        'ott'     => 'application/vnd.oasis.opendocument.text-template',
        'pbm'     => 'image/x-portable-bitmap',
        'pdb'     => 'chemical/x-pdb',
        'pdf'     => 'application/pdf',
        'pgm'     => 'image/x-portable-graymap',
        'pgn'     => 'application/x-chess-pgn',
        'php'     => 'text/x-php',
        'png'     => 'image/png',
        'pnm'     => 'image/x-portable-anymap',
        'ppm'     => 'image/x-portable-pixmap',
        'ppt'     => 'application/vnd.ms-powerpoint',
        'ps'      => 'application/postscript',
        'qt'      => 'video/quicktime',
        'ra'      => 'audio/x-realaudio',
        'ram'     => 'audio/x-pn-realaudio',
        'ras'     => 'image/x-cmu-raster',
        'rgb'     => 'image/x-rgb',
        'rm'      => 'audio/x-pn-realaudio',
        'roff'    => 'application/x-troff',
        'rpm'     => 'application/x-rpm',
        'rtf'     => 'text/rtf',
        'rtx'     => 'text/richtext',
        'sgm'     => 'text/sgml',
        'sgml'    => 'text/sgml',
        'sh'      => 'application/x-sh',
        'shar'    => 'application/x-shar',
        'silo'    => 'model/mesh',
        'sis'     => 'application/vnd.symbian.install',
        'sit'     => 'application/x-stuffit',
        'skd'     => 'application/x-koan',
        'skm'     => 'application/x-koan',
        'skp'     => 'application/x-koan',
        'skt'     => 'application/x-koan',
        'smi'     => 'application/smil',
        'smil'    => 'application/smil',
        'snd'     => 'audio/basic',
        'so'      => 'application/octet-stream',
        'spl'     => 'application/x-futuresplash',
        'src'     => 'application/x-wais-source',
        'stc'     => 'application/vnd.sun.xml.calc.template',
        'std'     => 'application/vnd.sun.xml.draw.template',
        'sti'     => 'application/vnd.sun.xml.impress.template',
        'stw'     => 'application/vnd.sun.xml.writer.template',
        'sv4cpio' => 'application/x-sv4cpio',
        'sv4crc'  => 'application/x-sv4crc',
        'swf'     => 'application/x-shockwave-flash',
        'sxc'     => 'application/vnd.sun.xml.calc',
        'sxd'     => 'application/vnd.sun.xml.draw',
        'sxg'     => 'application/vnd.sun.xml.writer.global',
        'sxi'     => 'application/vnd.sun.xml.impress',
        'sxm'     => 'application/vnd.sun.xml.math',
        'sxw'     => 'application/vnd.sun.xml.writer',
        't'       => 'application/x-troff',
        'tar'     => 'application/x-tar',
        'tcl'     => 'application/x-tcl',
        'tex'     => 'text/x-tex',
        'texi'    => 'text/x-texinfo',
        'texinfo' => 'text/x-texinfo',
        'tgz'     => 'application/x-gzip',
        'tif'     => 'image/tiff',
        'tiff'    => 'image/tiff',
        'torrent' => 'application/x-bittorrent',
        'tr'      => 'application/x-troff',
        'tsv'     => 'text/tab-separated-values',
        'txt'     => 'text/plain',
        'ustar'   => 'application/x-ustar',
        'vcd'     => 'application/x-cdlink',
        'vcf'     => 'text/x-vcard',
        'vim'     => 'text/plain',
        'vrml'    => 'model/vrml',
        'wav'     => 'audio/x-wav',
        'wax'     => 'audio/x-ms-wax',
        'wbmp'    => 'image/vnd.wap.wbmp',
        'wbxml'   => 'application/vnd.wap.wbxml',
        'wm'      => 'video/x-ms-wm',
        'wma'     => 'audio/x-ms-wma',
        'wml'     => 'text/vnd.wap.wml',
        'wmlc'    => 'application/vnd.wap.wmlc',
        'wmls'    => 'text/vnd.wap.wmlscript',
        'wmlsc'   => 'application/vnd.wap.wmlscriptc',
        'wmv'     => 'video/x-ms-wmv',
        'wmx'     => 'video/x-ms-wmx',
        'wrl'     => 'model/vrml',
        'wvx'     => 'video/x-ms-wvx',
        'xbm'     => 'image/x-xbitmap',
        'xht'     => 'application/xhtml+xml',
        'xhtml'   => 'application/xhtml+xml',
        'xls'     => 'application/vnd.ms-excel',
        'xml'     => 'text/xml',
        'xpm'     => 'image/x-xpixmap',
        'xsl'     => 'text/xml',
        'xwd'     => 'image/x-xwindowdump',
        'xyz'     => 'chemical/x-xyz',
        'zip'     => 'application/zip'
    );
}
