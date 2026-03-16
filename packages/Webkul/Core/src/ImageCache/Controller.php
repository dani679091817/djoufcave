<?php

namespace Webkul\Core\ImageCache;

use Closure;
use Illuminate\Http\Response as IlluminateResponse;
use Illuminate\Support\Facades\Config;
use Intervention\Image\ImageCacheController;

class Controller extends ImageCacheController
{
    /**
     * Cache template.
     *
     * @var string
     */
    protected $template;

    /**
     * Logo.
     *
     * @var string
     */
    const BAGISTO_LOGO = 'https://updates.bagisto.com/bagisto.png';

    /**
     * Get HTTP response of either original image file or
     * template applied file.
     *
     * @param  string  $template
     * @param  string  $filename
     * @return Illuminate\Http\Response
     */
    public function getResponse($template, $filename)
    {
        switch (strtolower($template)) {
            case 'original':
                return $this->getOriginal($filename);

            case 'download':
                return $this->getDownload($filename);

            default:
                return $this->getImage($template, $filename);
        }
    }

    /**
     * Get HTTP response of template applied image file
     *
     * @param  string  $template
     * @param  string  $filename
     * @return Illuminate\Http\Response
     */
    public function getImage($template, $filename)
    {
        $this->template = $template;

        $cacheTime = $template == 'logo' ? 10080 : config('imagecache.lifetime');

        if ($template == 'logo') {
            $path = self::BAGISTO_LOGO;
        } else {
            $template = $this->getTemplate($template);

            $path = $this->getImagePath($filename);
        }

        /**
         * Image manipulation based on callback
         */
        $manager = new ImageManager(Config::get('image'));

        try {
            $content = $manager->cache(function ($image) use ($template, $path) {
                if ($template instanceof Closure) {
                    /**
                     * Build from closure callback template
                     */
                    $template($image->make($path));
                } elseif (is_object($template)) {
                    /**
                     * Build from filter template
                     */
                    $image->make($path)->filter($template);
                } else {
                    $image->make($path);
                }
            }, $cacheTime);
        } catch (\Throwable $e) {
            if ($template == 'logo') {
                $content = '';
            } else {
                $content = $this->renderWithoutCache($manager, $template, $path);
            }
        }

        return $this->buildResponse($content);
    }

    /**
     * Render image without using cache storage.
     */
    protected function renderWithoutCache(ImageManager $manager, $template, string $path): string
    {
        try {
            $image = $manager->make($path);

            if ($template instanceof Closure) {
                $template($image);
            } elseif (is_object($template)) {
                $image->filter($template);
            }

            return (string) $image->encode();
        } catch (\Throwable $e) {
            abort(404);
        }
    }

    /**
     * Builds HTTP response from given image data
     *
     * @param  string  $content
     * @return Illuminate\Http\Response
     */
    protected function buildResponse($content)
    {
        /**
         * Define mime type
         */
        $mime = finfo_buffer(finfo_open(FILEINFO_MIME_TYPE), $content);

        /**
         * Respond with 304 not modified if browser has the image cached
         */
        $eTag = md5($content);

        $notModified = isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == $eTag;

        $content = $notModified ? null : $content;

        $statusCode = $notModified ? 304 : 200;

        $maxAge = ($this->template == 'logo' ? 10080 : config('imagecache.lifetime')) * 60;

        /**
         * Return http response
         */
        return new IlluminateResponse($content, $statusCode, [
            'Content-Type' => $mime,
            'Cache-Control' => 'max-age='.$maxAge.', public',
            'Content-Length' => strlen($content),
            'Etag' => $eTag,
        ]);
    }
}
