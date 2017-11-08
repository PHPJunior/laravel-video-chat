<?php
/**
 * Created by PhpStorm.
 * User: nyinyilwin
 * Date: 11/7/17
 * Time: 3:09 PM.
 */

namespace PhpJunior\LaravelVideoChat\Services;

use Dflydev\ApacheMimeTypes\PhpRepository;
use Illuminate\Support\Facades\Storage;

class UploadManager
{
    private $mime;
    private $disk;
    private $config;

    /**
     * UploadManager constructor.
     *
     * @param $config
     * @param PhpRepository $mime
     */
    public function __construct($config, PhpRepository $mime)
    {
        $this->config = $config;
        $this->mime = $mime;
        $this->disk = Storage::disk($this->config->get('laravel-video-chat.upload.storage'));
    }

    /**
     * Return an array of file details for a file.
     *
     * @param $path
     *
     * @return array
     */
    public function fileDetails($path)
    {
        $path = '/'.ltrim($path, '/');

        return [
            'name'     => basename($path),
            'fullPath' => $path,
            'webPath'  => $this->fileWebPath($path),
            'mimeType' => $this->fileMimeType($path),
            'size'     => $this->fileSize($path),
        ];
    }

    /**
     * Delete a file.
     *
     * @param $path
     *
     * @return string
     */
    public function deleteFile($path)
    {
        $path = $this->cleanFolder($path);

        if ($this->checkFileExists($path)) {
            return $this->disk->delete($path);
        }

        return 'File does not exist.';
    }

    /**
     * Save a file.
     *
     * @param $path
     * @param $content
     *
     * @return string
     */
    public function saveFile($path, $content)
    {
        $path = $this->cleanFolder($path);

        return $this->disk->put($path, $content, 'public');
    }

    /**
     * Check File Exits.
     *
     * @param $path
     *
     * @return bool
     */
    public function checkFileExists($path)
    {
        if ($this->disk->exists($path)) {
            return true;
        }

        return false;
    }

    /**
     * Sanitize the folder name.
     *
     * @param $folder
     *
     * @return string
     */
    private function cleanFolder($folder)
    {
        return '/'.trim(str_replace('..', '', $folder), '/');
    }

    /**
     * Return the full web path to a file.
     *
     * @param $path
     *
     * @return \Illuminate\Contracts\Routing\UrlGenerator|string
     */
    private function fileWebPath($path)
    {
        return $this->disk->url($path);
    }

    /**
     * Return the mime type.
     *
     * @param $path
     *
     * @return mixed|null|string
     */
    private function fileMimeType($path)
    {
        return $this->mime->findType(
            pathinfo($path, PATHINFO_EXTENSION)
        );
    }

    /**
     * Return the file size.
     *
     * @param $path
     *
     * @return
     */
    private function fileSize($path)
    {
        return $this->disk->size($path);
    }
}
