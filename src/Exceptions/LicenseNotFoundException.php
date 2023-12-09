<?php

declare(strict_types=1);

namespace MuriloChianfa\LaravelIoncubeHelper\Exceptions;

use Exception;

/**
 * LicenseNotFoundException class...
 *
 * @since 0.1.0
 * @version 1.0.0
 * @author Murilo Chianfa <github.com/MuriloChianfa>
 * @package Exceptions
 *
 * @method render
 *
 * @final
 */
final class LicenseNotFoundException extends Exception
{
    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        return response()->view('vendor.license', [
            'title' => 'Missing license',
            'subtitle' => 'Submit a valid license to continue...'
        ]);
    }
}
